<?php

declare(strict_types=1);

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\File\Internal\FileHashTable;
use WebPConvert\WebPConvert;

final class IblockWebpMigrate
{
    public const ARCHIVE_SUBDIR = '_ORIGINALIMG';

    /** @var array<string, mixed> */
    private array $config;

    private string $docRoot;
    private string $uploadDir;
    private string $archiveRoot;
    private string $manifestPath;
    private bool $useWebPConvert = false;

    /** @param array<string, mixed> $config */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->docRoot = rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
        $this->uploadDir = (string)($config['upload_dir'] ?? 'upload');
        $this->archiveRoot = $this->docRoot . '/' . $this->uploadDir . '/' . self::ARCHIVE_SUBDIR;
        $this->manifestPath = (string)$config['manifest'];
    }

    public function bootstrap(): void
    {
        if (\Bitrix\Main\Loader::includeModule('apriori.optimizer')) {
            $this->useWebPConvert = class_exists(WebPConvert::class);
        }
        if (!$this->useWebPConvert && !function_exists('imagewebp')) {
            throw new RuntimeException('Neither WebPConvert nor imagewebp is available');
        }
        if (!is_dir($this->archiveRoot)) {
            if (!@mkdir($this->archiveRoot, 0775, true) && !is_dir($this->archiveRoot)) {
                throw new RuntimeException('Cannot create archive dir: ' . $this->archiveRoot);
            }
        }
    }

    public function runCheck(bool $benchmark): int
    {
        global $DB;

        $res = $DB->Query("
            SELECT COUNT(*) AS CNT FROM b_file
            WHERE SUBDIR LIKE 'iblock/%'
              AND CONTENT_TYPE IN ('image/jpeg', 'image/png')
        ");
        $row = $res->Fetch();
        $total = (int)($row['CNT'] ?? 0);

        echo "b_file iblock jpeg/png: {$total}\n";

        $missing = 0;
        $hardlinks = 0;
        $sample = $this->fetchRows(0, min(5000, $total > 0 ? $total : 5000));
        foreach ($sample as $r) {
            if (!$r['exists']) {
                $missing++;
            } elseif ($r['nlink'] > 1) {
                $hardlinks++;
            }
        }
        echo "Sample scan (" . count($sample) . "): missing={$missing}, hardlinks={$hardlinks}\n";
        echo 'Engine: ' . ($this->useWebPConvert ? 'WebPConvert/cwebp' : 'GD') . "\n";
        echo 'cwebp: ' . (trim((string)shell_exec('command -v cwebp')) ?: 'not found') . "\n";

        if ($benchmark) {
            return $this->runBenchmark((int)$this->config['limit']);
        }

        return 0;
    }

    private function runBenchmark(int $limit): int
    {
        $rows = $this->fetchRows(0, $limit);
        $savings = [];
        $bytesBefore = 0;
        $bytesAfter = 0;
        $skipped = 0;
        $converted = 0;

        foreach ($rows as $r) {
            if (!$r['exists']) {
                continue;
            }
            $result = $this->convertCandidate($r['abs'], (string)$r['content_type'], true);
            if ($result === null) {
                $skipped++;
                continue;
            }
            $converted++;
            $bytesBefore += $result['bytes_before'];
            $bytesAfter += $result['bytes_after'];
            $savings[] = $result['savings_percent'];
            foreach ($result['temps'] ?? [] as $t) {
                @unlink($t);
            }
        }

        sort($savings);
        $median = $savings ? $savings[(int)floor(count($savings) / 2)] : 0;
        $report = [
            'date' => date('c'),
            'limit' => $limit,
            'converted' => $converted,
            'skipped' => $skipped,
            'bytes_before' => $bytesBefore,
            'bytes_after' => $bytesAfter,
            'savings_total_percent' => $bytesBefore > 0 ? round((1 - $bytesAfter / $bytesBefore) * 100, 2) : 0,
            'savings_median_percent' => $median,
        ];

        $reportPath = $this->archiveRoot . '/benchmark_report.json';
        file_put_contents($reportPath, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo "Benchmark: converted={$converted}, skipped={$skipped}\n";
        echo "Bytes before: {$bytesBefore}, after: {$bytesAfter}\n";
        echo "Total savings: {$report['savings_total_percent']}%, median: {$median}%\n";
        echo "Report: {$reportPath}\n";

        return 0;
    }

    public function runMigrate(): int
    {
        $lastId = (int)$this->config['last_id'];
        $limit = (int)$this->config['limit'];
        $rows = $this->fetchRows($lastId, $limit);
        $stats = ['ok' => 0, 'skip' => 0, 'err' => 0];

        foreach ($rows as $r) {
            try {
                $status = $this->migrateOne($r);
            } catch (Throwable $e) {
                $this->appendManifest($r, 'error', ['error' => $e->getMessage()], null);
                $status = 'err';
                fwrite(STDERR, "#{$r['id']} ERROR: {$e->getMessage()}\n");
            }
            $stats[$status] = ($stats[$status] ?? 0) + 1;
            echo "#{$r['id']} {$status} {$r['subdir']}/{$r['file_name']}\n";
            $lastId = (int)$r['id'];
        }

        echo "Batch done. last-id={$lastId} ok={$stats['ok']} skip={$stats['skip']} err={$stats['err']}\n";

        return 0;
    }

    /** @param array<string, mixed> $row */
    private function migrateOne(array $row): string
    {
        if (!$row['exists']) {
            $this->appendManifest($row, 'missing', [], null);
            return 'skip';
        }

        $src = $row['abs'];
        if ($row['nlink'] > 1) {
            if (!$this->breakHardlink($src)) {
                $this->appendManifest($row, 'hardlink_fail', [], null);
                return 'err';
            }
        }

        $workSrc = $this->prepareSource($src, (string)$row['content_type']);
        $temps = $workSrc !== $src ? [$workSrc] : [];

        try {
            $candidate = $this->convertCandidate($workSrc, (string)$row['content_type'], false);
            if ($candidate === null) {
                $strict = !empty($this->config['strict_size']);
                $force = !empty($this->config['force_webp']);
                if ($force) {
                    $candidate = $this->convertAtQuality($workSrc, (string)$row['content_type'], 85, false);
                    if ($candidate === null) {
                        $this->appendManifest($row, 'convert_fail', [], null);
                        return 'err';
                    }
                    $candidate['status'] = 'forced_webp';
                } elseif ($strict) {
                    $this->appendManifest($row, 'skipped_below_target', [], null);
                    return 'skip';
                } else {
                    $this->appendManifest($row, 'skipped_below_target', [], null);
                    return 'skip';
                }
            }

            $temps = array_merge($temps, $candidate['temps'] ?? []);
            $webpPath = $candidate['webp_path'];
            $newName = preg_replace('/\.(jpe?g|png)$/i', '.webp', (string)$row['file_name']);
            if ($newName === $row['file_name']) {
                $newName .= '.webp';
            }

            $destDir = dirname($src);
            $finalWebp = $destDir . '/' . $newName;
            $archivePath = $this->archiveRoot . '/' . $row['subdir'] . '/' . $row['file_name'];

            if (!is_dir(dirname($archivePath))) {
                mkdir(dirname($archivePath), 0775, true);
            }

            if (is_file($archivePath)) {
                @unlink($archivePath);
            }
            if (!rename($src, $archivePath)) {
                $this->appendManifest($row, 'archive_fail', $candidate, null);
                return 'err';
            }

            if (is_file($finalWebp)) {
                @unlink($finalWebp);
            }
            if (!rename($webpPath, $finalWebp)) {
                rename($archivePath, $src);
                $this->appendManifest($row, 'install_fail', $candidate, null);
                return 'err';
            }

            $info = @getimagesize($finalWebp);
            if (!$info || ($info[2] ?? 0) !== IMAGETYPE_WEBP) {
                rename($archivePath, $src);
                @unlink($finalWebp);
                $this->appendManifest($row, 'invalid_webp', $candidate, null);
                return 'err';
            }

            $this->updateBFile((int)$row['id'], $newName, filesize($finalWebp), (int)$info[0], (int)$info[1]);
            $this->updateFileHash((int)$row['id'], $finalWebp);

            $status = $candidate['status'] ?? 'converted';
            $this->appendManifest($row, $status, $candidate, $archivePath);

            return 'ok';
        } finally {
            foreach ($temps as $t) {
                if (is_file($t)) {
                    @unlink($t);
                }
            }
        }
    }

    /**
     * @return array{webp_path:string,bytes_before:int,bytes_after:int,savings_percent:float,quality_used:int,engine:string,status?:string,temps?:list<string>}|null
     */
    private function convertCandidate(string $src, string $contentType, bool $dryRun): ?array
    {
        $bytesBefore = filesize($src) ?: 0;
        if ($bytesBefore <= 0) {
            return null;
        }

        $qualities = $this->qualitySteps();
        $minSavings = (float)$this->config['min_savings'];
        $targetMid = 0.25;
        $candidates = [];

        foreach ($qualities as $q) {
            $res = $this->convertAtQuality($src, $contentType, $q, $dryRun);
            if ($res === null) {
                continue;
            }
            $savings = $res['savings_percent'] / 100;
            if ($savings >= $minSavings) {
                $candidates[] = $res;
            } elseif ($dryRun) {
                $candidates[] = $res;
            }
            if ($savings >= (float)$this->config['target_savings_min'] && $savings <= (float)$this->config['target_savings_max']) {
                break;
            }
            if ($savings > (float)$this->config['target_savings_max'] && $q === $qualities[0]) {
                return $res;
            }
        }

        if ($candidates === []) {
            return null;
        }

        usort($candidates, static function (array $a, array $b) use ($targetMid): int {
            $da = abs($a['savings_percent'] / 100 - $targetMid);
            $db = abs($b['savings_percent'] / 100 - $targetMid);
            if (abs($da - $db) < 0.001) {
                return $b['quality_used'] <=> $a['quality_used'];
            }
            return $da <=> $db;
        });

        $best = $candidates[0];
        foreach ($candidates as $c) {
            if ($c !== $best && isset($c['temps'])) {
                foreach ($c['temps'] as $t) {
                    @unlink($t);
                }
            }
        }

        return $best;
    }

    /** @return list<int> */
    private function qualitySteps(): array
    {
        $max = (int)$this->config['quality_max'];
        $min = (int)$this->config['quality_min'];
        $steps = [];
        foreach ([90, 85, 80] as $q) {
            if ($q <= $max && $q >= $min) {
                $steps[] = $q;
            }
        }
        return $steps ?: [$max];
    }

    /**
     * @return array{webp_path:string,bytes_before:int,bytes_after:int,savings_percent:float,quality_used:int,engine:string,temps?:list<string>}|null
     */
    private function convertAtQuality(string $src, string $contentType, int $quality, bool $keepTemp): ?array
    {
        $bytesBefore = filesize($src) ?: 0;
        $tmp = $src . '.migr.' . $quality . '.' . uniqid('', true) . '.webp';

        $engine = 'gd';
        $ok = false;
        if ($this->useWebPConvert && ($this->config['engine'] === 'auto' || $this->config['engine'] === 'cwebp')) {
            $ok = $this->convertWebPConvert($src, $tmp, $quality, $contentType);
            $engine = 'cwebp';
        }
        if (!$ok) {
            $ok = $this->convertGd($src, $tmp, $quality, $contentType);
            $engine = 'gd';
        }
        if (!$ok || !is_file($tmp) || filesize($tmp) <= 0) {
            @unlink($tmp);
            return null;
        }

        $bytesAfter = filesize($tmp) ?: 0;
        $savings = $bytesBefore > 0 ? round((1 - $bytesAfter / $bytesBefore) * 100, 2) : 0;

        return [
            'webp_path' => $tmp,
            'bytes_before' => $bytesBefore,
            'bytes_after' => $bytesAfter,
            'savings_percent' => $savings,
            'quality_used' => $quality,
            'engine' => $engine,
            'temps' => $keepTemp ? [$tmp] : [],
        ];
    }

    private function convertWebPConvert(string $source, string $destination, int $quality, string $contentType): bool
    {
        $options = [
            'converters' => ['cwebp', 'gd'],
            'png' => [
                'encoding' => 'auto',
                'near-lossless' => 20,
                'quality' => $quality,
                'sharp-yuv' => true,
            ],
            'jpeg' => [
                'encoding' => 'auto',
                'quality' => $quality,
                'auto-limit' => true,
                'sharp-yuv' => true,
            ],
            'jpg' => [
                'encoding' => 'auto',
                'quality' => $quality,
                'auto-limit' => true,
                'sharp-yuv' => true,
            ],
            'metadata' => 'all',
            'cwebp-metadata' => 'exif',
        ];

        try {
            WebPConvert::convert($source, $destination, $options);
            return is_file($destination) && filesize($destination) > 0;
        } catch (Throwable $e) {
            return false;
        }
    }

    private function convertGd(string $source, string $destination, int $quality, string $contentType): bool
    {
        if (!function_exists('imagewebp')) {
            return false;
        }

        $mime = $contentType ?: (mime_content_type($source) ?: '');
        $img = null;
        switch ($mime) {
            case 'image/png':
                $img = @imagecreatefrompng($source);
                if ($img) {
                    imagepalettetotruecolor($img);
                    imagealphablending($img, true);
                    imagesavealpha($img, true);
                }
                break;
            case 'image/jpeg':
                $img = @imagecreatefromjpeg($source);
                break;
            default:
                return false;
        }
        if (!$img) {
            return false;
        }

        $dir = dirname($destination);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $ok = imagewebp($img, $destination, $quality);
        imagedestroy($img);

        return $ok && is_file($destination);
    }

    private function prepareSource(string $src, string $contentType): string
    {
        if ($contentType !== 'image/jpeg' || !function_exists('exif_read_data')) {
            return $src;
        }
        $exif = @exif_read_data($src);
        if (empty($exif['Orientation']) || (int)$exif['Orientation'] === 1) {
            return $src;
        }

        $img = @imagecreatefromjpeg($src);
        if (!$img) {
            return $src;
        }

        $angle = match ((int)$exif['Orientation']) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };
        if ($angle !== 0) {
            $rotated = imagerotate($img, $angle, 0);
            if ($rotated) {
                imagedestroy($img);
                $img = $rotated;
            }
        }

        $tmp = $src . '.exiffix.' . uniqid('', true) . '.jpg';
        if (!imagejpeg($img, $tmp, 95)) {
            imagedestroy($img);
            return $src;
        }
        imagedestroy($img);

        return $tmp;
    }

    private function breakHardlink(string $path): bool
    {
        $tmp = $path . '.breaklink.' . uniqid('', true);
        if (!copy($path, $tmp)) {
            return false;
        }
        if (!unlink($path)) {
            @unlink($tmp);
            return false;
        }
        return copy($tmp, $path) && unlink($tmp);
    }

    /** @return list<array<string, mixed>> */
    private function fetchRows(int $lastId, int $limit): array
    {
        global $DB;
        $lastId = (int)$lastId;
        $limit = max(1, (int)$limit);

        $sql = "
            SELECT ID, SUBDIR, FILE_NAME, CONTENT_TYPE, FILE_SIZE, WIDTH, HEIGHT
            FROM b_file
            WHERE SUBDIR LIKE 'iblock/%'
              AND CONTENT_TYPE IN ('image/jpeg', 'image/png')
        ";
        if ($lastId > 0) {
            $sql .= ' AND ID > ' . $lastId;
        }
        $sql .= ' ORDER BY ID ASC LIMIT ' . $limit;

        $rows = [];
        $res = $DB->Query($sql);
        while ($row = $res->Fetch()) {
            $subdir = (string)$row['SUBDIR'];
            $fileName = (string)$row['FILE_NAME'];
            $abs = $this->docRoot . '/' . $this->uploadDir . '/' . $subdir . '/' . $fileName;
            $stat = is_file($abs) ? stat($abs) : false;
            $rows[] = [
                'id' => (int)$row['ID'],
                'subdir' => $subdir,
                'file_name' => $fileName,
                'content_type' => (string)$row['CONTENT_TYPE'],
                'file_size' => (int)$row['FILE_SIZE'],
                'width' => (int)$row['WIDTH'],
                'height' => (int)$row['HEIGHT'],
                'abs' => $abs,
                'exists' => is_file($abs),
                'nlink' => $stat ? (int)$stat['nlink'] : 0,
            ];
        }

        return $rows;
    }

    private function updateBFile(int $id, string $newName, int $fileSize, int $width, int $height): void
    {
        global $DB;
        $DB->Query("
            UPDATE b_file SET
                FILE_NAME = '" . $DB->ForSql($newName) . "',
                CONTENT_TYPE = 'image/webp',
                FILE_SIZE = " . (int)$fileSize . ',
                WIDTH = ' . (int)$width . ',
                HEIGHT = ' . (int)$height . ",
                TIMESTAMP_X = " . $DB->CurrentTimeFunction() . '
            WHERE ID = ' . $id
        );
    }

    private function updateFileHash(int $fileId, string $absPath): void
    {
        if (Option::get('main', 'control_file_duplicates', 'N') !== 'Y') {
            return;
        }

        $size = filesize($absPath) ?: 0;
        $hash = '';
        $maxSize = (int)Option::get('main', 'duplicates_max_size', '100') * 1024 * 1024;
        if ($size > 0 && ($maxSize === 0 || $size <= $maxSize)) {
            $hash = hash_file('md5', $absPath) ?: '';
        }

        $existing = FileHashTable::getList([
            'filter' => ['=FILE_ID' => $fileId],
            'limit' => 1,
        ])->fetch();

        if ($hash === '') {
            if ($existing) {
                FileHashTable::delete($fileId);
            }
            return;
        }

        if ($existing) {
            FileHashTable::update($fileId, [
                'FILE_SIZE' => $size,
                'FILE_HASH' => $hash,
            ]);
        } else {
            FileHashTable::add([
                'FILE_ID' => $fileId,
                'FILE_SIZE' => $size,
                'FILE_HASH' => $hash,
            ]);
        }
    }

    /**
     * @param array<string, mixed> $row
     * @param array<string, mixed>|null $candidate
     */
    private function appendManifest(array $row, string $status, ?array $candidate, ?string $archivePath): void
    {
        $isNew = !is_file($this->manifestPath);
        $fp = fopen($this->manifestPath, 'ab');
        if (!$fp) {
            return;
        }
        if ($isNew) {
            fputcsv($fp, [
                'id', 'subdir', 'old_name', 'new_name', 'orig_path', 'status',
                'bytes_before', 'bytes_after', 'savings_percent', 'quality_used', 'engine', 'timestamp',
            ]);
        }
        $newName = isset($candidate['webp_path'])
            ? preg_replace('/\.(jpe?g|png)$/i', '.webp', (string)$row['file_name'])
            : '';
        fputcsv($fp, [
            $row['id'],
            $row['subdir'],
            $row['file_name'],
            $newName,
            $archivePath ? str_replace($this->docRoot . '/', '', $archivePath) : '',
            $status,
            $candidate['bytes_before'] ?? '',
            $candidate['bytes_after'] ?? '',
            $candidate['savings_percent'] ?? '',
            $candidate['quality_used'] ?? '',
            $candidate['engine'] ?? '',
            date('c'),
        ]);
        fclose($fp);
    }

    public function runRollback(int $limit): int
    {
        if (!is_file($this->manifestPath)) {
            echo "Manifest not found: {$this->manifestPath}\n";
            return 1;
        }

        $lines = file($this->manifestPath, FILE_IGNORE_NEW_LINES);
        if (!$lines) {
            return 0;
        }

        $header = str_getcsv($lines[0]);
        $rows = [];
        for ($i = 1; $i < count($lines); $i++) {
            $data = str_getcsv($lines[$i]);
            if (count($data) < count($header)) {
                continue;
            }
            $rows[] = array_combine($header, $data);
        }

        $rows = array_reverse($rows);
        $done = 0;
        foreach ($rows as $r) {
            if ($done >= $limit) {
                break;
            }
            if (($r['status'] ?? '') !== 'converted' && ($r['status'] ?? '') !== 'forced_webp') {
                continue;
            }
            if ($this->rollbackOne($r)) {
                $done++;
                echo "Rollback #{$r['id']}\n";
            }
        }

        echo "Rolled back: {$done}\n";

        return 0;
    }

    /** @param array<string, string> $r */
    private function rollbackOne(array $r): bool
    {
        global $DB;

        $id = (int)$r['id'];
        $subdir = $r['subdir'];
        $oldName = $r['old_name'];
        $newName = $r['new_name'] ?: preg_replace('/\.(jpe?g|png)$/i', '.webp', $oldName);
        $origRel = $r['orig_path'] ?? ($this->uploadDir . '/' . self::ARCHIVE_SUBDIR . '/' . $subdir . '/' . $oldName);
        $origAbs = $this->docRoot . '/' . ltrim($origRel, '/');
        $liveAbs = $this->docRoot . '/' . $this->uploadDir . '/' . $subdir . '/' . $newName;

        if (!is_file($origAbs)) {
            return false;
        }

        if (is_file($liveAbs)) {
            @unlink($liveAbs);
        }

        $liveOrig = $this->docRoot . '/' . $this->uploadDir . '/' . $subdir . '/' . $oldName;
        if (!rename($origAbs, $liveOrig)) {
            return false;
        }

        $info = @getimagesize($liveOrig);
        $mime = $info ? image_type_to_mime_type($info[2]) : 'image/jpeg';

        $DB->Query("
            UPDATE b_file SET
                FILE_NAME = '" . $DB->ForSql($oldName) . "',
                CONTENT_TYPE = '" . $DB->ForSql($mime) . "',
                FILE_SIZE = " . (int)filesize($liveOrig) . ',
                WIDTH = ' . (int)($info[0] ?? 0) . ',
                HEIGHT = ' . (int)($info[1] ?? 0) . ",
                TIMESTAMP_X = " . $DB->CurrentTimeFunction() . '
            WHERE ID = ' . $id
        );

        $this->updateFileHash($id, $liveOrig);

        return true;
    }
}
