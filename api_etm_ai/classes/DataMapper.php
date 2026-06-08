<?php
/**
 * Нормализация ответа API ETM к единому формату для импорта.
 * Поддержка разных структур: result, data, products, items и т.п.
 */

class ApiEtmDataMapper
{
    private static array $nameKeys = ['name', 'title', 'naimenovanie', 'product_name', 'название', 'наименование'];
    private static array $descKeys = ['description', 'desc', 'opisanie', 'описание', 'detail_text'];
    private static array $codeKeys = ['id', 'code', 'article', 'item_id', 'art', 'код', 'артикул', 'cod'];
    private static array $priceKeys = ['price', 'retail_price', 'цена', 'price_rub', 'cost'];
    private static array $qtyKeys = ['quantity', 'stock', 'balance', 'qty', 'available', 'остаток', 'количество'];
    private static array $imgKeys = ['image', 'image_url', 'photo', 'photo_url', 'img', 'картинка', 'изображение'];
    private static array $imgsKeys = ['images', 'photos', 'gallery', 'pictures'];

    /**
     * Из сырого ответа API извлечь список товаров.
     * @return array<int, array{name: string, description: string, etm_code: string, price: float, quantity: int, image_url: ?string, images: array, attributes: array}>
     */
    public static function extractProducts(array $raw): array
    {
        $list = self::findList($raw);
        if ($list === null) {
            return [];
        }
        $out = [];
        foreach ($list as $i => $item) {
            if (!is_array($item)) {
                continue;
            }
            $n = self::normalizeProduct($item);
            if ($n['etm_code'] !== '' || $n['name'] !== '') {
                $out[] = $n;
            }
        }
        return $out;
    }

    private static function findList(array $raw): ?array
    {
        foreach (['result', 'data', 'products', 'items', 'rows', 'list', 'goods'] as $k) {
            if (isset($raw[$k]) && is_array($raw[$k])) {
                $v = $raw[$k];
                if (isset($v[0]) && is_array($v[0])) {
                    return $v;
                }
                if (isset($v['items']) && is_array($v['items'])) {
                    return $v['items'];
                }
            }
        }
        if (isset($raw[0]) && is_array($raw[0])) {
            return $raw;
        }
        return null;
    }

    private static function pick(array $item, array $keys): ?string
    {
        foreach ($keys as $k) {
            if (array_key_exists($k, $item) && (is_string($item[$k]) || is_numeric($item[$k]))) {
                return trim((string)$item[$k]);
            }
        }
        return null;
    }

    private static function normalizeProduct(array $item): array
    {
        $name = self::pick($item, self::$nameKeys) ?: '';
        $desc = self::pick($item, self::$descKeys) ?: '';
        $code = self::pick($item, self::$codeKeys) ?: (string)($item['id'] ?? '');
        $price = (float)(self::pick($item, self::$priceKeys) ?: 0);
        $qty = (int)(self::pick($item, self::$qtyKeys) ?: 0);
        $img = self::pick($item, self::$imgKeys);
        $imgs = [];
        foreach (self::$imgsKeys as $k) {
            if (!empty($item[$k]) && is_array($item[$k])) {
                foreach ($item[$k] as $u) {
                    if (is_string($u) && $u !== '') {
                        $imgs[] = $u;
                    }
                }
            }
        }
        if ($img && !in_array($img, $imgs, true)) {
            array_unshift($imgs, $img);
        }

        $attrs = [];
        $skip = array_merge(
            self::$nameKeys,
            self::$descKeys,
            self::$codeKeys,
            self::$priceKeys,
            self::$qtyKeys,
            self::$imgKeys,
            self::$imgsKeys,
            ['id', 'category', 'category_id', 'section', 'parent_id']
        );
        foreach ($item as $k => $v) {
            if (in_array($k, $skip, true) || $v === null || $v === '') {
                continue;
            }
            if (is_scalar($v)) {
                $attrs[$k] = $v;
            }
        }

        return [
            'name' => $name,
            'description' => $desc,
            'etm_code' => $code,
            'price' => $price,
            'quantity' => $qty,
            'image_url' => $img,
            'images' => $imgs,
            'attributes' => $attrs,
            '_raw' => $item,
        ];
    }
}
