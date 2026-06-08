<?php
/**
 * Дерево разделов IB41 из gdsClassTree (ETM API).
 */

declare(strict_types=1);

function etmSectionSlug(string $url, string $name, string $classCode): string {
    $url = trim($url);
    if ($url !== '' && preg_match('/^[a-z0-9][a-z0-9_-]*$/i', $url)) {
        return mb_strtolower($url);
    }
    $slug = CUtil::translit($name, 'ru', [
        'max_len' => 80,
        'change_case' => 'L',
        'replace_space' => '_',
        'replace_other' => '_',
    ]);
    $slug = preg_replace('/[^a-z0-9_-]+/', '_', (string)$slug);
    $slug = trim((string)$slug, '_');
    return $slug !== '' ? $slug : 'class_' . $classCode;
}

/**
 * Режим размещения разделов ETM:
 * все ветки gdsClassTree создаются сразу в корне инфоблока.
 */
function etmEnsureRootSectionEtm(int $iblockId): int {
    return 0;
}

/**
 * Найти или создать раздел по XML_ID = ETM_CLASS_{code}.
 */
function etmFindOrCreateSectionByClass(
    int $iblockId,
    int $parentId,
    string $classCode,
    string $name,
    string $slug,
    bool $dryRun
): int {
    $xmlId = 'ETM_CLASS_' . $classCode;
    $existing = CIBlockSection::GetList(
        [],
        ['IBLOCK_ID' => $iblockId, 'XML_ID' => $xmlId],
        false,
        ['nTopCount' => 1],
        ['ID', 'IBLOCK_SECTION_ID']
    )->Fetch();
    if ($existing) {
        $existingId = (int)$existing['ID'];
        $existingParentId = (int)($existing['IBLOCK_SECTION_ID'] ?? 0);
        if (!$dryRun && $existingParentId !== $parentId) {
            $bs = new CIBlockSection();
            $bs->Update($existingId, ['IBLOCK_SECTION_ID' => $parentId]);
        }
        return $existingId;
    }

    $byCode = CIBlockSection::GetList(
        [],
        ['IBLOCK_ID' => $iblockId, 'SECTION_ID' => $parentId, 'CODE' => $slug],
        false,
        ['nTopCount' => 1],
        ['ID']
    )->Fetch();
    if ($byCode) {
        return (int)$byCode['ID'];
    }

    if ($dryRun) {
        return 0;
    }

    $bs = new CIBlockSection();
    $id = $bs->Add([
        'IBLOCK_ID' => $iblockId,
        'IBLOCK_SECTION_ID' => $parentId,
        'NAME' => $name,
        'CODE' => $slug,
        'XML_ID' => $xmlId,
        'ACTIVE' => 'Y',
        'SORT' => 500,
    ]);
    return $id ? (int)$id : 0;
}

/**
 * Путь разделов из gdsClassTree → ID листового раздела.
 *
 * @param list<array{code?:string,name?:string,url?:string}> $tree
 */
function etmResolveSectionPathFromClassTree(int $iblockId, array $tree, bool $dryRun = false): int {
    $rootId = etmEnsureRootSectionEtm($iblockId);
    $parentId = $rootId;
    $leafId = $rootId;
    foreach ($tree as $node) {
        if (!is_array($node)) {
            continue;
        }
        $classCode = trim((string)($node['code'] ?? ''));
        $name = trim((string)($node['name'] ?? ''));
        $url = trim((string)($node['url'] ?? ''));
        if ($classCode === '' || $name === '') {
            continue;
        }
        $slug = etmSectionSlug($url, $name, $classCode);
        $secId = etmFindOrCreateSectionByClass($iblockId, $parentId, $classCode, $name, $slug, $dryRun);
        if ($secId > 0) {
            $parentId = $secId;
            $leafId = $secId;
        }
    }
    return $leafId;
}
