<?$bTab = isset($tabCode) && $tabCode === 'char';?>
<?// show char block?>
<?if ($templateData['SHOW_CHARACTERISTICS']):?>
    <?if ($bTab):?>
        <?if (!isset($arTabs[$tabCode])):?>
            <?
            $arTabs[$tabCode] = ['classList' => []];
            if (empty($templateData['VISIBLE_PROPS_BLOCK'])) {
                $arTabs[$tabCode]['classList'][] = 'hidden';
            }
            ?>
        <?else:?>
            <div class="tab-pane<?=TSolution\Utils::implodeClasses($arTabs[$tabCode]['classList'], leadingDelimiter: true);?>" id="char">
                <div class="js-lvt-char-collapse" data-limit="10">
                    <?$APPLICATION->ShowViewContent('PRODUCT_PROPS_INFO');?>
                    <button type="button" class="catalog-detail__pseudo-link pointer dark_link font_13 js-lvt-char-show-all hidden">
                        <span class="choise dotted">Показать все</span>
                    </button>
                </div>
            </div>
        <?endif;?>
    <?else:?>
        <div class="detail-block ordered-block char<?=empty($templateData['VISIBLE_PROPS_BLOCK']) ? ' hidden' : '';?>">
            <h3 class="switcher-title"><?=$arParams['T_CHAR'];?></h3>
            <div class="js-lvt-char-collapse" data-limit="10">
                <?$APPLICATION->ShowViewContent('PRODUCT_PROPS_INFO');?>
                <button type="button" class="catalog-detail__pseudo-link pointer dark_link font_13 js-lvt-char-show-all hidden">
                    <span class="choise dotted">Показать все</span>
                </button>
            </div>
        </div>
    <?endif;?>
    <?TSolution\Extensions::init(['chars']);?>
    <script>
        (function () {
            function collectCharItems(root) {
                var selectors = [
                    '.props_block .char',
                    '.properties.list .properties__item',
                    '.properties.table .properties__item',
                    '.properties .properties__item',
                    '.properties-group__item'
                ];
                var seen = new Set();
                var out = [];

                selectors.forEach(function (selector) {
                    root.querySelectorAll(selector).forEach(function (el) {
                        if (seen.has(el)) {
                            return;
                        }
                        seen.add(el);
                        out.push(el);
                    });
                });

                return out;
            }

            function initCharCollapse(scope) {
                (scope || document).querySelectorAll('.js-lvt-char-collapse').forEach(function (wrap) {
                    if (wrap.dataset.lvtCharCollapseInit === 'Y') {
                        return;
                    }

                    var limit = parseInt(wrap.dataset.limit || '10', 10);
                    if (isNaN(limit) || limit < 1) {
                        limit = 10;
                    }

                    var items = collectCharItems(wrap);
                    var btn = wrap.querySelector('.js-lvt-char-show-all');

                    if (!btn || items.length <= limit) {
                        if (btn) {
                            btn.classList.add('hidden');
                        }
                        wrap.dataset.lvtCharCollapseInit = 'Y';
                        return;
                    }

                    items.forEach(function (item, idx) {
                        if (idx >= limit) {
                            item.classList.add('hidden');
                            item.setAttribute('data-lvt-char-hidden', 'Y');
                        }
                    });

                    btn.classList.remove('hidden');
                    btn.addEventListener('click', function () {
                        wrap.querySelectorAll('[data-lvt-char-hidden="Y"]').forEach(function (item) {
                            item.classList.remove('hidden');
                            item.removeAttribute('data-lvt-char-hidden');
                        });
                        btn.classList.add('hidden');
                    });

                    wrap.dataset.lvtCharCollapseInit = 'Y';
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function () {
                    initCharCollapse(document);
                });
            } else {
                initCharCollapse(document);
            }
        })();
    </script>
<?endif;?>
