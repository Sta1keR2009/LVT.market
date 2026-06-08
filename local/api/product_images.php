<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

$ids = isset($_GET["ids"]) ? explode(",", $_GET["ids"]) : [];
$ids = array_filter(array_map("intval", $ids));
if (empty($ids) || count($ids) > 20) {
    echo json_encode(["ok" => false, "error" => "Provide 1-20 ids"]);
    exit;
}

$_SERVER["DOCUMENT_ROOT"] = "/var/www/www-root/data/www/lvtgroup.ru";
define("NO_KEEP_STATISTIC", true);
require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";

$images = [];
$idList = implode(",", $ids);

$db = \Bitrix\Main\Application::getConnection();

// Get products with CODE, SECTION_ID, and IMAGE
$rows = $db->query("
    SELECT e.ID, e.CODE, e.IBLOCK_SECTION_ID, e.PREVIEW_PICTURE, e.DETAIL_PICTURE,
           f.SUBDIR, f.FILE_NAME, f.WIDTH, f.HEIGHT
    FROM b_iblock_element e
    LEFT JOIN b_file f ON COALESCE(e.PREVIEW_PICTURE, e.DETAIL_PICTURE) = f.ID
    WHERE e.ID IN ($idList)
      AND e.ACTIVE = \"Y\"
")->fetchAll();

// Build section path cache
$sectionPaths = [];
foreach ($rows as $row) {
    $sid = (int)$row["IBLOCK_SECTION_ID"];
    if ($sid && !isset($sectionPaths[$sid])) {
        $sectionPaths[$sid] = "";
        $nav = CIBlockSection::GetNavChain(11, $sid, ["CODE"]);
        $parts = [];
        while ($s = $nav->Fetch()) $parts[] = $s["CODE"];
        $sectionPaths[$sid] = implode("/", $parts);
    }
}

foreach ($rows as $row) {
    $pid = (int)$row["ID"];
    $code = $row["CODE"] ?: $pid;
    $sid = (int)$row["IBLOCK_SECTION_ID"];
    $sectionPath = $sectionPaths[$sid] ?? "";
    
    $url = "/catalog/";
    if ($sectionPath) $url .= $sectionPath . "/";
    $url .= $code . "/";
    
    $img = null;
    if ($row["FILE_NAME"]) {
        $img = [
            "url" => "/upload/" . $row["SUBDIR"] . "/" . $row["FILE_NAME"],
            "w" => (int)$row["WIDTH"],
            "h" => (int)$row["HEIGHT"],
        ];
    }
    
    $images[$pid] = [
        "url" => $url,
        "code" => $code,
        "image" => $img,
    ];
}

echo json_encode(["ok" => true, "images" => $images], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
