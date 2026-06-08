<?php
foreach ($arResult['PROPERTIES'] as $propertyCode => $property) {

	if ($propertyCode == 'SITE' && $arResult['DISPLAY_PROPERTIES'][$propertyCode] && $property['VALUE']) {
		$value = preg_replace('#(http|https)(://)|((\?.*)|(\/\?.*))#', '', $property['VALUE']);
		$arResult['CONTACT_PROPERTIES'][$propertyCode] = [
			'NAME' => $property['NAME'],
			'VALUE' => $value,
			'TYPE' => 'LINK',
			'HREF' => $property['VALUE'],
			'ATTR' => 'target="_blank"',
			'SORT' => 200,
		];

		continue;
	}
}

if ($arResult['CONTACT_PROPERTIES']) {
	usort($arResult['CONTACT_PROPERTIES'], function ($a, $b) {
		return ($a['SORT'] > $b['SORT']);
	});
}

$arResult['IMAGE'] = null;
$pictureField = 'PREVIEW_PICTURE';
TSolution::getFieldImageData($arResult, [$pictureField]);
$picture = $arResult[$pictureField];
$preview = CFile::ResizeImageGet($picture['ID'], ['width' => 150, 'height' => 90], BX_RESIZE_IMAGE_PROPORTIONAL_ALT, true);
if ($picture) {
	$arResult['IMAGE'] = [
		'DETAIL_SRC' => $picture['SRC'],
		'PREVIEW_SRC' => $preview['src'],
		'TITLE' => (strlen($picture['DESCRIPTION']) ? $picture['DESCRIPTION'] : (strlen($picture['TITLE']) ? $picture['TITLE'] : $arResult['NAME'])),
		'ALT' => (strlen($picture['DESCRIPTION']) ? $picture['DESCRIPTION'] : (strlen($picture['ALT']) ? $picture['ALT'] : $arResult['NAME'])),
	];
}
//top gallery
if (
	isset($arResult['PROPERTIES']['PHOTOPOS']) && 
	$arResult['PROPERTIES']['PHOTOPOS']['VALUE_XML_ID'] == 'TOP_SIDE'
) {
	$arResult['TOP_GALLERY'] = [];
	if ($arResult['FIELDS']['DETAIL_PICTURE']) {
		$atrTitle = (strlen($arResult['DETAIL_PICTURE']['DESCRIPTION']) ? $arResult['DETAIL_PICTURE']['DESCRIPTION'] : (strlen($arResult['DETAIL_PICTURE']['TITLE']) ? $arResult['DETAIL_PICTURE']['TITLE'] : $arResult['NAME']));
		$atrAlt = (strlen($arResult['DETAIL_PICTURE']['DESCRIPTION']) ? $arResult['DETAIL_PICTURE']['DESCRIPTION'] : (strlen($arResult['DETAIL_PICTURE']['ALT']) ? $arResult['DETAIL_PICTURE']['ALT'] : $arResult['NAME']));

		$arResult['TOP_GALLERY'][] = array(
			'DETAIL' => $arResult['DETAIL_PICTURE'],
			'PREVIEW' => CFile::ResizeImageGet($arResult['DETAIL_PICTURE']['ID'], array('width' => 1500, 'height' => 1500), BX_RESIZE_PROPORTIONAL_ALT, true),
			'THUMB' => CFile::ResizeImageGet($arResult['DETAIL_PICTURE']['ID'] , array('width' => 60, 'height' => 60), BX_RESIZE_IMAGE_EXACT, true),
			'TITLE' => $atrTitle,
			'ALT' => $atrAlt,
		);
	}
	if(
		$arParams['TOP_GALLERY_PROP_CODE'] && 
		isset($arResult['PROPERTIES'][$arParams['TOP_GALLERY_PROP_CODE']]) && 
		$arResult['PROPERTIES'][$arParams['TOP_GALLERY_PROP_CODE']]['VALUE']
	){
		foreach($arResult['PROPERTIES'][$arParams['TOP_GALLERY_PROP_CODE']]['VALUE'] as $img){
			$arPhoto = CFile::GetFileArray($img);
	
			$alt = $arPhoto['DESCRIPTION'] ?: $arPhoto['ALT'] ?: $arResult['NAME'];
			$title = $arPhoto['DESCRIPTION'] ?: $arPhoto['TITLE'] ?: $arResult['NAME'];;
	
			$arResult['TOP_GALLERY'][] = array(
				'DETAIL' => $arPhoto,
				'PREVIEW' => CFile::ResizeImageGet($img, array('width' => 1500, 'height' => 1500), BX_RESIZE_PROPORTIONAL_ALT, true),
				'THUMB' => CFile::ResizeImageGet($img , array('width' => 60, 'height' => 60), BX_RESIZE_IMAGE_EXACT, true),
				'TITLE' => $title,
				'ALT' => $alt,
			);
		}
	}
}