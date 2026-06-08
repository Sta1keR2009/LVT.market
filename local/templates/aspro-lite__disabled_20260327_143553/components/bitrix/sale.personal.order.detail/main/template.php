<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

// Reuse the original aspro template to avoid risky copy-paste.
$originalTemplate = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/components/aspro/personal.section.lite/templates/.default/bitrix/sale.personal.order.detail/main/template.php';
if (is_readable($originalTemplate)) {
    include $originalTemplate;
}

$invoiceFileUrl = '';
$invoiceFileName = '';
$orderId = (int)($arResult['ID'] ?? 0);

if ($orderId > 0 && class_exists('\Bitrix\Sale\Order')) {
    $order = \Bitrix\Sale\Order::load($orderId);
    if ($order) {
        $collection = $order->getPropertyCollection();
        $fileProp = $collection->getItemByOrderPropertyCode('INVOICE_FILE_ID');
        if ($fileProp) {
            $value = $fileProp->getValue();
            if (is_array($value)) {
                $value = reset($value);
            }
            $fileId = (int)$value;
            if ($fileId > 0) {
                $fileUrl = \CFile::GetPath($fileId);
                if ($fileUrl) {
                    $invoiceFileUrl = $fileUrl;
                    $fileMeta = \CFile::GetByID($fileId)->Fetch();
                    $invoiceFileName = $fileMeta['ORIGINAL_NAME'] ?? ('invoice_' . $orderId . '.pdf');
                }
            }
        }
    }
}
?>
<?php if ($invoiceFileUrl): ?>
    <div class="personal__block personal__block--order" style="margin-top: 16px;">
        <div class="order__bar order__bar--grey outer-rounded-x">
            <div class="order__status__text flexbox flexbox--row flexbox--align-center" style="padding: 16px;">
                <div style="flex:1;">
                    <div class="order__status__value" style="font-weight:600;">Счет на оплату</div>
                    <div style="margin-top:4px; color:#666; font-size:13px;">
                        <?php echo htmlspecialcharsbx($invoiceFileName); ?>
                    </div>
                </div>
                <a class="btn btn-default btn-transparent-border"
                   href="<?php echo htmlspecialcharsbx($invoiceFileUrl); ?>"
                   target="_blank"
                   rel="noopener noreferrer">
                    Скачать счет
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>
