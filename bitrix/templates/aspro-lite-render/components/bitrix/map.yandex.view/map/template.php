<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$this->setFrameMode(true);

if ($arParams['BX_EDITOR_RENDER_MODE'] == 'Y'):
?>
<img src="/bitrix/components/bitrix/map.yandex.view/templates/.default/images/screenshot.png" border="0" />
<?
else:

	$arTransParams = array(
		'KEY' => $arParams['KEY'],
		'INIT_MAP_TYPE' => $arParams['INIT_MAP_TYPE'],
		'INIT_MAP_LON' => $arResult['POSITION']['yandex_lon'],
		'INIT_MAP_LAT' => $arResult['POSITION']['yandex_lat'],
		'INIT_MAP_SCALE' => $arResult['POSITION']['yandex_scale'],
		'MAP_WIDTH' => $arParams['MAP_WIDTH'],
		'MAP_HEIGHT' => $arParams['MAP_HEIGHT'],
		'CONTROLS' => $arParams['CONTROLS'],
		'OPTIONS' => $arParams['OPTIONS'],
		'MAP_ID' => $arParams['MAP_ID'],
		'LOCALE' => $arParams['LOCALE'],
		'ONMAPREADY' => 'BX_SetPlacemarks_'.$arParams['MAP_ID'],
	);

	if ($arParams['DEV_MODE'] == 'Y')
	{
		$arTransParams['DEV_MODE'] = 'Y';
		if ($arParams['WAIT_FOR_EVENT'])
			$arTransParams['WAIT_FOR_EVENT'] = $arParams['WAIT_FOR_EVENT'];
	}
?>
<script type="text/javascript">
var geo_result;
var clusterer;

var markerSVG = '<svg width="38" height="48" class="marker dynamic" viewBox="0 0 38 48" fill="none" xmlns="http://www.w3.org/2000/svg"><path class="fill-theme-svg" d="M31.7547 28.6615C33.7914 25.977 35 22.6296 35 19C35 10.1634 27.8366 3 19 3C10.1634 3 3 10.1634 3 19C3 27.8366 10.1634 35 19 35V43.061C19 44.0285 20.2369 44.4321 20.8075 43.6509L31.7555 28.6627L31.7547 28.6615Z" fill="#3761E9"/><path d="M1.5 19C1.5 28.1597 8.5372 35.6758 17.5 36.4366V43.061C17.5 45.4796 20.5922 46.4887 22.0188 44.5357L32.9668 29.5474L33.0099 29.4884C33.0974 29.3717 33.1834 29.2539 33.2681 29.1349L33.5968 28.6849L33.5871 28.6709C35.4274 25.9001 36.5 22.5736 36.5 19C36.5 9.33502 28.665 1.5 19 1.5C9.33502 1.5 1.5 9.33502 1.5 19Z" stroke="white" stroke-opacity="0.5" stroke-width="3"/><circle cx="19" cy="19" r="10" fill="white"/></svg>';

var clusterSVG = '<div class="cluster_custom"><span>$[properties.geoObjects.length]</span><svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 56 56"><defs><style>.cls-cluster, .cls-cluster3 {fill: #fff;}.cls-cluster {opacity: 0.5;}</style></defs><circle class="cls-cluster" cx="28" cy="28" r="28"/><circle data-name="Ellipse 275 copy 2" class="cls-cluster2 fill-theme-svg" cx="28" cy="28" r="25"/><circle data-name="Ellipse 276 copy" class="cls-cluster3" cx="28" cy="28" r="18"/></svg></div>';
				
