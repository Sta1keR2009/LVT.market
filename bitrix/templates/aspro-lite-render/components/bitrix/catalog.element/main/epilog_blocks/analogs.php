<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * Блок «Товары-анalogи» для IB 11 (promelec) и IB 41 (element ID / kod_tovara_).
 */

$elementId = (int) ($arResult['ID'] ?? 0);
$iblockId = (int) ($arResult['IBLOCK_ID'] ?? 0);

if ($elementId <= 0 || $iblockId <= 0) {
    return;
}

$fullAnalogCodes = '';
$interchangeableCodes = '';

$res = CIBlockElement::GetProperty(
    $iblockId,
    $elementId,
    [],
    ["CODE" => "FULL_ANALOG_CODES"]
);
if ($prop = $res->Fetch()) {
    $fullAnalogCodes = trim((string) ($prop['VALUE'] ?? ''));
}

$res = CIBlockElement::GetProperty(
    $iblockId,
    $elementId,
    [],
    ["CODE" => "INTERCHANGEABLE_ANALOG_CODES"]
);
if ($prop = $res->Fetch()) {
    $interchangeableCodes = trim((string) ($prop['VALUE'] ?? ''));
}

if ($fullAnalogCodes === '' && $interchangeableCodes === '') {
    return;
}

$helperPath = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/LvtCatalogAnalogsHelper.php';
if (!is_file($helperPath)) {
    return;
}
require_once $helperPath;

$allAnalogs = LvtCatalogAnalogsHelper::getAnalogItems(
    $iblockId,
    $elementId,
    $fullAnalogCodes,
    $interchangeableCodes
);

if ($allAnalogs === []) {
    return;
}

