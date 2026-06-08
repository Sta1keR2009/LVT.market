<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

$productId = (int)($_GET['product_id'] ?? 0);
$quantity = (int)($_GET['quantity'] ?? 1);
$city = trim((string)($_GET['city'] ?? ''));

$apiUrl = '/local/api/catapulto_delivery_quote.php';
$query = [
    'product_id' => $productId,
    'quantity' => max(1, $quantity),
];
if ($city !== '') {
    $query['city'] = $city;
}

?><!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Catapulto Delivery Demo</title>
    <style>
        body{font-family:Arial,sans-serif;background:#f7f7f7;color:#222;margin:20px}
        .wrap{max-width:980px;margin:0 auto;background:#fff;border:1px solid #e5e5e5;padding:20px}
        h1{margin:0 0 16px}
        form{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px}
        input{padding:8px;border:1px solid #ccc;border-radius:4px}
        button{padding:8px 14px;border:0;background:#163760;color:#fff;border-radius:4px;cursor:pointer}
        .note{font-size:13px;color:#666;margin:8px 0}
        table{width:100%;border-collapse:collapse;margin-top:10px}
        th,td{padding:10px;border-bottom:1px solid #ececec;text-align:left}
        .mono{font-family:Consolas,monospace;font-size:12px;background:#fafafa;padding:10px;border:1px solid #eee;overflow:auto}
    </style>
</head>
<body>
<div class="wrap">
    <h1>Демо расчета Catapulto</h1>
    <form method="get">
        <input type="number" name="product_id" min="1" placeholder="product_id" value="<?=htmlspecialchars((string)$productId)?>" required>
        <input type="number" name="quantity" min="1" placeholder="quantity" value="<?=htmlspecialchars((string)$quantity)?>">
        <input type="text" name="city" placeholder="Город (необязательно)" value="<?=htmlspecialchars($city)?>">
        <button type="submit">Рассчитать</button>
    </form>

    <div class="note">Этот экран безопасный и изолированный: работает только через <code>/local/api/...</code>.</div>

    <?php if ($productId > 0): ?>
        <?php
            $response = @file_get_contents($apiUrl . '?' . http_build_query($query));
            $data = $response ? json_decode($response, true) : null;
        ?>

        <?php if (is_array($data) && !empty($data['ok'])): ?>
            <h2>Способы доставки в <?=htmlspecialchars((string)$data['city'])?></h2>
            <div class="note">Доставка <?=htmlspecialchars((string)$data['productName'])?>, количество: <?=htmlspecialchars((string)$data['quantity'])?></div>
            <table>
                <thead>
                <tr>
                    <th>Служба</th>
                    <th>Срок</th>
                    <th>Стоимость</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach (($data['deliveries'] ?? []) as $item): ?>
                    <tr>
                        <td><?=htmlspecialchars((string)($item['name'] ?? ''))?></td>
                        <td><?=htmlspecialchars((string)($item['periodText'] ?? ''))?></td>
                        <td><?=htmlspecialchars((string)($item['priceFormatted'] ?? ''))?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <div class="note">* <?=htmlspecialchars((string)($data['notice'] ?? ''))?></div>
            <div class="note">* <?=htmlspecialchars((string)($data['disclaimer'] ?? ''))?></div>
        <?php else: ?>
            <div class="note" style="color:#a00">Ошибка расчета: <?=htmlspecialchars((string)($data['error'] ?? 'empty response'))?></div>
        <?php endif; ?>

        <h3>Raw JSON</h3>
        <div class="mono"><?=htmlspecialchars((string)$response)?></div>
    <?php endif; ?>
</div>
</body>
</html>
