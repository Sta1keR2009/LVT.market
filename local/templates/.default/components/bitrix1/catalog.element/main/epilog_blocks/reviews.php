<?php
use Bitrix\Main\Localization\Loc;

$bTab = isset($tabCode) && $tabCode === 'reviews';
?>
<?// reviews filter?>
<?ob_start();?>
    <?Bitrix\Main\Page\Frame::getInstance()->startDynamicWithID('area');?>
    <div class="ordered-block__title switcher-title font_24 font_20--to-600">
        <?=$arParams['T_REVIEWS'];?>

        <span class="element-count-wrapper">
            <span class="element-count muted font_14 hidden"></span>
        </span>
    </div>
    <div class="comments-block__wrapper line-block line-block--gap line-block--gap-70">
        <div class="comments-block line-block__item flex-1">
            <?$APPLICATION->IncludeComponent(
                'bitrix:catalog.comments',
                'catalog',
                [
                    'CACHE_TYPE' => $arParams['CACHE_TYPE'],
                    'CACHE_TIME' => $arParams['CACHE_TIME'],
                    'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],
                    'COMMENTS_COUNT' => ($arParams['MESSAGES_PER_PAGE'] ?? 5),
                    'ELEMENT_CODE' => '',
                    'ELEMENT_ID' => $arResult['ID'],
                    'XML_ID' => $templateData['XML_ID'].(isset($templateData['OFFERS_INFO']) && isset($templateData['OFFERS_INFO']['OFFERS']) && count($templateData['OFFERS_INFO']['OFFERS']) ? '%' : ''),
                    'IBLOCK_ID' => $arParams['IBLOCK_ID'],
                    'IBLOCK_TYPE' => 'aspro_max_catalog',
                    'SHOW_DEACTIVATED' => 'N',
                    'TEMPLATE_THEME' => 'blue',
                    'URL_TO_COMMENT' => '',
                    'AJAX_POST' => 'Y',
                    'WIDTH' => '',
                    'COMPONENT_TEMPLATE' => 'catalog',
                    'BLOG_USE' => 'Y',
                    'PATH_TO_SMILE' => '/bitrix/images/blog/smile/',
                    'EMAIL_NOTIFY' => $arParams['DETAIL_BLOG_EMAIL_NOTIFY'],
                    'SHOW_SPAM' => 'Y',
                    'SHOW_RATING' => 'Y',
                    'RATING_TYPE' => 'like_graphic_catalog_reviews',
                    'MAX_IMAGE_SIZE' => $arParams['MAX_IMAGE_SIZE'],
                    'BLOG_URL' => $arParams['BLOG_URL'],
                    'REVIEW_COMMENT_REQUIRED' => $arParams['REVIEW_COMMENT_REQUIRED'],
                    'REVIEW_FILTER_BUTTONS' => $arParams['REVIEW_FILTER_BUTTONS'],
                    'REAL_CUSTOMER_TEXT' => $arParams['REAL_CUSTOMER_TEXT'],
                ],
                false, ['HIDE_ICONS' => 'Y']
            );?>
        </div>

        <div class="comments-block__reviews-info catalog-detail__right-column right_reviews_info line-block__item flex-1">
            <div class="comments-block__reviews-info-inner catalog-detail__cell-block sticky-block shadow outer-rounded-x">
                <div class="rating-wrapper flexbox flexbox--row">
                    <div class="votes_block nstar with-text<?=!$templateData['RATING'] ? ' hidden' : '';?>">
                        <div class="item-rating rating__star-svg rating__star-svg--filled">
                            <?=TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH.'/images/svg/catalog/item_icons.svg#star-13-13', '', [
                                'WIDTH' => 28,
                                'HEIGHT' => 28,
                            ]);?>
                        </div>
                    </div>

                    <div class="rating-value font_24">
                        <span class="count"><?=$templateData['RATING'] > 0 ? $templateData['RATING'] : Loc::getMessage('VOTES_RESULT_NONE');?></span>
                    </div>
                </div>
                <div class="show-comment btn btn-default blog-comment-action__link">
                    <?=GetMessage('ADD_REVIEW');?>
                </div>
            </div>
        </div>
    </div>
    <?TSolution\Comment\Review::showMicrodata($arResult['NAME']);?>
    <?Bitrix\Main\Page\Frame::getInstance()->finishDynamicWithID('area', '');?>
<?$html_reviews = ob_get_clean();?>

<?// show reviews block?>
<?if ($bTab):?>
    <?if (!isset($arTabs[$tabCode])):?>
        <?$arTabs[$tabCode] = ['classList' => []];?>
    <?else:?>
        <div class="tab-pane EXTENDED<?=TSolution\Utils::implodeClasses($arTabs[$tabCode]['classList'], leadingDelimiter: true);?>" id="reviews">
            <?if ($html_reviews && strpos($html_reviews, 'error') === false):?>
                <?=$html_reviews;?>
            <?endif;?>
        </div>
    <?endif;?>
<?else:?>
    <div class="detail-block ordered-block reviews EXTENDED">
        <?if ($html_reviews && strpos($html_reviews, 'error') === false):?>
            <?=$html_reviews;?>
        <?endif;?>
    </div>
<?endif;?>
<?TSolution\Extensions::init('uniform');?>
