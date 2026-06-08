<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
//$this->setFrameMode(true);
global $APPLICATION;

$arThemeValues = TSolution::GetFrontParametrsValues(SITE_ID);
$isBaseCustom = $arThemeValues['BASE_COLOR'] == 'CUSTOM';
if ($isBaseCustom) {
	$baseColor = (strpos($arThemeValues['BASE_COLOR_CUSTOM'], '#') === false ? '#' : '').$arThemeValues['BASE_COLOR_CUSTOM'];
} else {
	$arBaseColors = TSolution::$arParametrsList['MAIN']['OPTIONS']['BASE_COLOR']['LIST'];
	$baseColor = $arBaseColors[$arThemeValues['BASE_COLOR']]['COLOR'];
}

if (!$baseColor) {
	$baseColor = '#365edc';
}

$frame = $this->createFrame()->begin('');
$frame->setAnimation(true);
$arTransParams = array(
	'INIT_MAP_TYPE' => $arParams['INIT_MAP_TYPE'],
	'INIT_MAP_LON' => $arResult['POSITION']['google_lon'],
	'INIT_MAP_LAT' => $arResult['POSITION']['google_lat'],
	'INIT_MAP_SCALE' => $arResult['POSITION']['google_scale'],
	'MAP_WIDTH' => $arParams['MAP_WIDTH'],
	'MAP_HEIGHT' => $arParams['MAP_HEIGHT'],
	'CONTROLS' => $arParams['CONTROLS'],
	'OPTIONS' => $arParams['OPTIONS'],
	'MAP_ID' => $arParams['MAP_ID'],
	'API_KEY' => $arParams['API_KEY'],
);

if ($arParams['DEV_MODE'] == 'Y'){
	$arTransParams['DEV_MODE'] = 'Y';
	if ($arParams['WAIT_FOR_EVENT'])
		$arTransParams['WAIT_FOR_EVENT'] = $arParams['WAIT_FOR_EVENT'];
}
$arParams["CLICKABLE"] = ( $arParams["CLICKABLE"] ? $arParams["CLICKABLE"] : "Y" );?>
	<div class="module-map">
		<div class="map-wr module-contacts-map-layout">
			<?$APPLICATION->IncludeComponent('bitrix:map.google.system', '.default', $arTransParams, false, array('HIDE_ICONS' => 'Y'));?>
		</div>
	</div>
<?$APPLICATION->AddHeadScript( $this->__folder.'/markerclustererplus.min.js', true )?>
<?//$APPLICATION->AddHeadScript( $this->__folder.'/infobox.js', true )?>
<script>
	if (!window.BX_GMapAddPlacemark_){
		window.BX_GMapAddPlacemark_ = function(markers, bounds, arPlacemark, map_id, clickable){
			var map = GLOBAL_arMapObjects[map_id];
			if (null == map) {
				return false;
			}

			if (!arPlacemark.LAT || !arPlacemark.LON) {
				return false;
			}

			var pt = new google.maps.LatLng(arPlacemark.LAT, arPlacemark.LON);
			bounds.extend(pt);

			var template = ['<svg width="38" height="48" class="marker dynamic" viewBox="0 0 38 48" fill="none" xmlns="http://www.w3.org/2000/svg"><path class="fill-theme-svg" d="M31.7547 28.6615C33.7914 25.977 35 22.6296 35 19C35 10.1634 27.8366 3 19 3C10.1634 3 3 10.1634 3 19C3 27.8366 10.1634 35 19 35V43.061C19 44.0285 20.2369 44.4321 20.8075 43.6509L31.7555 28.6627L31.7547 28.6615Z" fill="{{ color }}"/><path d="M1.5 19C1.5 28.1597 8.5372 35.6758 17.5 36.4366V43.061C17.5 45.4796 20.5922 46.4887 22.0188 44.5357L32.9668 29.5474L33.0099 29.4884C33.0974 29.3717 33.1834 29.2539 33.2681 29.1349L33.5968 28.6849L33.5871 28.6709C35.4274 25.9001 36.5 22.5736 36.5 19C36.5 9.33502 28.665 1.5 19 1.5C9.33502 1.5 1.5 9.33502 1.5 19Z" stroke="white" stroke-opacity="0.5" stroke-width="3"/><circle cx="19" cy="19" r="10" fill="white"/></svg>'].join('');

			var markerSVG = template.replace('{{ color }}', '<?=$baseColor?>').replace('#', '%23');

			var icon = {
				anchor: new google.maps.Point(19, 47),
				url: 'data:image/svg+xml;utf-8, ' + markerSVG
			}
			
			var obPlacemark = new google.maps.Marker({
				'position': pt,
				'map': map,
				'icon': icon,
				'clickable': (clickable == "Y" ? true : false),
				// 'title': arPlacemark.TEXT,
				'zIndex': 5,
				'html': arPlacemark.TEXT
			});
			markers.push(obPlacemark);
			
			var infowindow = new google.maps.InfoWindow({
				content: arPlacemark.TEXT
			});
			
			obPlacemark.addListener("click", function(){
				if (null != window['__bx_google_infowin_opened_' + map_id]) {
					window['__bx_google_infowin_opened_' + map_id].close();
				}
				infowindow.open(map, obPlacemark);

				window['__bx_google_infowin_opened_' + map_id] = infowindow;
			});

			google.maps.event.addListener(obPlacemark, 'mouseover', function() {
				obPlacemark.set("opacity","0.9");
			});

			google.maps.event.addListener(obPlacemark, 'mouseout', function() {
				obPlacemark.set("opacity","1");
			});

			if (BX.type.isNotEmptyString(arPlacemark.TEXT)){
				obPlacemark.infowin = new google.maps.InfoWindow({
					content: "Loading..."
				});
				
			}

			return obPlacemark;
		}
	}

	if (null == window.BXWaitForMap_view){
		function BXWaitForMap_view(map_id){
			if (null == window.GLOBAL_arMapObjects)
				return;
		
			if (window.GLOBAL_arMapObjects[map_id])
				window['BX_SetPlacemarks_' + map_id]();
			else
				setTimeout('BXWaitForMap_view(\'' + map_id + '\')', 300);
		}
	}
