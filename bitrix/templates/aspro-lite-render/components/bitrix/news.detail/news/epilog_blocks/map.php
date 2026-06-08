<?
use \Bitrix\Main\Localization\Loc;
?>
<?//show map block?>
<?if($templateData['MAP']):?>
    <div class="detail-block ordered-block map contacts-page-map-top">
        <h3 class="switcher-title"><?=$arParams["T_MAP"]?></h3>
        <div class="bordered outer-rounded-x map-view">
            <?
            $arShop = [];
            $mapLAT = $mapLON = 0;
            $gps_S = $gps_N = 0;

            if ($arStoreMap = explode(',', $templateData['MAP'])) {
                $gps_S = $arStoreMap[0];
                $gps_N = $arStoreMap[1];
                
                if ($gps_S && $gps_N) {
					$mapLAT += $gps_S;
                    $mapLON += $gps_N;

                    $html = '<div class="pane_info_wrapper"><div class="pane_info clearfix">';
                    if ($templateData['PREVIEW_PICTURE']) {
                        $html .= '<div class="image"><span style="background: url('.CFile::GetPath($templateData['PREVIEW_PICTURE']['ID']).') center/cover no-repeat;" data-src></span></div>';
                    }
                    $html .= '<div class="body-info'.(!$templateData['PREVIEW_PICTURE'] ? ' wti' : '').'">';

                    if (is_array($arResult['IBLOCK_SECTION_ID']) || is_array($arResult['DETAIL_PAGE_URL'])) {
                        if (is_array($arResult['IBLOCK_SECTION_ID'])) {
                            $firstKey = array_keys($arResult['IBLOCK_SECTION_ID'])[0];
                            if(isset($arSections[$arResult['IBLOCK_SECTION_ID'][$firstKey]]) && $arSections[$arResult['IBLOCK_SECTION_ID'][$firstKey]]['NAME']){
                                $html .= '<div class="section_name font_13 color_999">'.$arSections[$arResult['IBLOCK_SECTION_ID'][$firstKey]]['NAME'].'</div>';
                            }
                        }
                        if (is_array($arResult['DETAIL_PAGE_URL'])) {
                            $firstKey = array_keys($arResult['DETAIL_PAGE_URL'])[0];
                            $html .= '<div class="title font_14 font_bold">'.'<div class="name color_222">'.$arResult['NAME'].'</div></div>';
                            if (isset($templateData['MAP_DOP_INFO']) && $templateData['MAP_DOP_INFO']['TEXT']) {
                                $html .= '<div class="info font_14 color_999">'.$templateData['MAP_DOP_INFO']['TEXT'].'</div>';
                            }
                        }
                    } elseif ($arResult['IBLOCK_SECTION_ID']) {
                        if (
                            isset($arResult['SECTION']) && 
                            $arResult['SECTION']['PATH']
                        ) {
                            $html .= '<div class="section_name font_13 color_999">'.$arResult['SECTION']['PATH'][0]['NAME'].'</div>';
                        }
                        $html .= '<div class="title font_14 font_bold">'.'<div class="name color_222">'.$arResult['NAME'].'</div></div>';
                        if (isset($templateData['MAP_DOP_INFO']) && $templateData['MAP_DOP_INFO']['TEXT']) {
                            $html .= '<div class="info font_14 color_999">'.$templateData['MAP_DOP_INFO']['TEXT'].'</div>';
                        }
                    }
            
                    
                    $html .= '</div></div></div>';
                    
                    $arPlacemarks[] = [
                        "ID" => $arResult["ID"],
						"LAT" => $gps_S,
						"LON" => $gps_N,
                        "TEXT" => $html,
                        "HTML" => $html
                    ];
                }
            }

	    	$arShop["PLACEMARKS"] = $arPlacemarks;
            $arShop["POINTS"] = [
                "LAT" => $mapLAT,
                "LON" => $mapLON,
            ];
            ?>
            <?if ($arShop['PLACEMARKS']):?>
                <?if ($arParams['MAP_TYPE'] == 'GOOGLE'):?>
                    <?$APPLICATION->IncludeComponent(
                        "bitrix:map.google.view",
                        "map",
                        Array(
                            "API_KEY" => \Bitrix\Main\Config\Option::get('fileman','google_map_api_key',''),
                            "COMPONENT_TEMPLATE" => "map",
                            "COMPOSITE_FRAME_MODE" => "A",
                            "COMPOSITE_FRAME_TYPE" => "AUTO",
                            "CONTROLS" => array(0=>"ZOOM",1=>"SMALL_ZOOM_CONTROL",),
                            "INIT_MAP_TYPE" => "ROADMAP",
                            "MAP_DATA" => serialize(array("google_lat" => $arShop["POINTS"]["LAT"], "google_lon" => $arShop["POINTS"]["LON"], "google_scale" => 20, "PLACEMARKS" => $arShop["PLACEMARKS"])),
                            "MAP_HEIGHT" => "500",
                            "MAP_ID" => "",
                            "MAP_WIDTH" => "100%",
                            "OPTIONS" => array(0=>"ENABLE_DBLCLICK_ZOOM",1=>"ENABLE_DRAGGING",),
                            "ZOOM_BLOCK" => array("POSITION"=>"right center",)
                        ),
                        false,
                        Array(
                            'HIDE_ICONS' => 'Y'
                        )
                    );?>
                <?else:?>
                    <?$APPLICATION->IncludeComponent(
                        "bitrix:map.yandex.view",
                        "map",
                        Array(
                            "API_KEY" => \Bitrix\Main\Config\Option::get('fileman','yandex_map_api_key',''),
                            "COMPONENT_TEMPLATE" => "map",
                            "COMPOSITE_FRAME_MODE" => "A",
                            "COMPOSITE_FRAME_TYPE" => "AUTO",
                            "CONTROLS" => array(0=>"ZOOM",1=>"SMALLZOOM",3=>"TYPECONTROL",4=>"SCALELINE",),
                            "INIT_MAP_TYPE" => "ROADMAP",
                            "KEY" => "AKk9BlwBAAAAcf5CSgMAHyfAq9knnHW9nsNrwnKOBpJ8-FUAAAAAAAAAAABE8lP1ifeROCbNOEGuF0oRi1P0xQ==",
                            "MAP_DATA" => serialize(array("yandex_lat" => $arShop["POINTS"]["LAT"], "yandex_lon" => $arShop["POINTS"]["LON"], "yandex_scale" => 15, "PLACEMARKS" => $arShop["PLACEMARKS"])),
                            "MAP_HEIGHT" => "500",
                            "MAP_ID" => "",
                            "MAP_WIDTH" => "100%",
                            "OPTIONS" => array(0=>"ENABLE_DBLCLICK_ZOOM",1=>"ENABLE_DRAGGING",),
                            "ZOOM_BLOCK" => array("POSITION"=>"right center",)
                        ),
                        false,
                        Array(
                            'HIDE_ICONS' => 'Y'
                        )
                    );?>
                <?endif;?>
            <?endif;?>
        </div>
    </div>
<?endif;?>