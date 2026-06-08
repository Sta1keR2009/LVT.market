<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$lvtSpecificationHtml = trim((string)($templateData['LVT_SPECIFICATION_HTML'] ?? ''));
if ($lvtSpecificationHtml === '') {
    return;
}
?>
<div class="detail-block ordered-block lvt-detail-specification-epilog">
    <?= $lvtSpecificationHtml ?>
</div>