function BX_SetPlacemarks_<?echo $arParams['MAP_ID']?>(map)
{
	if(typeof window["BX_YMapAddPlacemark"] != 'function')
	{
		/* If component's result was cached as html,
		 * script.js will not been loaded next time.
		 * let's do it manualy.
		*/

		(function(d, s, id)
		{
			var js, bx_ym = d.getElementsByTagName(s)[0];
			if (d.getElementById(id)) return;
			js = d.createElement(s); js.id = id;
			js.src = "<?=$templateFolder.'/script.js'?>";
			bx_ym.parentNode.insertBefore(js, bx_ym);
		}(document, 'script', 'bx-ya-map-js'));

		var ymWaitIntervalId = setInterval( function(){
				if(typeof window["BX_YMapAddPlacemark"] == 'function')
				{
					BX_SetPlacemarks_<?echo $arParams['MAP_ID']?>(map);
					clearInterval(ymWaitIntervalId);
				}
			}, 300
		);

		return;
	}
	var arObjects = {PLACEMARKS:[],POLYLINES:[]};
	// clusterer = new ymaps.Clusterer();

<?
if( is_array($arResult['POSITION']['PLACEMARKS']) && ($cnt = count($arResult['POSITION']['PLACEMARKS'])) ){
	for( $i = 0; $i < $cnt; $i++ ){
?>
arObjects.PLACEMARKS[arObjects.PLACEMARKS.length] = BX_YMapAddPlacemark(map, <?echo CUtil::PhpToJsObject($arResult['POSITION']['PLACEMARKS'][$i])?><?if(count($arResult['POSITION']['PLACEMARKS'])>1):?>, true<?endif;?>);<?	
	}
}
?>

<?
	if (is_array($arResult['POSITION']['POLYLINES']) && ($cnt = count($arResult['POSITION']['POLYLINES']))):
		for($i = 0; $i < $cnt; $i++):
?>
	arObjects.POLYLINES[arObjects.POLYLINES.length] = BX_YMapAddPolyline(map, <?echo CUtil::PhpToJsObject($arResult['POSITION']['POLYLINES'][$i])?>);
<?
		endfor;
	endif;	

	if ($arParams['ONMAPREADY']):
?>
	if (window.<?echo $arParams['ONMAPREADY']?>)
	{
		window.<?echo $arParams['ONMAPREADY']?>(map, arObjects);
	}
<?
	endif;
?>
	/* set dynamic zoom for ballons */
	// map.setBounds(map.geoObjects.getBounds(), {checkZoomRange: true});
	   
	<?if(count((array)$arResult['POSITION']['PLACEMARKS'])>1):?>
		 var clusterIcons = [{
			size: [56, 56],
			offset: [-28, -28]
		}];

		clusterer = new ymaps.Clusterer({
			clusterIcons: clusterIcons,
			clusterIconContentLayout: ymaps.templateLayoutFactory.createClass(clusterSVG),
		});

		clusterer.add(arObjects.PLACEMARKS);

	    map.geoObjects.add(clusterer);

		map.setBounds(clusterer.getBounds(), {
			zoomMargin: 40,
			// checkZoomRange: true
		});

		if(arLiteOptions['THEME']['DEFAULT_MAP_MARKET'] != 'Y'){
			clusterer.events.add('click', function (e) {
				setTimeout(function(){
					$('.ymaps-image-with-content').each(function(){
						if(!$(this).find('.marker').length){
							$(this).prepend('<div class="marker">'+markerSVG+'</div>');
						}
					});
				}, 1000);
			});	
		}
	<?endif;?>	
}

$(document).ready(function(){
	$('.contacts-list__item .show_on_map > span,.stores-list__item.show_on_map').on('click', function(){
		var arCoordinates = $(this).data('coordinates').split(',');

		$('.contacts__tabs .tabs .nav-tabs li a[href="#map"]').trigger('click');

		if ($('.contacts__map').length) {
			scrollToBlock($('.contacts__map'));
		}

		map.setCenter([arCoordinates[0], arCoordinates[1]], '<?=$arResult['POSITION']['yandex_scale']?>');

		if(arLiteOptions['THEME']['DEFAULT_MAP_MARKET'] != 'Y'){
			setTimeout(function(){
				$('.ymaps-image-with-content').each(function(){
					if(!$(this).find('.marker').length){
						$(this).prepend('<div class="marker">'+markerSVG+'</div>');
					}
				});
			}, 800);
		}
	});
});
</script>
<div class="bx-map-view-layout bx-yandex-view-layout swipeignore">
	<div class="bx-yandex-view-map"><?$APPLICATION->IncludeComponent('bitrix:map.yandex.system', 'map', $arTransParams, false, array('HIDE_ICONS' => 'Y'));?></div>
	<div class="map-mobile-opener"></div>
</div>
<?endif;?>