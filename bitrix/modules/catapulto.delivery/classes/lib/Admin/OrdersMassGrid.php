<?php

namespace Ipol\Catapulto\Admin;

use \Bitrix\Main\Type\DateTime;

use \Ipol\Catapulto\Bitrix\Adapter;
use \Ipol\Catapulto\Bitrix\Tools;
use Ipol\Catapulto\Option;
use \Ipol\Catapulto\OrdersTable;
use \Ipol\Catapulto\Admin\Grid\DatabaseGrid;

/**
 * Class OrdersMassGrid
 *
 * @package Ipol\Catapulto\Admin
 */
class OrdersMassGrid extends DatabaseGrid
{
    protected bool $debug = false;
    /**
     * @var string
     */
    protected $fetchMode = self::FETCH_AS_ARRAY;
    
    /**
     * @var array
     */
    protected $defaultSorting = ['ID' => 'DESC'];
    
    
    /**
     * @var array
     */
    protected $defaultButtons = [];
    
    
    /**
     * @var array
     */
    protected $defaultColumns
        = [
            [
                'id'          => 'ID',
                'name'        => 'TABLE_ORDERS_MASS_ID',
                'sort'        => 'ID',
                'default'     => true,
                'editable'    => false,
                'filterable'  => true,
                'type'        => 'number',
                'quickSearch' => '%',
            ],
            [
                'id'          => 'ACCOUNT_NUMBER',
                'name'        => 'TABLE_ORDERS_MASS_BITRIX_NUMBER',
                'sort'        => 'ACCOUNT_NUMBER',
                'default'     => true,
                'editable'    => false,
                'filterable'  => true,
                'quickSearch' => '%',
            ],
            [
                'id'         => 'ORDER_ERROR',
                'name'       => 'TABLE_ORDERS_MASS_ORDER_ERROR',
                'sort'       => false,
                'default'    => true,
                'editable'   => false,
                'filterable' => false,
                'type'       => 'custom',
            ],
            [
                'id'         => 'ADDRESS',
                'name'       => 'TABLE_ORDERS_MASS_ADDRESS',
                'sort'       => false,
                'default'    => true,
                'editable'   => false,
                'filterable' => false,
                'type'       => 'custom',
            ],
            /*
            [
                'id'         => 'INSURANCE',
                'name'       => 'TABLE_ORDERS_MASS_INSURANCE',
                'sort'       => false,
                'default'    => true,
                'editable'   => false,
                'filterable' => false,
                'type'       => 'custom',
            ],*/
            [
                'id'         => 'INSURANCE_VALUE',
                'name'       => 'TABLE_ORDERS_MASS_INSURANCE_VALUE',
                'sort'       => false,
                'default'    => true,
                'editable'   => false,
                'filterable' => false,
                'type'       => 'custom',
            ],
            [
                'id'         => 'FITTING',
                'name'       => 'TABLE_ORDERS_MASS_FITTING',
                'sort'       => false,
                'default'    => true,
                'editable'   => false,
                'filterable' => false,
                'type'       => 'custom',
            ],
            [
                'id'         => 'PARTIAL_REDEMPTION',
                'name'       => 'TABLE_ORDERS_MASS_PARTIAL_REDEMPTION',
                'sort'       => false,
                'default'    => true,
                'editable'   => false,
                'filterable' => false,
                'type'       => 'custom',
            ],
            [
                'id'         => 'ORDER_PAID',
                'name'       => 'TABLE_ORDERS_MASS_ORDER_PAID',
                'sort'       => false,
                'default'    => true,
                'editable'   => false,
                'filterable' => false,
                'type'       => 'custom',
            ],
            [
                'id'         => 'NP_SUM',
                'name'       => 'TABLE_ORDERS_MASS_NP_SUM',
                'sort'       => false,
                'default'    => true,
                'editable'   => false,
                'filterable' => false,
                'type'       => 'custom',
            ],
        
        ];
    
    protected $select = ['*'];
    
    public function getDefaultColumns()
    {
        if($this->isDebug()) {
            $this->defaultColumns[] = [
                'id'         => 'NP_SUM_LOG',
                'name'       => 'TABLE_ORDERS_MASS_NP_INFO',
                'sort'       => false,
                'default'    => true,
                'editable'   => false,
                'filterable' => false,
                'type'       => 'custom',
            ];
        }
        
        return $this->defaultColumns;
    }
    
