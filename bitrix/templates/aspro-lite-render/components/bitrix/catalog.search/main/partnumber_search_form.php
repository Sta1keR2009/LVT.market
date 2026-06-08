<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

global $searchQuery;

use CLite as TSolution;

$siteId = defined('SITE_ID') ? SITE_ID : 's1';
$dbSite = CSite::GetByID($siteId);
$siteDir = '/';
if ($arSite = $dbSite->Fetch()) {
    $siteDir = $arSite['DIR'];
}

$catalogPage = trim(TSolution::GetFrontParametrValue('CATALOG_PAGE_URL'));
$catalogScriptConst = 'ASPRO_CATALOG_SCRIPT_' . $siteId;
$catalogScript = defined($catalogScriptConst) && strlen(constant($catalogScriptConst)) ? constant($catalogScriptConst) : 'index.php';
$pathFile = str_replace(['#SITE_DIR#', $catalogScript], [$siteDir, ''], $catalogPage) . $catalogScript;
$pathFile = str_replace('/index.php', '/', $pathFile);

$qVal = isset($_GET['q']) ? (string) $_GET['q'] : '';
$searchQuery = $qVal;
$examples = lvt_partnumber_search_example_articles();
$placeholder = $examples[0] ?? 'IRF6727MTRPBF';
?>
<div class="search-page-wrap lvt-partnumber-search-page-wrap">
    <form class="search lvt-partnumber-search-form" action="<?= htmlspecialchars($pathFile ?: '/catalog/', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" method="get">
        <input type="hidden" name="<?= htmlspecialchars(GetchipsPartnumberSearchHelper::MODE_PARAM, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" value="<?= htmlspecialchars(GetchipsPartnumberSearchHelper::MODE_VALUE, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"/>
        <div class="lvt-partnumber-search__row flexbox flexbox--direction-row flexbox--align-center">
            <div class="lvt-partnumber-search__input-wrap flex-1">
                <input class="form-control search-input lvt-partnumber-search__input font_16"
                       type="text"
                       name="q"
                       value="<?= htmlspecialchars($qVal, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                       size="40"
                       maxlength="80"
                       autocomplete="off"
                       placeholder="<?= htmlspecialchars($placeholder, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                       aria-label="Партномер"/>
            </div>
            <div class="search-button-div lvt-partnumber-search__actions">
                <button class="btn btn--no-rippple btn-clear-search fill-dark-light-block banner-light-icon-fill light-opacity-hover lvt-partnumber-clear"
                        type="button"
                        name="rs">
                    <?= TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH . '/images/svg/header_icons.svg#close-9-9', 'clear ', ['WIDTH' => 9, 'HEIGHT' => 9]); ?>
                </button>
                <button class="btn btn-default btn-lg lvt-partnumber-search__submit" type="submit" value="Y">
                    Найти
                </button>
            </div>
        </div>
        <?php
        if ($examples !== []) {
            $catalogUrl = htmlspecialchars($pathFile ?: '/catalog/', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $modeParam = htmlspecialchars(GetchipsPartnumberSearchHelper::MODE_PARAM, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $modeVal = htmlspecialchars(GetchipsPartnumberSearchHelper::MODE_VALUE, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            ?>
            <div class="lvt-partnumber-search__examples font_14">
                <span class="lvt-partnumber-search__examples-label">Например:</span>
                <?php foreach ($examples as $ex) {
                    $exEsc = htmlspecialchars($ex, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    $href = $catalogUrl . (strpos($catalogUrl, '?') !== false ? '&' : '?') . 'q=' . rawurlencode($ex) . '&' . $modeParam . '=' . $modeVal;
                    ?>
                    <a class="lvt-partnumber-search__example-tag btn btn-primary btn-sm" href="<?= $href ?>"><?= $exEsc ?></a>
                <?php } ?>
            </div>
        <?php } ?>
    </form>
</div>
<script>
(function () {
    var form = document.querySelector('.lvt-partnumber-search-form');
    if (!form) return;
    var input = form.querySelector('input[name="q"]');
    var clearBtn = form.querySelector('.lvt-partnumber-clear');
    if (clearBtn && input) {
        clearBtn.addEventListener('click', function () {
            input.value = '';
            input.focus();
        });
    }
})();
</script>
