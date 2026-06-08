<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    define('B_PROLOG_INCLUDED', true);
}

$company = isset($data['company']) ? $data['company'] : [];
$buyer = isset($data['buyer']) ? $data['buyer'] : [];
$positions = isset($data['positions']) && is_array($data['positions']) ? $data['positions'] : [];
$invoiceNumber = isset($data['invoice_number']) ? $data['invoice_number'] : '';
$invoiceDate = isset($data['invoice_date']) ? $data['invoice_date'] : '';
$currency = isset($data['currency']) ? $data['currency'] : 'RUB';
$total = isset($data['total']) ? (float)$data['total'] : 0.0;

function invoiceFmt($value)
{
    return number_format((float)$value, 2, '.', ' ');
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Счет <?php echo htmlspecialcharsbx($invoiceNumber); ?></title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 20px; margin: 0 0 10px; }
        .muted { color: #666; }
        .block { margin: 0 0 14px; }
        .line { margin: 0 0 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; vertical-align: top; }
        th { background: #f5f5f5; text-align: left; }
        .num { text-align: right; white-space: nowrap; }
        .total { margin-top: 12px; text-align: right; font-size: 14px; font-weight: 700; }
    </style>
</head>
<body>
    <div class="block">
        <h1>Счет на оплату № <?php echo htmlspecialcharsbx($invoiceNumber); ?></h1>
        <div class="muted">от <?php echo htmlspecialcharsbx($invoiceDate); ?></div>
    </div>

    <div class="block">
        <div class="line"><strong>Поставщик:</strong> <?php echo htmlspecialcharsbx(isset($company['name']) ? $company['name'] : ''); ?></div>
        <?php if (!empty($company['inn'])): ?><div class="line">ИНН: <?php echo htmlspecialcharsbx($company['inn']); ?></div><?php endif; ?>
        <?php if (!empty($company['kpp'])): ?><div class="line">КПП: <?php echo htmlspecialcharsbx($company['kpp']); ?></div><?php endif; ?>
        <?php if (!empty($company['bank'])): ?><div class="line">Банк: <?php echo htmlspecialcharsbx($company['bank']); ?></div><?php endif; ?>
        <?php if (!empty($company['account'])): ?><div class="line">Р/с: <?php echo htmlspecialcharsbx($company['account']); ?></div><?php endif; ?>
    </div>

    <div class="block">
        <div class="line"><strong>Покупатель:</strong> <?php echo htmlspecialcharsbx(isset($buyer['name']) ? $buyer['name'] : ''); ?></div>
        <?php if (!empty($buyer['inn'])): ?><div class="line">ИНН: <?php echo htmlspecialcharsbx($buyer['inn']); ?></div><?php endif; ?>
        <?php if (!empty($buyer['email'])): ?><div class="line">Email: <?php echo htmlspecialcharsbx($buyer['email']); ?></div><?php endif; ?>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 45px;">#</th>
                <th>Товар</th>
                <th class="num" style="width: 90px;">Кол-во</th>
                <th class="num" style="width: 120px;">Цена</th>
                <th class="num" style="width: 120px;">Сумма</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($positions as $idx => $item): ?>
                <tr>
                    <td><?php echo (int)$idx + 1; ?></td>
                    <td><?php echo htmlspecialcharsbx(isset($item['name']) ? $item['name'] : ''); ?></td>
                    <td class="num"><?php echo invoiceFmt(isset($item['quantity']) ? $item['quantity'] : 0); ?></td>
                    <td class="num"><?php echo invoiceFmt(isset($item['price']) ? $item['price'] : 0); ?> <?php echo htmlspecialcharsbx($currency); ?></td>
                    <td class="num"><?php echo invoiceFmt(isset($item['sum']) ? $item['sum'] : 0); ?> <?php echo htmlspecialcharsbx($currency); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="total">
        Итого к оплате: <?php echo invoiceFmt($total); ?> <?php echo htmlspecialcharsbx($currency); ?>
    </div>
</body>
</html>
