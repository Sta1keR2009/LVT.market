<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * Блок вариантов ETM (similar products by attribute) для IB 41.
 */

$iblockId = (int) ($arResult['IBLOCK_ID'] ?? 0);
$elementId = (int) ($arResult['ID'] ?? 0);

if ($iblockId !== 41 || $elementId <= 0) {
    return;
}

$variantOptions = [];
$attrName = '';

$res = CIBlockElement::GetProperty($iblockId, $elementId, [], ['CODE' => 'ETM_VARIANT_OPTIONS']);
while ($row = $res->Fetch()) {
    $val = trim((string) ($row['VALUE'] ?? ''));
    if ($val !== '') {
        $variantOptions[] = $val;
    }
}

if ($variantOptions === []) {
    return;
}

$res = CIBlockElement::GetProperty($iblockId, $elementId, [], ['CODE' => 'ETM_SIMILAR_ATTR']);
if ($row = $res->Fetch()) {
    $attrName = trim((string) ($row['VALUE'] ?? ''));
}

$helperPath = $_SERVER['DOCUMENT_ROOT'] . '/api_etm_ai/includes/etm_element_code.php';
if (is_file($helperPath)) {
    require_once $helperPath;
}

$variants = [];
foreach ($variantOptions as $optionLine) {
    $parts = explode('|', $optionLine);
    if (count($parts) < 2) {
        continue;
    }

    $label = trim((string) $parts[0]);
    $etmCode = trim((string) $parts[1]);
    $selected = strtoupper(trim((string) ($parts[2] ?? 'N'))) === 'Y';

    if ($etmCode === '') {
        continue;
    }

    $targetId = function_exists('etmFindElementIdByEtmCode')
        ? etmFindElementIdByEtmCode($iblockId, $etmCode)
        : 0;

    $url = '';
    if ($targetId > 0) {
        $row = CIBlockElement::GetList(
            [],
            ['IBLOCK_ID' => $iblockId, 'ID' => $targetId],
            false,
            ['nTopCount' => 1],
            ['ID', 'DETAIL_PAGE_URL']
        )->GetNext();
        if ($row) {
            $url = (string) ($row['DETAIL_PAGE_URL'] ?? '');
        }
    }

    $variants[] = [
        'LABEL' => $label !== '' ? $label : $etmCode,
        'URL' => $url,
        'SELECTED' => $selected || $targetId === $elementId,
        'AVAILABLE' => $url !== '',
    ];
}

if ($variants === []) {
    return;
}

$blockTitle = $attrName !== '' ? $attrName : 'Варианты';
$placement = isset($etmVariantsPlacement) ? (string)$etmVariantsPlacement : 'epilog';
$selectId = 'etm-variants-select-' . $elementId;
?>

