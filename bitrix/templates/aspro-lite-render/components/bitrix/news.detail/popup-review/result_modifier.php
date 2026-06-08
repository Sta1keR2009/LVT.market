<?
TSolution::getFieldImageData($arResult, array('PREVIEW_PICTURE'));

if($arResult['PROPERTIES'])
{
	foreach($arResult['PROPERTIES'] as $key2 => $arProp)
	{
		if(($key2 == 'EMAIL' || $key2 == 'PHONE') && $arProp['VALUE'])
			$arResult['MIDDLE_PROPS'][] = $arProp;
		if(strpos($key2, 'SOCIAL') !== false && $arProp['VALUE'])
		{
			if($arResult['DISPLAY_PROPERTIES'][$key2])
				unset($arResult['DISPLAY_PROPERTIES'][$key2]);
			$arResult['SOCIAL_PROPS'][] = $arProp;
		}
	}
}
?>