<?global $sMenuContent, $isCabinet;?>
<div class="left_block visible-by-block-presence">
    <div class="sticky-block sticky-block--show-<?=$arTheme['STICKY_SIDEBAR']['VALUE'];?>">
        <?if ($isCabinet):?>
            <?$APPLICATION->IncludeComponent(
                'bitrix:menu',
                'left',
                [
                    'ROOT_MENU_TYPE' => 'cabinet',
                    'MENU_CACHE_TYPE' => 'A',
                    'MENU_CACHE_TIME' => '3600000',
                    'MENU_CACHE_USE_GROUPS' => 'N',
                    'MENU_CACHE_GET_VARS' => [],
                    'MAX_LEVEL' => '4',
                    'CHILD_MENU_TYPE' => 'left',
                    'USE_EXT' => 'Y',
                    'DELAY' => 'N',
                    'ALLOW_MULTI_SELECT' => 'Y',
                    'COMPONENT_TEMPLATE' => 'left',
                ],
                false
            );?>
        <?else:?>
            <?=$sMenuContent;?>
        <?endif;?>

        <div class="sidearea">
            <?$APPLICATION->ShowViewContent('under_sidebar_content');?>

            <?TSolution::get_banners_position('SIDE');?>

            <?ob_start();?>
                <?$APPLICATION->IncludeComponent(
                    'bitrix:main.include',
                    '',
                    [
                        'AREA_FILE_SHOW' => 'sect',
                        'AREA_FILE_SUFFIX' => 'sidebar',
                        'AREA_FILE_RECURSIVE' => 'Y'
                    ],
                    false
                );?>
            <?$htmlInclude = trim(ob_get_clean());?>
            <?if ($htmlInclude):?>
                <div class="include visible-by-block-presence__condition">
                    <?=$htmlInclude;?>
                </div>
            <?endif;?>
        </div>
    </div>
</div>