</script>
<?if (is_array($arResult['POSITION']['PLACEMARKS']) && ($cnt = count($arResult['POSITION']['PLACEMARKS']))):?>
	<script type="text/javascript">
		function BX_SetPlacemarks_<?echo $arParams['MAP_ID']?>(){
			var markers = [];
			bounds = new google.maps.LatLngBounds();
			<?for($i = 0; $i < $cnt; $i++):?>
				BX_GMapAddPlacemark_(markers, bounds, <?echo CUtil::PhpToJsObject($arResult['POSITION']['PLACEMARKS'][$i])?>, '<?echo $arParams['MAP_ID']?>', '<?=$arParams["CLICKABLE"];?>');
			<?endfor;?>
			<?if( $arParams["ORDER"] != "Y" ){?>
				/*cluster icon*/
				map = window.GLOBAL_arMapObjects['<?echo $arParams['MAP_ID']?>'];

				var template = ['<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'56\' height=\'56\' viewBox=\'0 0 56 56\'>',
							"<defs><style>.cls-cluster, .cls-cluster3 {fill: %23fff;}.cls-cluster {opacity: 0.5;}</style></defs>",
							"<circle class='cls-cluster' cx='28' cy='28' r='28'/>",
							"<circle class='cls-cluster2' cx='28' cy='28' r='25' fill='{{ color }}'/>",
							"<circle class='cls-cluster3' cx='28' cy='28' r='18'/>",
						"</svg>"].join('');

				var markerSVG = template.replace('{{ color }}', '<?=$baseColor?>').replace('#', '%23');

				var clusterOptions = {
					zoomOnClick: true,
					averageCenter: true,
					clusterClass: 'cluster',
					styles: [{
						url: 'data:image/svg+xml;utf-8,' + markerSVG,
						height: 56, 
						width: 56,
						textColor: '#333',
						textSize: 13,
						lineHeight: '56px'
						// fontFamily: 'Ubuntu'
					}]
				}
				var markerCluster = new MarkerClusterer(map, markers, clusterOptions);
			
				center = bounds.getCenter();
				<?if ( $cnt > 1 ){?>
					map.fitBounds(bounds);
				<?} else {?>
					map.setCenter({lat: +<?=$arResult['POSITION']['PLACEMARKS'][0]['LAT'];?>, lng: +<?=$arResult['POSITION']['PLACEMARKS'][0]['LON'];?>});
					map.setZoom(17);
				<?}?>
			<?}?>
			
			/*reinit map*/
			//google.maps.event.trigger(map,'resize');
		}

		function BXShowMap_<?echo $arParams['MAP_ID']?>() {
			BXWaitForMap_view('<?echo $arParams['MAP_ID']?>');
		}

		BX.ready(BXShowMap_<?echo $arParams['MAP_ID']?>);

		$(document).ready(function(){
			let t = setInterval(
				function(){
					if (
						typeof window.GLOBAL_arMapObjects === 'object' &&
						window.GLOBAL_arMapObjects
					) {
						clearInterval(t);

						var map = window.GLOBAL_arMapObjects['<?echo $arParams['MAP_ID']?>'];
						$('.contacts-list__item .show_on_map > span,.stores-list__item.show_on_map').on('click', function(){
							var arCoordinates = $(this).data('coordinates').split(',');
							
							$('.contacts__tabs .tabs .nav-tabs li a[href="#map"]').trigger('click');

							if ($('.contacts__map').length) {
								scrollToBlock($('.contacts__map'));
							}
							
							map.setCenter( new google.maps.LatLng( arCoordinates[0], arCoordinates[1] ) );
							map.setZoom(<?=$arResult['POSITION']['google_scale']?>);
						});
					}
				}, 100
			);
        });
	</script>
<?endif;?>