    protected function isDebug(): bool
    {
        if(isset($_REQUEST['debug']) && $_REQUEST['debug'] === 'Y') {
            $this->debug = true;
        }
        else {
            $this->debug = false;
        }
        
        return $this->debug;
    }
    
    protected function getQuery()
    {
        $query = parent::getQuery();
        
        $query->registerRuntimeField('CATAPULTO_ORDER', [
            'data_type' => \Ipol\Catapulto\OrdersTable::class,
            'reference' => ['=ref.BITRIX_ID' => 'this.ID'],
            'join_type' => 'LEFT',
        ]);
        
        $query->setFilter(array_merge($query->getFilter(), [
            '=DELIVERY_ID'            => \Ipol\Catapulto\Bitrix\Handler\Deliveries::getCatapultoDeliveryId(),
            '=CATAPULTO_ORDER.NUMBER' => false
        ]));
        
        return $query;
    }
    
    public function getDefaultFilter()
    {
        return [];
    }
    
    /**
     * @var array
     */
    protected $defaultRowActions
        = [
            // Acceptable system icon classes are in \bitrix\js\main\popup\dist\main.popup.bundle.css
            // menu-popup-item-copy for documents
            
            'VIEW_BITRIX_ORDER' => [
                'ICONCLASS' => 'menu-popup-item-delegate',
                'TEXT'      => 'TABLE_ORDERS_MASS_ROW_VIEW_BITRIX_ORDER',
                'ONCLICK'   => 'document.location.href="sale_order_view.php?ID=#ID#"',
            ],
        ];
    
    /**
     * Get grid action panel controls
     *
     * @return array
     * @see component bitrix:main.ui.grid
     */
    public function getControls()
    {
        return [
            [
                'ID'       => 'mass-send',
                'TYPE'     => 'BUTTON',
                'NAME'     => 'mass-send',
                'TEXT'     => Tools::getMessage('TABLE_ORDERS_MASS_BTN_SEND'),
                'ONCHANGE' => [
                    [
                        'ACTION' => 'CALLBACK',
                        'DATA'   => [
                            [
                                'JS' => '
                                    var grid = BX.Main.gridManager.getInstanceById("' . $this->getId() . '");
                                    var ids  = grid.getRows().getSelectedIds();
                                    if (ids.length > 0){
                                        var dataSend = [];
                                        for(var i = 0; i < ids.length; i++){
                                            let tr = document.querySelector(`tr[data-id="${ids[i]}"]`);
                                            if(tr) {
                                                dataSend.push({
                                                    "id": ids[i],
                                                    "ins": true,
                                                    "sumToPay": tr.querySelector(`input#sumToPay_${ids[i]}`).value || 0,
                                                    "isNp": tr.querySelector(`input#isNp_${ids[i]}`).value || `N`,
                                                    "isCod": tr.querySelector(`input#isCod_${ids[i]}`).value || `N`,
                                                    "isSmsAmount": tr.querySelector(`input#isSmsAmount_${ids[i]}`).value || `N`,
                                                    "services": tr.querySelector(`input#services_${ids[i]}`).value || ``
                                                }); //always true ins
                                            }
                                        }
                                        ' . CATAPULTO_DELIVERY_LBL . 'controller.getPage("main").actions.sendOrdersMass(dataSend);
                                    }
                                ',
                            ]
                        ]
                    ]
                ]
            ],
        ];
    }
    
    /**
     * Return ORM data mapper for data selection
     *
     * @return \Bitrix\Main\ORM\Data\DataManager
     */
    public function getDataMapper()
    {
        return \Bitrix\Sale\Internals\OrderTable::class;
    }
    
