<?
//check ajax-request
use \Bitrix\Main\Web\Json;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

// БЕЗОПАСНОСТЬ: Убрана небезопасная десериализация
// Вместо base64_decode + unserialize используем прямой доступ к $_POST
// Если нужна передача данных через query, используйте JSON

// Простая проверка на наличие данных
if(empty($_POST))
    LocalRedirect("/");

// БЕЗОПАСНОСТЬ: Rate limiting для авторизации
function checkRateLimit($ip, $action = 'login') {
    $rateLimitFile = $_SERVER['DOCUMENT_ROOT'] . '/api/logs/rate_limit_' . md5($ip . $action) . '.json';
    $maxAttempts = 5;
    $timeWindow = 300; // 5 минут
    
    $attempts = [];
    if (file_exists($rateLimitFile)) {
        $attempts = json_decode(file_get_contents($rateLimitFile), true) ?: [];
    }
    
    // Удаляем старые попытки
    $currentTime = time();
    $attempts = array_filter($attempts, function($timestamp) use ($currentTime, $timeWindow) {
        return ($currentTime - $timestamp) < $timeWindow;
    });
    
    // Проверяем лимит
    if (count($attempts) >= $maxAttempts) {
        return false;
    }
    
    // Добавляем текущую попытку
    $attempts[] = $currentTime;
    file_put_contents($rateLimitFile, json_encode($attempts));
    
    return true;
}

if(!empty($_POST) && isset($_POST['login']) && isset($_POST['password']))
{
    // БЕЗОПАСНОСТЬ: Rate limiting
    $clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    if (!checkRateLimit($clientIP, 'login')) {
        http_response_code(429);
        exit(JSON::encode(array('error' => true, 'error_message' => 'Слишком много попыток входа. Попробуйте позже.')));
    }
    
    global $USER;
    if (!is_object($USER)) $USER = new CUser;
    $arAuthResult = $USER->Login($_POST['login'], $_POST['password'], "Y");
    if($arAuthResult !== true)
    {
        exit(JSON::encode(array('error' => true, 'error_message' => $arAuthResult['MESSAGE'])));
    }
    
    // БЕЗОПАСНОСТЬ: Проверка прав администратора для всех операций
    if (!$USER->IsAdmin()) {
        exit(JSON::encode(array('error' => true, 'error_message' => 'Доступ запрещен. Требуются права администратора.')));
    }
}
else
{
    exit(JSON::encode(array('error' => true, 'error_message' => 'bad request')));
}

if(isset($_POST['get']) && $_POST['get'] == 'iblocks')
{
    // БЕЗОПАСНОСТЬ: Проверка авторизации уже выполнена выше
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
    $result = \Bitrix\Iblock\IblockTable::getList( array(
        'select' => array(
            'ID',
            'NAME'
        ),
        'filter' => array()
    ) );
    
    $Iblocks = array();
    
    while ( $Iblock = $result->fetch() )
    {
        $Iblocks['REFERENCE_ID'][] = $Iblock['ID'];
        $Iblocks['REFERENCE'][] = '[' . $Iblock['ID'] . '] ' . $Iblock['NAME'];
    }
    $result = Json::encode(array('iblocks' => $Iblocks));
    exit($result);
}

if(isset($_POST['get']) && $_POST['get'] == 'data' && isset($_POST['iblock']) && !empty($_POST['iblock']))
{
    // БЕЗОПАСНОСТЬ: Проверка авторизации уже выполнена выше
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
    $sections = array();
    $res = \Bitrix\Iblock\SectionTable::getList( array(
        'select' => array(
            'ID',
            'NAME',
        ),
        'filter' => array('IBLOCK_ID' => intval($_POST['iblock']))
    ) );
    while ( $section = $res->fetch() )
    {
        $sections['REFERENCE_ID'][] = $section['ID'];
        $sections['REFERENCE'][] = $section['NAME'];
    }
    
    $res = \CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>intval($_POST['iblock']), '!CODE' => false, '!CODE' => ''), false, false, array('ID', 'NAME', 'CODE'));
    $properties = array();
    
    while($arProperty = $res -> fetch())
    {
        $properties['REFERENCE_ID'][] = $arProperty['CODE'];
        $properties['REFERENCE'][] = '['.$arProperty['CODE'].'] '.$arProperty['NAME'];
    }
    
    $res = \Bitrix\Catalog\GroupTable ::getList(array('select' => array('ID', 'NAME')));
    $prices = array();
    
    while($arPrice = $res->fetch())
    {
        $prices['REFERENCE_ID'][] = $arPrice['ID'];
        $prices['REFERENCE'][] = '['.$arPrice['ID'].'] '.$arPrice['NAME'];
    }
    
    $result = \Bitrix\Iblock\IblockTable::getList( array(
        'select' => array(
            'ID',
            'NAME'
        ),
        'filter' => array()
    ) );
    
    $Iblocks = array();
    
    while ( $Iblock = $result->fetch() )
    {
        $Iblocks['REFERENCE_ID'][] = $Iblock['ID'];
        $Iblocks['REFERENCE'][] = '[' . $Iblock['ID'] . '] ' . $Iblock['NAME'];
    }
    
    exit(JSON::encode(array('sections' => $sections, 'properties' => $properties, 'prices' => $prices, 'iblocks' => $Iblocks)));
}

