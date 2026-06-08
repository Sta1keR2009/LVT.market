<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();?>

<?if ($arParams['USE_FILTER'] != 'N'):?>

    <?// show years?>
    <?if ($arYears = TSolution\Functions::getItemsYear([
            'FILTER' => $arItemFilter,
            'PARAMS' => $arParams
        ])
    ) {
        $bHasYear = (isset($_GET['year']) && (int)$_GET['year']);
        $year = ($bHasYear ? (int)$_GET['year'] : 0);?>

        <?\TSolution\Functions::showBlockHtml([
            'FILE' => '/filter/years_link.php',
            'PARAMS' => [
                'HAS_YEAR' => $bHasYear,
                'ITEMS' => $arYears,
                'YEAR' => $year,
                'ALL_ITEMS_LANG' => 'ALL_TIME',
            ],
        ])?>
        
        <?if ($bHasYear) {
            $GLOBALS[$arParams["FILTER_NAME"]][] = array(
                ">=DATE_ACTIVE_FROM" => ConvertDateTime("01.01.".$year, FORMAT_DATE,''),
                "<DATE_ACTIVE_FROM" => ConvertDateTime("01.01.".($year+1), FORMAT_DATE,''),
            );
        }?>
    <?}?>
<?endif;?>