    /**
     * Get single data item in grid row format
     *
     * @param array $item
     *
     * @return array
     */
    protected function getRow($item)
    {
        $ret = parent::getRow($item);
        
        // Add some human-readable texts instead of specific identifiers there
        // $ret['data'][__COLUMN_NAME__] = ... ;
        
        if (!empty($ret['data']['UPTIME'])) {
            $ret['data']['UPTIME'] = DateTime::createFromTimestamp($ret['data']['UPTIME'])->format("H:i:s d.m.Y");
        }
        
        // convert sender address
        if (!empty($ret['data']['SENDER_ADDRESS'])) {
            $address                       = unserialize($ret['data']['SENDER_ADDRESS'], ['allowed_classes' => false]);
            $ret['data']['SENDER_ADDRESS'] = Adapter::getAddressString($address);
        }
        
        // convert receiver address
        if (!empty($ret['data']['RECEIVER_ADDRESS'])) {
            $address                         = unserialize($ret['data']['RECEIVER_ADDRESS'], ['allowed_classes' => false]);
            $ret['data']['RECEIVER_ADDRESS'] = Adapter::getAddressString($address);
        }
        
        // convert boolean values
        array_walk($ret['data'], 'Ipol\Catapulto\Bitrix\Adapter::convertBooleanValues');
        
        $ret['attrs'] = ['cancelable' => ($ret['data']['MAIN_STATUS'] == 'in_proccess') ? 1 : 0];
        
        $ret['data']['ACCOUNT_NUMBER'] = '<a href="/bitrix/admin/sale_order_view.php?lang=ru&ID=' . $ret['data']['ID'] . '" target="_blank">' . $ret['data']['ACCOUNT_NUMBER'] . '</a>';
        
        $this->validateOrder($ret);
        
        if ($ret['d']['vValid'] === 'N') {
            $ret['attrs']['data-hide-checkbox'] = true;
        }
        else {
            $ret['attrs']['data-hide-checkbox'] = false;
        }
        
        $ret['data']['ORDER_ERROR'] = $ret['d']['vProblem'];
        
        $ret['data']['ADDRESS'] = $ret['d']['receiverAddressLine'];
        
        /*
        if ($ret['d']['vldInsurance'] === 'Y') {
            $ret['data']['INSURANCE'] = $ret['d']['needInsurance'] === 'Y' ? Tools::getMessage('ORDERS_MASS_Y') : Tools::getMessage('ORDERS_MASS_N');
        }
        else {
            $ret['data']['INSURANCE'] = '<input class="form-check-input ctpt_add_insurance" type="checkbox" value="Y" />';
        }*/
        
        $ret['data']['INSURANCE_VALUE'] = number_format((float)$ret['d']['insuranceValue'], '2', '.', ' ');
        $ret['data']['FITTING']         = $ret['d']['fitting'] === 'Y' ? Tools::getMessage('ORDERS_MASS_Y') : Tools::getMessage('ORDERS_MASS_N');
        $ret['data']['ORDER_PAID']      = $ret['d']['orderPaid'] === 'Y' ? Tools::getMessage('ORDERS_MASS_Y') : Tools::getMessage('ORDERS_MASS_N');
        
        if ($ret['d']['vValidStage1'] === 'Y') {
            $ret['data']['NP_SUM'] = number_format((float)$ret['d']['sumToPay'], '2', '.', ' ')
                                     . '<input type="hidden" id="sumToPay_' . $ret['data']['ID'] . '" value="' . (float)$ret['d']['sumToPay'] . '">'
                                     . '<input type="hidden" id="isNp_' . $ret['data']['ID'] . '" value="' . $ret['d']['isNp'] . '">'
                                     . '<input type="hidden" id="isCod_' . $ret['data']['ID'] . '" value="' . $ret['d']['isCod'] . '">'
                                     . '<input type="hidden" id="isSmsAmount_' . $ret['data']['ID'] . '" value="' . $ret['d']['isSmsAmount'] . '">'
                                     . '<input type="hidden" id="services_' . $ret['data']['ID'] . '" value="' . implode(',', $ret['d']['services']) . '">';
        }
        
        if ($ret['d']['vValidStage3'] === 'Y') {
            $ret['data']['PARTIAL_REDEMPTION'] = $ret['d']['partialRedemption'] === 'Y' ? Tools::getMessage('ORDERS_MASS_Y') : Tools::getMessage('ORDERS_MASS_N');
        }
        
        if($this->isDebug()) {
            $ret['data']['NP_SUM_LOG'] = implode("<br>\n", $ret['d']['sumToPayLog']);
        }
        
        return $ret;
    }
    
