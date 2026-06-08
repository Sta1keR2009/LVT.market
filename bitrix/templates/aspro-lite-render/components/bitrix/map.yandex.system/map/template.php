<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
?>
<?$this->setFrameMode(true);?>
<script type="text/javascript">
if (!window.GLOBAL_arMapObjects)
	window.GLOBAL_arMapObjects = {};

var map;
var animateFunctionexists = false;
var markerSVG = '<svg xmlns="http://www.w3.org/2000/svg" width="44" height="55" viewBox="0 0 44 55">'
				 +'<defs><style>.mcls-1,.mcls-3{fill:#fff;}.mcls-1,.mcls-2{fill-rule:evenodd;}.mcls-2{opacity: 0.75;fill:#fff;}.mcls-1{fill: #ff6307;}</style></defs>'
				  +'<path class="mcls-2" d="M44,23.051c0,15.949-22,31.938-22,31.938S0.009,39,.009,23.051c0-.147.019-0.29,0.022-0.436C0.025,22.409,0,22.208,0,22A22,22,0,0,1,19.627.132,19.174,19.174,0,0,1,24.5.152,22,22,0,0,1,44,22c0,0.172-.022.338-0.026,0.509S44,22.869,44,23.051Z"/><path id="Shape-2" data-name="Shape" class="mcls-1" d="M42,23.393c0,13.424-20,29.6-20,29.6s-20-16.174-20-29.6c0-.209.024-0.414,0.03-0.623C2.029,22.513,2,22.26,2,22a20,20,0,0,1,40,0c0,0.227-.026.448-0.034,0.673C41.974,22.914,42,23.151,42,23.393Z"/><circle id="Shape-3" data-name="Shape" class="mcls-3" cx="22" cy="22" r="11"/>'
				+'</svg>';

function init_<?echo $arParams['MAP_ID']?>()
{
	if (!window.ymaps)
		return;

	/*if(typeof window.GLOBAL_arMapObjects['<?echo $arParams['MAP_ID']?>'] !== "undefined")
		return;*/

	var node = BX("BX_YMAP_<?echo $arParams['MAP_ID']?>");
	node.innerHTML = '';

	map = window.GLOBAL_arMapObjects['<?echo $arParams['MAP_ID']?>'] = new ymaps.Map(node, {
		center: [<?echo $arParams['INIT_MAP_LAT']?>, <?echo $arParams['INIT_MAP_LON']?>],
		zoom: <?echo $arParams['INIT_MAP_SCALE']?>,
		type: 'yandex#<?=$arResult['ALL_MAP_TYPES'][$arParams['INIT_MAP_TYPE']]?>',
		// adjustZoomOnTypeChange: true
	});
	
	/*map.geoObjects.events.add('balloonclose', function (e){
		setTimeout(function(){
			$('.ymaps-image-with-content').each(function(){
				if(!$(this).find('.marker').length){
					$(this).prepend('<div class="marker">'+markerSVG+'</div>');
				}
			});
		}, 20);
	});

	
	map.events.add('boundschange', function (e) {
		//$('.ymaps-image-with-content .marker').remove();
		setTimeout(function(){
			$('.ymaps-image-with-content').each(function(){
				if(!$(this).find('.marker').length){
					$(this).prepend('<div class="marker">'+markerSVG+'</div>');
				}
			});
		}, 300);
	});*/

<?
foreach ($arResult['ALL_MAP_OPTIONS'] as $option => $method)
{
	if (in_array($option, $arParams['OPTIONS'])):
?>
	map.behaviors.enable("<?echo $method?>");
<?
	else:
?>
	if (map.behaviors.isEnabled("<?echo $method?>"))
		map.behaviors.disable("<?echo $method?>");
<?
	endif;
}

foreach ($arResult['ALL_MAP_CONTROLS'] as $control => $method)
{
	if (in_array($control, $arParams['CONTROLS'])):
?>
	map.controls.add('<?=$method?>');
<?
	endif;
}


if ($arParams['DEV_MODE'] == 'Y'):
?>
	window.bYandexMapScriptsLoaded = true;
<?
endif;

if ($arParams['ONMAPREADY']):
?>
	if (window.<?echo $arParams['ONMAPREADY']?>)
	{
<?
	if ($arParams['ONMAPREADY_PROPERTY']):
?>
		<?echo $arParams['ONMAPREADY_PROPERTY']?> = map;
		window.<?echo $arParams['ONMAPREADY']?>();
<?
	else:
?>
		window.<?echo $arParams['ONMAPREADY']?>(map);
<?
	endif;
?>
	}
<?
endif;
?>
}
<?
if ($arParams['DEV_MODE'] == 'Y'):
?>
function BXMapLoader_<?echo $arParams['MAP_ID']?>()
{
	if (null == window.bYandexMapScriptsLoaded)
	{
		function _wait_for_map(){
			if (window.ymaps && window.ymaps.Map)
				init_<?echo $arParams['MAP_ID']?>();
			else
				setTimeout(_wait_for_map, 50);
		}

		BX.loadScript('<?=$arResult['MAPS_SCRIPT_URL']?>', _wait_for_map);
	}
	else
	{
		init_<?echo $arParams['MAP_ID']?>();
	}
}
<?
	if ($arParams['WAIT_FOR_EVENT']):
?>
	<?=CUtil::JSEscape($arParams['WAIT_FOR_EVENT'])?> = BXMapLoader_<?=$arParams['MAP_ID']?>;
<?
	elseif ($arParams['WAIT_FOR_CUSTOM_EVENT']):
?>
	BX.addCustomEvent('<?=CUtil::JSEscape($arParams['WAIT_FOR_EVENT'])?>', BXMapLoader_<?=$arParams['MAP_ID']?>);
<?
	else:
?>
	BX.ready(BXMapLoader_<?echo $arParams['MAP_ID']?>);
<?
	endif;
else: // $arParams['DEV_MODE'] == 'Y'
?>

(function bx_ymaps_waiter(){
	setTimeout(function(){
		/*if(!('.ymaps-default-cluster').length)
		{*/
			/*$('.ymaps-image-with-content').each(function(){
				if(!$(this).find('.marker').length){
					$(this).prepend('<div class="marker">'+markerSVG+'</div>');
				}
			});*/
		//}
	}, 1500);
	if(typeof ymaps !== 'undefined')
		ymaps.ready(init_<?echo $arParams['MAP_ID']?>);
	else
		setTimeout(bx_ymaps_waiter, 100);
})();

<?
endif; // $arParams['DEV_MODE'] == 'Y'
?>

/* if map inits in hidden block (display:none)
*  after the block showed
*  for properly showing map this function must be called
*/
function BXMapYandexAfterShow(mapId)
{
	if(window.GLOBAL_arMapObjects[mapId] !== undefined)
		window.GLOBAL_arMapObjects[mapId].container.fitToViewport();
}

</script>
<div id="BX_YMAP_<?echo $arParams['MAP_ID']?>" class="bx-yandex-map" style="height: <?echo $arParams['MAP_HEIGHT'];?>; width: <?echo $arParams['MAP_WIDTH']?>;"><?echo GetMessage('MYS_LOADING'.($arParams['WAIT_FOR_EVENT'] ? '_WAIT' : ''));?></div>