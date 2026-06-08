<?php

/**
 * Список позиций Mouser на странице пустого поиска (ссылка открывает карточку через local/api/mouser_open_product.php).
 *
 * @param list<array<string, mixed>> $parts
 */
function lvt_mouser_search_results_html(array $parts, int $catalogIblockId): string
{
    if ($parts === []) {
        return '';
    }
    $catalogIblockId = max(1, $catalogIblockId);
    ob_start();
    ?>
    <div class="lvt-mouser-search alert alert-info">
        <h3 class="lvt-mouser-search__title">Результаты по каталогу Mouser</h3>
        <p class="lvt-mouser-search__note">Позиции не найдены в индексе сайта. Ниже — до <?= (int) count($parts) ?> совпадений по API Mouser. Открытие позиции создаёт или обновляет карточку в каталоге.</p>
        <div class="lvt-mouser-search__table-wrap">
            <table class="lvt-mouser-search__table">
                <thead>
                <tr>
                    <th>Mouser №</th>
                    <th>Партномер</th>
                    <th>Производитель</th>
                    <th>Описание</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($parts as $row) {
                    if (!is_array($row)) {
                        continue;
                    }
                    $mpn = trim((string) ($row['MouserPartNumber'] ?? ''));
                    if ($mpn === '') {
                        continue;
                    }
                    $manPn = htmlspecialchars(trim((string) ($row['ManufacturerPartNumber'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    $mfr = htmlspecialchars(trim((string) ($row['Manufacturer'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    $desc = htmlspecialchars(trim((string) ($row['Description'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    $href = '/local/api/mouser_open_product.php?mouser=' . rawurlencode($mpn) . '&catalog_iblock=' . $catalogIblockId;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($mpn, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td><?= $manPn ?></td>
                        <td><?= $mfr ?></td>
                        <td class="lvt-mouser-search__desc"><?= $desc ?></td>
                        <td><a class="btn btn-default btn-sm" href="<?= htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">Открыть</a></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php

    return (string) ob_get_clean();
}
