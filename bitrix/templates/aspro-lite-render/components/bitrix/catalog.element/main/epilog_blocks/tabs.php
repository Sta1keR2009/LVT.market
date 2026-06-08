<?php
/**
 * @var $arTabOrder - default tab order
 * @var $arTabs = [
 *  'code' => [
 *      'classList' => array
 *  ],
 *  ...
 * ]
 */
$arTabs = [];
$hasActiveTab = false;
foreach ($arTabOrder as $tabCode) {
    include $tabCode.'.php';

    if (isset($arTabs[$tabCode])) {
        // set active tab
        if (!$hasActiveTab && !in_array('hidden', $arTabs[$tabCode]['classList'])) {
            $arTabs[$tabCode]['classList'][] = 'active';
            $hasActiveTab = true;
        }
    }
}
?>

<?if ($arTabs):?>
    <div class="detail-block ordered-block tabs-block">
        <?if (count($arTabs) > 1):?>
            <div class="tabs tabs-history arrow_scroll">
                <ul class="nav nav-tabs font_14--to-600">
                    <?foreach ($arTabs as $tabCode => $options):?>
                        <li <?=$options['classList'] ? 'class="'.TSolution\Utils::implodeClasses($options['classList']).'"' : '';?>>
                            <a href="#<?=$tabCode;?>" data-toggle="tab">
                                <?=$arParams['T_'.mb_strtoupper($tabCode)];?>
                            </a>
                        </li>
                    <?endforeach;?>
                </ul>
            </div>
        <?endif;?>

        <div class="tab-content">
            <?php
            foreach ($arTabs as $tabCode => $options) {
                include $tabCode.'.php';
            }
            ?>
        </div>
    </div>
<?endif;?>
