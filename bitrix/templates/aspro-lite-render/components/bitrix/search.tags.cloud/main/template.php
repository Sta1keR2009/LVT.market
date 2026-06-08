<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
$templateData = array(
    'SEARCH' => $arResult["SEARCH"],
);
if(is_array($arResult["SEARCH"]) && !empty($arResult["SEARCH"])):?>
    <noindex>
        <div class="search-tags-cloud visible-by-block-presence__condition">
            <div class="tags">
                <div class="line-block line-block--6 line-block--6-vertical line-block--flex-wrap">
                    <?foreach ($arResult["SEARCH"] as $key => $res):?>
                        <div class="line-block__item">
                            <a href="<?=$res["URL"]?>" rel="nofollow" class="bordered chip chip--transparent">
                                <span class="chip__label font_14"><?=$res["NAME"]?></span>
                            </a>
                        </div>
                    <?endforeach;?>
                </div>
            </div>
        </div>
    </noindex>
<?endif;?>
