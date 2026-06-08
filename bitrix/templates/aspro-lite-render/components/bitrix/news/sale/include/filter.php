<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$bArchiveTab = isset($_GET["check_dates"]) && $_GET["check_dates"] === 'N' && $arParams["CHECK_DATES"] === "N";
?>
<?if ($arParams['USE_FILTER'] != 'N'):?>
    <?// show tabs
    $currentDate = ConvertTimeStamp(time(), "FULL");

    $bShowArchive = false;
    if (
        $arItemFilter && 
        $arParams['IBLOCK_ID'] &&
        $arParams["CHECK_DATES"] === "N"
    ) {
        $arItemFilter['<'.'DATE_ACTIVE_TO'] = $currentDate;
        unset($arItemFilter['ACTIVE_DATE']);

        $arItems = \CLiteCache::CIBlockElement_GetList(
            [
                'SORT' => 'ASC',
                'NAME' => 'ASC',
                'CACHE' => [
                    'TAG' => \CLiteCache::GetIBlockCacheTag($arParams['IBLOCK_ID'])
                ]
            ],
            $arItemFilter,
            false,
            false,
            ['ID', 'NAME', 'DATE_ACTIVE_TO']
        );

        $bShowArchive = count($arItems) > 0;
    }
    ?>
    <?if($bShowArchive):?>
        <?TSolution\Functions::showBlockHtml([
            'FILE' => '/filter/years_link.php',
            'PARAMS' => [
                'CURRENT_DATE' => $currentDate,
                'ARCHIVE_TAB' => $bArchiveTab,
                'SHOW_ARCHICE' => $bShowArchive,
                'ALL_ITEMS_LANG' => $arParams["ALL_TIME"] ? $arParams["ALL_TIME"] : GetMessage("ALL_TIME"),
                'ARCHIVE_ITEMS_LANG' => $arParams["ARCHIVE"] ? $arParams["ARCHIVE"] : GetMessage("ARCHIVE"),
            ],
        ]);?>
    <?endif;?>
    <?
    if($bArchiveTab) {
        $GLOBALS[$arParams["FILTER_NAME"]][] = array(
            "LOGIC" => "OR",
            array("<DATE_ACTIVE_TO" => $currentDate),
            array("DATE_ACTIVE_TO" => true),
        );
    }
    else {
        $arParams["CHECK_DATES"] = "Y";
    }
    ?>
<?endif;?>