if(isset($_POST['action']) && $_POST['action'] == 'export' && !empty($_POST['parser']))
{
    $parser = $_POST['parser'];
    $createList = $_POST['createlist'];
    $ignoreList = array(
        'PRICE' => array(),
        'SECTION' => array(),
        'PROPERTY' => array()
    );
    
    $changeSettings = array(
        'PRICE' => array(),
        'SECTION' => array(),
        'PROPERTY' => array()
    );
    
   
    // БЕЗОПАСНОСТЬ: Авторизация уже проверена выше, но проверяем еще раз для безопасности
    global $USER, $APPLICATION;
    if (!is_object($USER)) $USER = new CUser;
    
    if (!$USER->IsAuthorized() || !$USER->IsAdmin()) {
        exit(JSON::encode(array('error' => true, 'error_message' => 'Требуется авторизация администратора.')));
    }
    
    $APPLICATION->arAuthResult = array('SUCCESS' => true);
    
    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/shs.parser/lib/helper/export.php");
    $export = new \Bitrix\Shs\Helper\Export();
    $errors = array();
    if($createList['PRICE'])
    {
        foreach($createList['PRICE'] as $id => $arPrice)
        {
            if(isset($arPrice['ID']))
                unset($arPrice['ID']);
            
            $res = \Bitrix\Catalog\GroupTable::add($arPrice);
            
            if(!$res->isSuccess())
            {
                $errors['PRICE'] = array_merge($errors['PRICE'], $res->getErrorMessages());
                $ignoreList['PRICE'][$id] = $arPrice;
                continue;
            }
            
            $changeSettings['PRICE'][$id] = $res->getId();
        }
    }
    
    if($createList['SECTION'])
    {
        foreach($createList['SECTION'] as $id => $arSection)
        {
            if(isset($arSection['ID']))
                unset($arSection['ID']);
            
            if(!isset($arSection['CODE']) || empty($arSection['CODE']))
                $arSection['CODE'] = Cutil::translit($arSection['NAME'],"ru",array("replace_space"=>"-","replace_other"=>"-"));
            
            $bs = new CIBlockSection;
            $ID = $bs->Add($arSection);
            
            if(!$ID)
            {
                $errors['SECTION'][] = $bs->LAST_ERROR;
                $ignoreList['SECTION'][$id] = $arSection;
                continue;
            }
            
            $changeSettings['SECTION'][$id] = $ID;
        }
    }
    
    if($createList['PROPERTY'])
    {
        foreach($createList['PROPERTY'] as $id => $arProperty)
        {
            if(isset($arProperty['ID']))
                unset($arProperty['ID']);
            
            if(!isset($arProperty['CODE']) || empty($arProperty['CODE']))
                $arProperty['CODE'] = Cutil::translit($arProperty['NAME'],"ru",array("replace_space"=>"-","replace_other"=>"-"));
            
            $ibp = new CIBlockProperty;
            $ID = $ibp->Add($arProperty);
            
            if(!$ID)
            {
                $errors['PROPERTY'][] = $ibp->LAST_ERROR;
                $ignoreList['PROPERTY'][$id] = $arSection;
                continue;
            }
            
            $changeSettings['PROPERTY'][$id] = $ID;
        }
    }
    
    $parser = $export->correctByIgnoreList($parser, $ignoreList);
    $parser = $export->changeSettings($parser, $changeSettings);
    
    CModule::includeModule('shs.parser');
    
    $obParser = new ShsParserContent();
    RssContentParser::sotbitParserSetSettings($parser['SETTINGS']);
    
    if(isset($parser['ID']))
        unset($parser['ID']);
    
    $parser['SETTINGS'] = base64_encode(serialize($parser['SETTINGS']));
    
    $ID = $obParser->Add($parser);
    
    if(!empty($errors))
    {
        $errors['PRICE'] = implode('<br>', $errors['PRICE']);
        $errors['SECTION'] = implode('<br>', $errors['SECTION']);
        $errors['PROPERTY'] = implode('<br>', $errors['PROPERTY']);
    }
    
    exit(JSON::encode(array('error' => !$ID || !empty($errors), 'error_message' => implode('<br>', $errors), 'ID' => $ID)));
}

exit(JSON::encode(array('error' => true, 'error_description' => 'did nothing')));
?>