<style>
.etm-variants-block {
    margin: 24px 0;
    padding: 20px;
    background: var(--card_bg_black, #fff);
    border: 1px solid var(--stroke_black, #eaeaea);
    border-radius: 12px;
}

.etm-variants-block__title {
    font-size: 18px;
    font-weight: 600;
    color: var(--white_text_black, #222);
    margin: 0 0 14px;
}

.etm-variants-block__list {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.etm-variant-chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 38px;
    padding: 8px 16px;
    border: 1px solid #163760;
    border-radius: 8px;
    color: #163760;
    background: var(--black_bg_black, #fff);
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s ease;
}

.etm-variant-chip:hover {
    background: var(--darkerblack_bg_black, #f0f5fa);
    color: #163760;
}

.etm-variant-chip--selected {
    background: #163760;
    border-color: #163760;
    color: #fff;
    cursor: default;
    pointer-events: none;
}

.etm-variant-chip--disabled {
    border-color: var(--stroke_black, #ccc);
    color: var(--lite_basic_text_black, #999);
    background: var(--darkgrey_bg_black, transparent);
    cursor: not-allowed;
    pointer-events: none;
}

.etm-variants-sidebar {
    margin: 0 0 12px;
    padding: 14px;
    border: 1px solid var(--stroke_black, #e8edf5);
    border-radius: 10px;
    background: var(--card_bg_black, #fff);
}

.etm-variants-sidebar__title {
    font-size: 13px;
    color: var(--lite_basic_text_black, #5d6b82);
    margin: 0 0 8px;
}

.etm-variants-sidebar__row {
    display: grid;
    grid-template-columns: 1fr;
    gap: 8px;
}

.etm-variants-sidebar__label {
    font-size: 12px;
    color: var(--lite_basic_text_black, #8b97a8);
}

.etm-variants-sidebar__select {
    width: 100%;
    min-height: 38px;
    border: 1px solid var(--stroke_black, #dce3ee);
    border-radius: 8px;
    padding: 6px 10px;
    font-size: 14px;
    color: var(--white_text_black, #1f2a3d);
    background: var(--black_bg_black, #fff);
}

.etm-variants-block--in-column {
    margin: 0 0 12px;
    padding: 14px 16px;
    width: 100%;
    box-sizing: border-box;
}

.etm-variants-block--in-column .etm-variants-block__title {
    font-size: 15px;
    margin-bottom: 10px;
}

.etm-variants-block--in-column .etm-variants-block__list {
    gap: 8px;
}

.etm-variants-block--in-column .etm-variant-chip {
    min-height: 34px;
    padding: 6px 12px;
    font-size: 13px;
}

.theme-dark .etm-variants-block,
.theme-dark .etm-variants-sidebar {
    background: var(--card_bg_black, #2a2a2a);
    border-color: var(--stroke_black, #404040);
}

.theme-dark .etm-variants-block__title,
.theme-dark .etm-variants-sidebar__title {
    color: var(--white_text_black, #e8edf7);
}

.theme-dark .etm-variants-sidebar__label {
    color: var(--lite_basic_text_black, #b8c0cc);
}

.theme-dark .etm-variant-chip {
    border-color: #5a8fd4;
    color: #dbe2ef;
    background: var(--black_bg_black, #1e1e1e);
}

.theme-dark .etm-variant-chip:hover {
    background: var(--darkerblack_bg_black, #2f3540);
    color: #fff;
    border-color: #7aa8e8;
}

.theme-dark .etm-variant-chip--selected {
    background: #163760;
    border-color: #163760;
    color: #fff;
}

.theme-dark .etm-variant-chip--disabled {
    border-color: var(--stroke_black, #505050);
    color: var(--lite_basic_text_black, #777);
    background: var(--darkgrey_bg_black, #252525);
}

.theme-dark .etm-variants-sidebar__select {
    background: var(--black_bg_black, #1e1e1e);
    border-color: var(--stroke_black, #505050);
    color: var(--white_text_black, #e8edf7);
}

@media (prefers-color-scheme: dark) {
    .theme-default .etm-variants-block,
    .theme-default .etm-variants-sidebar {
        background: var(--card_bg_black, #2a2a2a);
        border-color: var(--stroke_black, #404040);
    }

    .theme-default .etm-variants-block__title,
    .theme-default .etm-variants-sidebar__title {
        color: var(--white_text_black, #e8edf7);
    }

    .theme-default .etm-variant-chip {
        border-color: #5a8fd4;
        color: #dbe2ef;
        background: var(--black_bg_black, #1e1e1e);
    }

    .theme-default .etm-variant-chip:hover {
        background: var(--darkerblack_bg_black, #2f3540);
        color: #fff;
    }

    .theme-default .etm-variant-chip--selected {
        background: #163760;
        border-color: #163760;
        color: #fff;
    }

    .theme-default .etm-variants-sidebar__select {
        background: var(--black_bg_black, #1e1e1e);
        border-color: var(--stroke_black, #505050);
        color: var(--white_text_black, #e8edf7);
    }
}
</style>

<?php if ($placement === 'sidebar'): ?>
    <div class="etm-variants-sidebar">
        <div class="etm-variants-sidebar__title">Варианты</div>
        <div class="etm-variants-sidebar__row">
            <label class="etm-variants-sidebar__label" for="<?= htmlspecialchars($selectId) ?>">
                <?= htmlspecialchars($blockTitle) ?>
            </label>
            <select id="<?= htmlspecialchars($selectId) ?>" class="etm-variants-sidebar__select">
                <?php foreach ($variants as $variant): ?>
                    <option
                        value="<?= htmlspecialchars((string)$variant['URL']) ?>"
                        <?= $variant['SELECTED'] ? 'selected' : '' ?>
                        <?= !$variant['AVAILABLE'] ? 'disabled' : '' ?>
                    >
                        <?= htmlspecialchars($variant['LABEL']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <script>
        (function () {
            var select = document.getElementById('<?= CUtil::JSEscape($selectId) ?>');
            if (!select) {
                return;
            }
            select.addEventListener('change', function () {
                var targetUrl = this.value || '';
                if (targetUrl) {
                    window.location.href = targetUrl;
                }
            });
        })();
    </script>
<?php elseif ($placement === 'column'): ?>
    <div class="etm-variants-block etm-variants-block--in-column">
        <h3 class="etm-variants-block__title"><?= htmlspecialchars($blockTitle) ?></h3>
        <div class="etm-variants-block__list">
            <?php foreach ($variants as $variant): ?>
                <?php
                $chipClass = 'etm-variant-chip';
                if ($variant['SELECTED']) {
                    $chipClass .= ' etm-variant-chip--selected';
                } elseif (!$variant['AVAILABLE']) {
                    $chipClass .= ' etm-variant-chip--disabled';
                }
                ?>
                <?php if ($variant['SELECTED']): ?>
                    <span class="<?= $chipClass ?>"><?= htmlspecialchars($variant['LABEL']) ?></span>
                <?php elseif ($variant['AVAILABLE']): ?>
                    <a href="<?= htmlspecialchars($variant['URL']) ?>" class="<?= $chipClass ?>">
                        <?= htmlspecialchars($variant['LABEL']) ?>
                    </a>
                <?php else: ?>
                    <span class="<?= $chipClass ?>"><?= htmlspecialchars($variant['LABEL']) ?></span>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
<?php else: ?>
    <div class="etm-variants-block detail-block ordered-block etm_variants">
        <h3 class="etm-variants-block__title"><?= htmlspecialchars($blockTitle) ?></h3>
        <div class="etm-variants-block__list">
            <?php foreach ($variants as $variant): ?>
                <?php
                $chipClass = 'etm-variant-chip';
                if ($variant['SELECTED']) {
                    $chipClass .= ' etm-variant-chip--selected';
                } elseif (!$variant['AVAILABLE']) {
                    $chipClass .= ' etm-variant-chip--disabled';
                }
                ?>
                <?php if ($variant['SELECTED']): ?>
                    <span class="<?= $chipClass ?>"><?= htmlspecialchars($variant['LABEL']) ?></span>
                <?php elseif ($variant['AVAILABLE']): ?>
                    <a href="<?= htmlspecialchars($variant['URL']) ?>" class="<?= $chipClass ?>">
                        <?= htmlspecialchars($variant['LABEL']) ?>
                    </a>
                <?php else: ?>
                    <span class="<?= $chipClass ?>"><?= htmlspecialchars($variant['LABEL']) ?></span>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>