$visibleCount = 2;
$totalCount = count($allAnalogs);
$hiddenCount = $totalCount - $visibleCount;
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
.analog-section {
    margin: 0;
    background-color: var(--card_bg_black, #fff);
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    border: 1px solid var(--stroke_black, #eaeaea);
    overflow: hidden;
}

.analog-section-card {
    display: flex;
    align-items: center;
    padding: 20px;
    background-color: var(--card_bg_black, #fff);
    position: relative;
}

.analog-section-card.hidden {
    display: none;
}

.analog-divider {
    height: 1px;
    background-color: var(--stroke_black, #eaeaea);
    margin: 0;
}

.analog-divider.hidden {
    display: none;
}

.analog-sction-card-picture {
    flex: 0 0 auto;
    width: 104px;
    height: 104px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 25px;
    border: 1px solid var(--stroke_black, #eaeaea);
    border-radius: 8px;
    overflow: hidden;
    background-color: var(--darkgrey_bg_black, #f9f9f9);
}

.analog-sction-card-picture img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.product-info {
    flex: 1;
    min-width: 0;
    padding-right: 25px;
    display: flex;
    flex-direction: column;
}

.product-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 8px;
}

.product-title {
    flex: 1;
    min-width: 0;
}

.product-info h4 {
    font-size: 18px;
    color: var(--white_text_black, #222222);
    margin-bottom: 0;
    font-weight: 500;
    line-height: 1.3;
}

.product-info h4 a {
    color: inherit;
}

.analog-type {
    display: inline-block;
    background-color: #163760A3;
    color: #fff;
    font-size: 12px;
    font-weight: 600;
    padding: 0px 12px;
    border-radius: 8px;
    margin-left: 15px;
    white-space: nowrap;
    flex-shrink: 0;
}

.analog-manufacturer {
    font-size: 14px;
    color: var(--lite_basic_text_black, #666);
    margin-top: 5px;
}

.analog-manufacturer strong {
    color: var(--basic_text_black, #444);
}

.divider {
    width: 1px;
    height: 70px;
    background-color: var(--stroke_black, #e0e0e0);
    margin: 0 25px;
    flex-shrink: 0;
}

.availability-section {
    flex: 0 0 auto;
    width: 180px;
    padding-right: 25px;
}

.analog-availability {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.analog-availability p {
    font-size: 14px;
    line-height: 1.4;
}

.analog-availability strong {
    color: var(--basic_text_black, #444);
    font-weight: 600;
}

.out-of-stock,
.in-stock,
.balance-to-order {
    color: #5a8fd4;
    font-weight: 500;
}

.balance-to-order {
    font-weight: 600;
}

.divider-2 {
    width: 1px;
    height: 70px;
    background-color: var(--stroke_black, #e0e0e0);
    margin: 0 25px;
    flex-shrink: 0;
}

.price-section {
    flex: 0 0 auto;
    display: flex;
    align-items: center;
    gap: 25px;
}

.analog-price {
    display: flex;
    flex-direction: column;
}

.analog-price .price {
    font-size: 21px;
    font-weight: 500;
    color: var(--white_text_black, #163760);
}

.analog-section .btn {
    background-color: #163760;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 12px 24px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
}

.analog-section .btn:hover {
    background-color: #1f4d8a;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(22, 55, 96, 0.3);
    color: #fff;
}

.analog-section .btn-small {
    padding: 10px 20px;
    font-size: 15px;
}

.show-more-container {
    padding: 20px;
    text-align: center;
    border-top: 1px solid var(--stroke_black, #eaeaea);
}

.show-more-btn {
    background-color: transparent;
    color: var(--white_text_black, #163760);
    border: 1px solid #163760;
    border-radius: 6px;
    padding: 10px 30px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.show-more-btn:hover {
    background-color: #163760;
    color: #fff;
}

.analog-count {
    display: inline-block;
    background-color: #163760;
    color: #fff;
    font-size: 14px;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 20px;
    margin-left: 10px;
}

.analog-section-title {
    font-size: 24px;
    font-weight: 700;
    color: var(--white_text_black, #222222);
    margin: 0 0 16px;
}

.lvt-product-analogs {
    margin-top: 2.92rem;
}

.theme-dark .analog-section-title,
.theme-dark .lvt-product-analogs .product-info h4,
.theme-dark .lvt-product-analogs .analog-price .price {
    color: var(--white_text_black, #e8edf7) !important;
}

.theme-dark .analog-section,
.theme-dark .analog-section-card {
    background: var(--card_bg_black, #2a2a2a) !important;
    border-color: var(--stroke_black, #404040) !important;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.35) !important;
}

.theme-dark .analog-sction-card-picture {
    background: var(--black_bg_black, #1e1e1e) !important;
    border-color: var(--stroke_black, #404040) !important;
}

.theme-dark .analog-manufacturer,
.theme-dark .analog-availability p {
    color: var(--lite_basic_text_black, #b8c0cc) !important;
}

.theme-dark .analog-manufacturer strong,
.theme-dark .analog-availability strong {
    color: var(--basic_text_black, #d8dde6) !important;
}

.theme-dark .show-more-btn {
    color: #dbe2ef !important;
    border-color: #5a8fd4 !important;
}

.theme-dark .show-more-btn:hover {
    background: #163760 !important;
    color: #fff !important;
}

@media (prefers-color-scheme: dark) {
    .theme-default .analog-section-title {
        color: var(--white_text_black, #e8edf7) !important;
    }

    .theme-default .analog-section,
    .theme-default .analog-section-card {
        background: var(--card_bg_black, #2a2a2a) !important;
        border-color: var(--stroke_black, #404040) !important;
    }
}

@media (max-width: 992px) {
    .analog-section-card {
        flex-wrap: wrap;
        padding: 15px;
    }

    .product-info {
        flex: 1 1 100%;
        order: 2;
        padding-right: 0;
        margin-top: 15px;
    }

    .product-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .analog-type {
        margin-left: 0;
        margin-top: 8px;
    }

    .analog-sction-card-picture {
        order: 1;
        margin-right: 15px;
    }

    .divider, .divider-2 {
        display: none;
    }

    .availability-section,
    .price-section {
        flex: 1;
        width: auto;
        padding-right: 0;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid var(--stroke_black, #eaeaea);
    }

    .price-section {
        justify-content: space-between;
    }
}
</style>

<div id="lvt-product-analogs" class="lvt-product-analogs detail-block ordered-block">
<h3 class="analog-section-title">Аналоги и замены</h3>
<div class="analog-section">
    <?php foreach ($allAnalogs as $index => $item): ?>
        <?php
        $cardClass = ($index >= $visibleCount) ? 'analog-section-card hidden' : 'analog-section-card';
        $dividerClass = ($index > 0 && $index < $visibleCount) ? 'analog-divider' : 'analog-divider hidden';
        $analogTypeLabel = ($item['ANALOG_TYPE'] === 'full') ? 'Полный аналог' : 'Функциональный аналог';
        $analogTypeClass = ($item['ANALOG_TYPE'] === 'full') ? 'analog-type full' : 'analog-type interchangeable';
        $inStockClass = ((int) ($item['TOTAL_QUANTITY'] ?? 0) > 0) ? 'in-stock' : 'out-of-stock';
        $inStockText = ((int) ($item['TOTAL_QUANTITY'] ?? 0) > 0) ? (int) $item['TOTAL_QUANTITY'] . ' шт' : '0 шт';
        $manufacturer = $item['MANUFACTURER'] ?? 'Не указан';
        ?>
        <?php if ($index > 0): ?>
            <div class="<?= $dividerClass ?>"></div>
        <?php endif; ?>

        <div class="<?= $cardClass ?>" data-id="<?= (int) $item['ID'] ?>">
            <div class="analog-sction-card-picture">
                <?php if (!empty($item['IMAGE_SRC'])): ?>
                    <img src="<?= htmlspecialchars($item['IMAGE_SRC']) ?>"
                         alt="<?= htmlspecialchars($item['NAME']) ?>"
                         loading="lazy">
                <?php else: ?>
                    <div style="color: #999; font-size: 12px; text-align: center;">
                        <i class="fas fa-image" style="font-size: 40px; margin-bottom: 5px;"></i><br>
                        Нет фото
                    </div>
                <?php endif; ?>
            </div>

            <div class="product-info">
                <div class="product-header">
                    <div class="product-title">
                        <h4>
                            <a href="<?= htmlspecialchars($item['DETAIL_PAGE_URL']) ?>" style="color: inherit; text-decoration: none;">
                                <?= htmlspecialchars($item['NAME']) ?>
                            </a>
                        </h4>
                    </div>
                    <p class="<?= $analogTypeClass ?>"><?= htmlspecialchars($analogTypeLabel) ?></p>
                </div>
                <p class="analog-manufacturer">
                    <strong>Производитель:</strong>
                    <span><?= htmlspecialchars($manufacturer) ?></span>
                </p>
            </div>

            <div class="divider"></div>

            <div class="availability-section">
                <div class="analog-availability">
                    <p><strong>Наличие:</strong> <span class="<?= $inStockClass ?>"><?= htmlspecialchars($inStockText) ?></span></p>
                    <p><strong>Под заказ:</strong> <span class="balance-to-order">1 000 шт</span></p>
                </div>
            </div>

            <div class="divider-2"></div>

            <div class="price-section">
                <div class="analog-price">
                    <?php if (isset($item['PRICE'])): ?>
                        <span class="price">от <?= number_format((float) $item['PRICE'], 2, ',', ' ') ?> ₽</span>
                    <?php else: ?>
                        <span class="price">Цена по запросу</span>
                    <?php endif; ?>
                </div>
                <?php if (isset($item['PRICE'])): ?>
                            <a href="<?= htmlspecialchars($item['DETAIL_PAGE_URL']) ?>" class="btn btn-small">
                        Купить
                    </a>
                <?php else: ?>
                    <button class="btn btn-small request-price"
                            data-id="<?= (int) $item['ID'] ?>"
                            data-name="<?= htmlspecialchars($item['NAME']) ?>">
                        <i class="fas fa-envelope"></i>
                        Запросить цену
                    </button>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if ($hiddenCount > 0): ?>
        <div class="show-more-container">
            <button class="show-more-btn" id="showMoreBtn">
                <i class="fas fa-chevron-down"></i>
                <span>Показать еще аналоги</span>
                <span class="analog-count" id="analogCount">+<?= (int) $hiddenCount ?></span>
            </button>
        </div>
    <?php endif; ?>
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const showMoreBtn = document.getElementById('showMoreBtn');
    let isExpanded = false;

    if (showMoreBtn) {
        showMoreBtn.addEventListener('click', function() {
            const hiddenCards = document.querySelectorAll('.analog-section-card.hidden');
            const hiddenDividers = document.querySelectorAll('.analog-divider.hidden');

            if (!isExpanded) {
                hiddenCards.forEach(function(card) { card.classList.remove('hidden'); });
                hiddenDividers.forEach(function(divider) { divider.classList.remove('hidden'); });
                showMoreBtn.innerHTML = '<i class="fas fa-chevron-up"></i><span>Скрыть аналоги</span>';
                isExpanded = true;
            } else {
                const allCards = document.querySelectorAll('.analog-section-card');
                const allDividers = document.querySelectorAll('.analog-divider');
                const totalHidden = allCards.length - 2;

                for (let i = 2; i < allCards.length; i++) {
                    allCards[i].classList.add('hidden');
                }
                for (let i = 2; i < allDividers.length; i++) {
                    allDividers[i].classList.add('hidden');
                }

                showMoreBtn.innerHTML = '<i class="fas fa-chevron-down"></i><span>Показать еще аналоги</span><span class="analog-count">+' + totalHidden + '</span>';
                isExpanded = false;
            }
        });
    }

    document.querySelectorAll('.btn-small.request-price').forEach(function(button) {
        button.addEventListener('click', function() {
            const productName = this.getAttribute('data-name');
            alert('Запрос цены на товар: "' + productName + '"');
        });
    });
});
</script>
