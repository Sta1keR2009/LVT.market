<?use \Bitrix\Main\Localization\Loc;?>
<?//show order and sale block?>

<?if($templateData['ORDER_BTN']):?>
    <div class="detail-block ordered-block order_sale">
        <div class="outer-rounded-x bordered grey-bg">
            <?$APPLICATION->ShowViewContent('PRODUCT_ORDER_SALE_INFO')?>
        </div>
    </div>
<?endif;?>