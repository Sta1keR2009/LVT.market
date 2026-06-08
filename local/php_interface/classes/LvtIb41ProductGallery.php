<?php

/**
 * Галерея карточки IB41: дедуп фото, видео первыми, без дубля блока «Видео».
 */
class LvtIb41ProductGallery
{
    public static function mergeGallery(array $gallery, array $arResult): array
    {
        $name = (string) ($arResult['NAME'] ?? '');
        $gallery = self::dedupeImageSlides($gallery);

        $hasMorePhoto = self::galleryHasUploadedPhotos($gallery, $arResult);
        if (!$hasMorePhoto) {
            $gallery = self::appendEtmImageUrls($gallery, $arResult, $name);
        }

        $videoSlides = self::buildVideoSlides($arResult, $gallery, $name);
        if ($videoSlides !== []) {
            return array_merge($videoSlides, $gallery);
        }

        return $gallery;
    }

    public static function galleryHasVideo(array $gallery): bool
    {
        foreach ($gallery as $slide) {
            if (!empty($slide['IS_VIDEO'])) {
                return true;
            }
        }

        return false;
    }

    public static function encodeMediaUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }
        if (strpos($url, '//') === 0) {
            $url = 'https:' . $url;
        } elseif (!preg_match('#^https?://#i', $url)) {
            if ($url[0] === '/') {
                $url = 'https://cdn.etm.ru' . $url;
            } else {
                return $url;
            }
        }

        $parts = parse_url($url);
        if ($parts === false || empty($parts['host'])) {
            return $url;
        }

        $path = (string) ($parts['path'] ?? '');
        if ($path !== '') {
            $segments = explode('/', $path);
            $encoded = [];
            foreach ($segments as $segment) {
                if ($segment === '') {
                    $encoded[] = '';
                    continue;
                }
                $encoded[] = rawurlencode(rawurldecode($segment));
            }
            $path = implode('/', $encoded);
        }

        $out = strtolower((string) ($parts['scheme'] ?? 'https')) . '://' . $parts['host'] . $path;
        if (!empty($parts['query'])) {
            $out .= '?' . $parts['query'];
        }
        if (!empty($parts['fragment'])) {
            $out .= '#' . $parts['fragment'];
        }

        return $out;
    }

    public static function extractVideoUrl(string $videoMarkup): string
    {
        $videoMarkup = trim($videoMarkup);
        if ($videoMarkup === '') {
            return '';
        }
        if (preg_match('#<source[^>]+src=["\']([^"\']+)#i', $videoMarkup, $m)) {
            return self::encodeMediaUrl(trim((string) $m[1]));
        }
        if (preg_match('#https?://[^\s"\'<>]+#i', $videoMarkup, $m)) {
            return self::encodeMediaUrl(trim((string) $m[0]));
        }

        return '';
    }

    private static function galleryHasUploadedPhotos(array $gallery, array $arResult): bool
    {
        foreach ($gallery as $slide) {
            if (!empty($slide['IS_VIDEO'])) {
                continue;
            }
            $src = (string) ($slide['SRC'] ?? $slide['BIG']['src'] ?? '');
            if ($src !== '' && strpos($src, '/upload/') !== false) {
                return true;
            }
        }

        $morePhoto = $arResult['PROPERTIES']['MORE_PHOTO']['VALUE'] ?? null;
        if (is_array($morePhoto)) {
            foreach ($morePhoto as $fileId) {
                if ((int) $fileId > 0) {
                    return true;
                }
            }
        } elseif ((int) $morePhoto > 0) {
            return true;
        }

        return false;
    }

    private static function imageDedupeKey(string $src): string
    {
        $src = trim($src);
        if ($src === '') {
            return '';
        }

        $path = (string) (parse_url($src, PHP_URL_PATH) ?? $src);
        $base = basename($path);
        $base = preg_replace('/^(small_|abovo_)/i', '', $base) ?? $base;

        if (preg_match('/_([a-f0-9]{8,})(\.[a-z0-9]+)?$/i', $base, $m)) {
            return strtolower($m[1]);
        }

        return strtolower($base);
    }

    private static function dedupeImageSlides(array $gallery): array
    {
        $out = [];
        $seen = [];

        foreach ($gallery as $slide) {
            if (!empty($slide['IS_VIDEO'])) {
                $out[] = $slide;
                continue;
            }

            $src = (string) ($slide['SRC'] ?? $slide['BIG']['src'] ?? '');
            $key = self::imageDedupeKey($src);
            if ($key === '') {
                $out[] = $slide;
                continue;
            }

            $isSmall = (bool) preg_match('#/small_#i', $src);
            if (isset($seen[$key])) {
                if ($isSmall && !$seen[$key]['is_small']) {
                    continue;
                }
                if (!$isSmall && $seen[$key]['is_small']) {
                    $out[$seen[$key]['index']] = $slide;
                    $seen[$key]['is_small'] = false;
                }
                continue;
            }

            $seen[$key] = ['index' => count($out), 'is_small' => $isSmall];
            $out[] = $slide;
        }

        return array_values($out);
    }

    private static function appendEtmImageUrls(array $gallery, array $arResult, string $name): array
    {
        $rawImageProp = $arResult['PROPERTIES']['ETM_IMAGE_URLS']['VALUE'] ?? [];
        if (!is_array($rawImageProp)) {
            $rawImageProp = [$rawImageProp];
        }

        $known = [];
        foreach ($gallery as $image) {
            if (!empty($image['IS_VIDEO'])) {
                continue;
            }
            $key = self::imageDedupeKey((string) ($image['SRC'] ?? $image['BIG']['src'] ?? ''));
            if ($key !== '') {
                $known[$key] = true;
            }
        }

        foreach ($rawImageProp as $url) {
            $url = self::encodeMediaUrl(trim((string) $url));
            if ($url === '' || !preg_match('#^https?://#i', $url)) {
                continue;
            }
            $key = self::imageDedupeKey($url);
            if ($key !== '' && isset($known[$key])) {
                continue;
            }
            if (preg_match('#/small_#i', $url) && $key !== '' && isset($known[$key])) {
                continue;
            }
            if ($key !== '') {
                $known[$key] = true;
            }

            $gallery[] = [
                'SRC' => $url,
                'BIG' => ['src' => $url],
                'PREVIEW' => ['src' => $url],
                'THUMB' => ['src' => $url],
                'ALT' => $name,
                'TITLE' => $name,
            ];
        }

        return $gallery;
    }

    private static function buildVideoSlides(array $arResult, array $photoSlides, string $name): array
    {
        $rawVideoProp = $arResult['PROPERTIES']['ETM_VIDEO_URLS']['VALUE'] ?? [];
        if (!is_array($rawVideoProp)) {
            $rawVideoProp = [$rawVideoProp];
        }

        $poster = '';
        foreach ($photoSlides as $image) {
            if (!empty($image['IS_VIDEO'])) {
                continue;
            }
            $poster = (string) ($image['BIG']['src'] ?? $image['SRC'] ?? '');
            if ($poster !== '') {
                break;
            }
        }
        if ($poster === '') {
            $poster = '/bitrix/images/main/1.gif';
        }

        $slides = [];
        $videoSeen = [];
        foreach ($rawVideoProp as $videoMarkup) {
            $videoUrl = self::extractVideoUrl((string) $videoMarkup);
            if ($videoUrl === '' || isset($videoSeen[$videoUrl])) {
                continue;
            }
            $videoSeen[$videoUrl] = true;

            $slides[] = [
                'IS_VIDEO' => true,
                'VIDEO_SRC' => $videoUrl,
                'SRC' => $poster,
                'BIG' => ['src' => $poster],
                'PREVIEW' => ['src' => $poster],
                'THUMB' => ['src' => $poster],
                'SMALL' => ['src' => $poster],
                'ALT' => $name,
                'TITLE' => $name,
            ];
        }

        return $slides;
    }
}