    protected function validateOrder(&$item)
    {
        $orderPrice = (float)($item['data']['PRICE'] - $item['data']['PRICE_DELIVERY']);
        $payTypeNP  = Option::get('payTypeNP');
        
        $order = \Ipol\Catapulto\Bitrix\Adapter::getOrderData($item['data']['ID']);
        
        $rateResult = $order->getField('rateResult');
        $isNpFilter = in_array('NP', explode(',', $rateResult['services_filter'] ?? ''), false);
        $isNp       = $order->getPayment()->getIsNp();
        $isCod      = $rateResult['was_cod'] === true; //$isCod = $order->getPayment()->getIsCod();
        
        //Если COD и нет NP, то NP = false. Т.е. оплата только доставки без товаров.
        if ($isCod && !$isNpFilter) {
            $isNp = false;
        }
        
        //Значение по умолчанию
        $oData = [
            //validators
            'vValid'       => 'N',
            'vValidStage1' => 'N', //rate validated
            'vValidStage2' => 'N', //address validated
            'vValidStage3' => 'N', //Cargo validated
            
            'vldInsurance' => 'Y', //insurance validated
            
            'vProblem'            => Tools::getMessage('ORDERS_MASS_N'), //Error message
            
            //data for sending
            'receiverContactId'   => '',
            'receiverName'        => '',
            'receiverPhone'       => '',
            'receiverCompany'     => '',
            'receiverZip'         => '',
            'receiverAddressLine' => '',
            'receiverCity'        => '',
            'receiverStreet'      => '',
            'receiverBuilding'    => '',
            'receiverFlat'        => '',
            'needInsurance'       => 'Y', //сейчас страховка включена по-умолчанию
            'insuranceValue'      => $orderPrice,
            'orderPaid'           => 'N',
            'sumToPay'            => '0',
            'sumToPayLog'         => [],
            'isNp'                => $isNp ? 'Y' : 'N',
            'isCod'               => $isCod ? 'Y' : 'N',
            'services'            => [],
            'comment'             => $item['data']['COMMENTS'],
            
            'fitting'           => 'N',
            'partialRedemption' => 'N',
            'isSmsAmount'       => 'N',
        ];
        
        
        //1 - rate validating
        if (!is_array($rateResult) || empty($rateResult)) {
            $oData['vProblem'] = Tools::getMessage('TABLE_ORDERS_MASS_VALIDATION_1');
            $ordersData        = [
                'o' => $order,
                'd' => $oData,
            ];
        }
        else {
            //2 - check rate date pickup
            $currentDate         = (new \DateTime())->setTime(0, 0);
            $pickupDate          = \DateTime::createFromFormat('Y-m-d', ($rateResult['pickup_date'] ?? $rateResult['pickup_day']));
            $isPickupDateExpired = true;
            $pickupDateValid     = false;
            if ($pickupDate instanceof \DateTime) {
                $pickupDateValid = true;
                $pickupDate->setTime(0, 0);
                $isPickupDateExpired = $currentDate->getTimestamp() > $pickupDate->getTimestamp();
            }
            
            if ($isPickupDateExpired || !$pickupDateValid) {
                $oData['vProblem'] = Tools::getMessage('TABLE_ORDERS_MASS_VALIDATION_2');
                $ordersData        = [
                    'o' => $order,
                    'd' => $oData,
                ];
            }
            else {
                $oData['vValidStage1'] = 'Y';
                
                //order info
                $orderPriceWithDlv = (float)($rateResult['price'] ?? 0);
                $addressTo         = $order->getAddressTo();
                $buyer             = $order->getBuyers()->getFirst();
                
                $oData['needInsurance'] = 'Y';
                //$oData['needInsurance'] = ($rateResult['was_insurance'] ?? false) ? 'Y' : 'N';
                $orderPaid          = ($item['data']['PAYED'] == 'Y');
                $oData['orderPaid'] = $orderPaid ? 'Y' : 'N';
                
                //Проверяем, оплачен ли заказ. Если да, все ок. Можно отправить с НП = 0,
                //но если не оплачен, то выдаем проблему с заказом, т.к. пользователю нужно проставить чекбоксы в разделе Оплата
                
                if ($orderPaid) {
                    $oData['sumToPay']      = 0;
                    $oData['isNp']          = 'N';
                    $oData['sumToPayLog'][] = 'Заказ оплачен. НП = 0.';
                }
                else {
                    
                    if (($isNpFilter && $isNp) || $isCod) {
                        //Наложенный платеж на товары
                        if($isNpFilter && $isNp) {
                            $oData['sumToPay']      = $orderPrice;
                            $oData['sumToPayLog'][] = 'NP -> НП = стоимости товаров: ' . $orderPrice;
                        }
                        else {
                            $oData['sumToPay']      = 0;
                            $oData['sumToPayLog'][] = 'NP -> нет';
                        }
                        
                        if ($isCod) {
                            //Добавляем стоимость доставки к НП
                            $deliveryCost = $rateResult['price'] === 0 ? 0 : (float)$rateResult['base_price_with_services'];
                            $oData['sumToPay']      += $deliveryCost;
                            $oData['sumToPayLog'][] = 'COD -> к НП добавляем стоимость доставки: ' . $deliveryCost;
                        }
                        
                        //Добавляем к НП стоимость услуг
                        //Все дополнительные услуги у нас включаются в стоимость доставки.
                        //Соответственно, если тип НП — "Оплата товаров", то в сумму НП должна попадать только сумма товаров.
                        //Если же тип НП — "Оплата доставки" или "Оплата товаров и доставки", то да, все дополнительные услуги как часть оплаты доставки в сумму НП должны быть включены
                        foreach ($rateResult['services'] as $arService) {
                            if ($arService['name'] == 'cod_amount') {
                                //Если была включена опция COD
                                if ($isCod) {
                                    $oData['services'][] = $arService['name'];
                                }
                            }
                            elseif ($arService['name'] == 'fitting_amount') {
                                //Если включена опция примерки
                                if ($rateResult['is_fitting']) {
                                    $oData['services'][]    = $arService['name'];
                                    if ($isCod) {
                                        $oData['sumToPay'] += $arService['cost'];
                                        $oData['sumToPayLog'][] = 'Включена опция примерки и COD. К НП прибавляем сумму ' . $arService['cost'] . ' за ' . $arService['name'] . '. Итого: ' . $oData['sumToPay'];
                                    }
                                }
                            }
                            elseif ($arService['name'] == 'partial_redemption_amount') {
                                if ($order->getField('isPartialRedInRate')) {
                                    
                                    if(!$order->getField('isSingleProduct')) {
                                        $oData['partialRedemption'] = 'Y';
                                    }
                                    else {
                                        if ($isCod) {
                                            $oData['sumToPay']      -= $arService['cost'];
                                            $oData['sumToPayLog'][] = 'Включена опция частичного выкупа и COD, но в заказе 1 товар, поэтому исключаем сумму за услугу: ' . $arService['cost'] . '. Итого: ' . $oData['sumToPay'];
                                        }
                                    }
                                    
                                    //Если включена опция частичного выкупа
                                    //if ($rateResult['with_partial_red'] || $rateResult['is_fitting']) {
                                    if ($oData['partialRedemption'] === "Y") {
                                        $oData['services'][]    = $arService['name'];
                                    }
                                }
                                
                            }
                            elseif ($arService['name'] == 'sms_amount') {
                                //Если включена опция смс-уведомления
                                if (strpos($rateResult['services_filter'], 'sms_amount') !== false) {
                                    $oData['services'][]  = $arService['name'];
                                    $oData['isSmsAmount'] = 'Y';
                                }
                            }
                        }
                    }
                    else {
                        $orderPrice = 0; //Нет НП, значит ставим 0.
                        $oData['sumToPay']      = 0;
                        $oData['sumToPayLog'][] = 'Нет NP -> НП = 0';
                        $oData['vProblem'] = Tools::getMessage('TABLE_ORDERS_MASS_VALIDATION_3');
                        $oData['vValidStage1'] = 'N';
                    }
                    
                    
                }
                
                $oData['fitting'] = ($rateResult['is_fitting'] ?? false) ? 'Y' : 'N';
                
                //3 - check address
                $newShippingAddress = $addressTo->getField('dadata_unrestricted_value');
                if (empty($newShippingAddress)) {
                    $oData['vProblem'] = Tools::getMessage('TABLE_ORDERS_MASS_VALIDATION_3.1');
                    $ordersData        = [
                        'o' => $order,
                        'd' => $oData,
                    ];
                }
                else {
                    $personalType = \Bitrix\Sale\Internals\BusinessValuePersonDomainTable::getList([
                        'filter' => ['=PERSON_TYPE_ID' => ($item['data']['PERSON_TYPE_ID'] ?? 1)]
                    ])->fetch();
                    
                    $oData['receiverCompany'] = $buyer->getField('company');
                    if ($personalType['DOMAIN'] == 'E' && empty($buyer->getField('company'))) {
                        $oData['vProblem'] = Tools::getMessage('TABLE_ORDERS_MASS_VALIDATION_4');
                        $ordersData        = [
                            'o' => $order,
                            'd' => $oData,
                        ];
                    }
                    else {
                        $oData['receiverName'] = $buyer->getFullName();
                        if (empty($oData['receiverName'])) {
                            $oData['vProblem'] = Tools::getMessage('TABLE_ORDERS_MASS_VALIDATION_5');
                            $ordersData        = [
                                'o' => $order,
                                'd' => $oData,
                            ];
                        }
                        
                        $oData['receiverPhone']       = $buyer->getPhone();
                        $oData['receiverZip']         = $addressTo->getZip();
                        $oData['receiverAddressLine'] = $addressTo->getField('dadata_unrestricted_value');
                        $oData['receiverCity']        = $addressTo->getCity();
                        $oData['receiverStreet']      = $addressTo->getStreet();
                        $oData['receiverBuilding']    = $addressTo->getBuilding();
                        $oData['receiverFlat']        = $addressTo->getFlat();
                        
                        if (
                            empty($oData['receiverZip'])
                            || empty($oData['receiverCity'])
                            || empty($oData['receiverStreet'])
                            || empty($oData['receiverBuilding'])
                        ) {
                            $oData['vProblem'] = Tools::getMessage('TABLE_ORDERS_MASS_VALIDATION_6');
                            $ordersData        = [
                                'o' => $order,
                                'd' => $oData,
                            ];
                        }
                        else {
                            $oData['vValidStage2'] = 'Y';
                            
                            //check cargo
                            $cargoError = \Ipol\Catapulto\OrderHandler::checkCargo($order);
                            
                            if (!empty($cargoError)) {
                                $oData['vProblem'] = $cargoError;
                                $ordersData        = [
                                    'o' => $order,
                                    'd' => $oData,
                                ];
                            }
                            else {
                                $oData['vValidStage3'] = 'Y';
                                $arValid               = [$oData['vValidStage1'], $oData['vValidStage2'], $oData['vValidStage3']];
                                $oData['vValid']       = in_array('N', $arValid, true) ? 'N' : 'Y'; //order valid
                                
                                $ordersData = [
                                    'o' => $order,
                                    'd' => $oData,
                                ];
                                
                                //check order paid and insurance
                                /*
                                if (!$orderPaid && $order->getField('insurance_cost') > 0) {
                                    $oData['vProblem']     = Tools::getMessage('TABLE_ORDERS_MASS_VALIDATION_7');
                                    $oData['vldInsurance'] = 'N'; //insurance not valid
                                    $ordersData            = [
                                        'o' => $order,
                                        'd' => $oData,
                                    ];
                                }
                                else {
                                    $oData['vValid'] = 'Y'; //order valid
                                    
                                    $ordersData = [
                                        'o' => $order,
                                        'd' => $oData,
                                    ];
                                }*/
                            }
                        }
                    }
                }
            }
        }
        
        $item = array_merge($item, $ordersData);
    }
    
    /**
     * Get row actions available for single row
     *
     * @param array $item
     *
     * @return array
     */
    protected
    function getRowActions(
        $item
    ) {
        $ret = parent::getRowActions($item);
        foreach ($ret as $index => $action) {
            $ret[$index]['LINK']    = str_replace(['#BITRIX_ID#', '#CATAPULTO_ID#'], [$item['BITRIX_ID'], $item['CATAPULTO_ID']], $action['LINK']);
            $ret[$index]['ONCLICK'] = str_replace(['#BITRIX_ID#', '#CATAPULTO_ID#'], [$item['BITRIX_ID'], $item['CATAPULTO_ID']], $action['ONCLICK']);
        }
        
        return array_values($ret);
    }
}
