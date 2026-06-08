<?php
foreach ($arResult['ITEMS'] as &$arItem) {
	if ($arItem['PROPERTIES']) {
		$arItem['CONTACT_PROPERTIES'] = [];

		foreach ($arItem['PROPERTIES'] as $propertyCode => $property) {
			if ($propertyCode == 'PHONE' && $arItem['DISPLAY_PROPERTIES'][$propertyCode] && $property['VALUE']) {
				$tel = $property['VALUE'] ? preg_replace('/[^\d]/', '', $property['VALUE']) : '';
				$arItem['CONTACT_PROPERTIES'][$propertyCode] = [
					'NAME' => $property['NAME'],
					'VALUE' => $property['VALUE'],
					'TYPE' => 'LINK',
					'HREF' => 'tel:+' . $tel,
					'ATTR' => '',
					'SORT' => 100,
				];

				continue;
			}

			if ($propertyCode == 'SITE' && $arItem['DISPLAY_PROPERTIES'][$propertyCode] && $property['VALUE']) {
				$value = preg_replace('#(http|https)(://)|((\?.*)|(\/\?.*))#', '', $property['VALUE']);
				$arItem['CONTACT_PROPERTIES'][$propertyCode] = [
					'NAME' => $property['NAME'],
					'VALUE' => $value,
					'TYPE' => 'LINK',
					'HREF' =>  $property['VALUE'],
					'ATTR' => 'target="_blank"',
					'SORT' => 200,
				];

				continue;
			}
		}

		if($arItem['CONTACT_PROPERTIES']) {
			usort($arItem['CONTACT_PROPERTIES'], function($a, $b) {
				return ($a['SORT'] > $b['SORT']);
			});
		}
	}
}
?>
