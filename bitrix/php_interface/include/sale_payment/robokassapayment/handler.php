<?php

namespace Sale\Handlers\PaySystem;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Error;
use Bitrix\Main\HttpApplication;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Request;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaySystem;
use Ipol\Robokassa;

Loc::loadMessages(__FILE__);
\CModule::IncludeModule("ipol.robokassa");

class RobokassaPaymentHandler
    extends PaySystem\ServiceHandler
    implements PaySystem\IRefundExtended
{

    public const PAYMENT_URL_RU = 'https://auth.robokassa.ru/Merchant/Index.aspx';
    public const PAYMENT_URL_KZ = 'https://auth.robokassa.kz/Merchant/Index.aspx';

	/**
	 * @param Payment $payment
	 * @param Request|null $request
	 * @return PaySystem\ServiceResult
	 */
	public function initiatePay(Payment $payment, Request $request = null)
	{
		$test = '';

        $paymentIdModificationCount = (int) Option::get(
            Robokassa\RobokassaPaymentService::$moduleId,
            'IPOL_ROBOKASSA_OPTIONS_TAB_PAYMENT_ID_MODIFICATION_COUNT',
            0
        );

        $enablePaymentByOrderId = Option::get(
            Robokassa\RobokassaPaymentService::$moduleId,
            'ENABLE_PAYMENT_BY_ORDER_ID',
            'N'
        ) === 'Y';

		if ($this->isTestMode($payment)) {
			$test = '_TEST';
		}

		$paymentShouldPay = (float) $payment->getField('SUM') - $payment->getSumPaid();

		/** @var array $signatureParams */
		$signatureParams = [
			$this->getBusinessValue($payment, 'SHOPLOGIN'),
			$paymentShouldPay,
		];

        if($enablePaymentByOrderId)
        {
            $signatureParams[] = $payment->getOrderId() + $paymentIdModificationCount;
        }
        else
        {
            $signatureParams[] = (int) $this->getBusinessValue($payment, 'PAYMENT_ID') + $paymentIdModificationCount;
        }

		if(
			$this->getBusinessValue($payment, 'OUT_CURRENCY') != ''
			&& !(
				$this->getBusinessValue($payment, 'COUNTRY_CODE') == "KZ"
				&& $this->getBusinessValue($payment, 'OUT_CURRENCY') == "KZT"
			)
		)
		{
			$signatureParams[] = $this->getBusinessValue($payment, 'OUT_CURRENCY');
		}
		
		$receipt = [
			'items' => Robokassa\RobokassaPaymentService::formReceiptData(
				$payment,
				$paymentShouldPay,
				$this,
				$this->getBusinessValue($payment, 'COUNTRY_CODE')
			)
		];

		if($this->getBusinessValue($payment, 'COUNTRY_CODE') !== 'KZ')
		{
			$receipt['sno'] = $this->getBusinessValue($payment, 'SNO');
		}

//        $receipt['payments'] = [
//            [
//                'type' => 'cashless',
//                'sum' => $paymentShouldPay
//            ]
//        ];

        $event = new \Bitrix\Main\Event(
            \Ipol\Robokassa\Event\Events::MODULE_ID,
            \Ipol\Robokassa\Event\Events::AFTER_FORMAT_RECEIPT_DATA,
            [
                'RECEIPT' => $receipt,
                'PAYMENT' => $payment,
            ]
        );

        $event->send();

        if ($event->getResults())
        {
            foreach($event->getResults() as $evenResult)
            {
                if($evenResult->getType() === \Bitrix\Main\EventResult::SUCCESS)
                {
                    $receipt = $evenResult->getParameters();
                }
            }
        }

        $ResultUrl2 = null;
		
		/** @var string $receipt */
        $receipt = !\defined('BX_UTF')
            ? \Bitrix\Main\Web\Json::encode($receipt)
            : \json_encode($receipt, \JSON_THROW_ON_ERROR)
        ;
		$signatureParams[] = $receipt;

        if(Option::get(Robokassa\RobokassaPaymentService::$moduleId, 'USE_TWO_STAGE_PAYMENT', 'N') === 'Y')
        {

            $server = HttpApplication::getInstance()->getContext()->getServer();

            if (
                $server->getRequestScheme() == 'https'
                || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
                || ($server->getServerPort() == '443')
            )
            {
                $ResultUrl2 = 'https://';
            }
            else
            {
                $ResultUrl2 = 'http://';
            }

            $ResultUrl2 .= $server->getHttpHost()
                . '/bitrix/tools/sale_ps_robokassa_two_stage_payment.php?'
                . \http_build_query(
                    [
                        'ORDER_ID' => $payment->getOrderId(),
                        'PAYMENT_ID' => $payment->getId()
                    ]
                )
            ;

            $signatureParams[] = 'true';
            $signatureParams[] = $ResultUrl2;
        }

		$signatureParams[] = $this->getBusinessValue($payment, 'SHOPPASSWORD' . $test);
		$signatureParams[] = 'SHP_BX_PAYSYSTEM_CODE=' . $payment->getPaymentSystemId();
		$signatureParams[] = 'SHP_HANDLER=ROBOKASSA.PAYMENT';
		$signatureParams[] = 'Shp_label=official_bitrix';

		/** @var string $signatureValue */
		$signatureValue = md5(implode(':', $signatureParams));

        $url = self::PAYMENT_URL_RU;
        
        if($this->getBusinessValue($payment, 'COUNTRY_CODE') == "KZ")
        {
            $url = self::PAYMENT_URL_KZ;
        }
        
		$params = array(
            'IPOL_ROBOKASSA_OPTIONS_TAB_PAYMENT_ID_MODIFICATION_COUNT' => $paymentIdModificationCount,
			'URL' => $url,
			'SIGNATURE_VALUE' => $signatureValue,
			'RECEIPT' => urlencode($receipt),
			'BX_PAYSYSTEM_CODE' => $payment->getPaymentSystemId(),
			'PAYMENT_SHOULD_PAY' => $paymentShouldPay,
			'OUT_CURRENCY' => $this->getBusinessValue($payment, 'OUT_CURRENCY'),
			'IFRAME_STATUS' => $this->getBusinessValue($payment, 'IFRAME_STATUS') == 'Y',
            'USE_TWO_STAGE_PAYMENT' => (Option::get(Robokassa\RobokassaPaymentService::$moduleId, 'USE_TWO_STAGE_PAYMENT', 'N') === 'Y'),
            'ResultUrl2' => $ResultUrl2,
            'ORDER_EMAIL' => $payment->getOrder()->getPropertyCollection()->getUserEmail()?->getValue(),
		);

        if($enablePaymentByOrderId)
        {
            $params['INV_ID'] = $paymentIdModificationCount + $payment->getOrderId();
        }
        else
        {
            $params['INV_ID'] = $paymentIdModificationCount + $payment->getId();
        }

        Robokassa\RobokassaLogger::log(
            Robokassa\RobokassaLogger::TYPE_REQUEST,
            Robokassa\RobokassaLogger::REQUEST_TYPE_PAYMENT,
            $params
        );

		$this->setExtraParams($params);

		return $this->showTemplate($payment, "template");
	}

	/**
	 * @return array
	 */
	public function getCurrencyList()
	{
		return array('RUB');
	}

	/**
	 * @return array
	 */
	public static function getIndicativeFields()
	{
		return array('SHP_HANDLER' => 'ROBOKASSA.PAYMENT');
	}

	/**
	 * @param Request $request
	 * @param $paySystemId
	 * @return bool
	 */
	protected static function isMyResponseExtended(Request $request, $paySystemId)
	{
		$id = $request->get('SHP_BX_PAYSYSTEM_CODE');
		return $id == $paySystemId;
	}

	/**
	 * @param Payment $payment
	 * @param $request
	 * @return bool
	 */
	private function checkHash(Payment $payment, Request $request)
	{
		$test = '';
		if ($this->isTestMode($payment)) {
			$test = '_TEST';
		}

		$hash = md5($request->get('OutSum') . ":" . $request->get('InvId') . ":" . $this->getBusinessValue($payment, 'SHOPPASSWORD2' . $test) . ':SHP_BX_PAYSYSTEM_CODE=' . $payment->getPaymentSystemId() . ':SHP_HANDLER=ROBOKASSA.PAYMENT' . ':Shp_label=official_bitrix');

		return ToUpper($hash) == ToUpper($request->get('SignatureValue'));
	}

	/**
	 * @param Payment $payment
	 * @param Request $request
	 * @return bool
	 */
	private function checkSum(Payment $payment, Request $request)
	{
		return true;
	}

    /**
     * Отображение ошибки не найденной оплаты или заказа
     * при обработке result запроса
     *
     * @param string $message
     * @return void
     */
    private function throwSystemErrorResponse(string $message)
    {
        global $APPLICATION;
        $APPLICATION->RestartBuffer();
        echo $message;
        die();
    }

	/**
	 * @param Request $request
	 * @return int
	 */
	public function getPaymentIdFromRequest(Request $request)
	{

        Loader::includeModule('sale');

        $paymentIdModificationCount = (int) Option::get(
            Robokassa\RobokassaPaymentService::$moduleId,
            'IPOL_ROBOKASSA_OPTIONS_TAB_PAYMENT_ID_MODIFICATION_COUNT',
            0
        );

        $enablePaymentByOrderId = Option::get(
            Robokassa\RobokassaPaymentService::$moduleId,
            'ENABLE_PAYMENT_BY_ORDER_ID',
            'N'
        ) === 'Y';

        if($enablePaymentByOrderId)
        {
            $orderId = (int) $request->get('InvId') - $paymentIdModificationCount;

            try
            {
                $order = Sale\Order::load($orderId);

                if($order === null)
                {
                    $this->throwSystemErrorResponse(
                        \sprintf(
                            Loc::getMessage('IPOL_ROBOKASSA_HANDLER_ERROR_ORDER_NOT_FOUND'),
                            $orderId
                        )
                    );
                }

                /** @var Sale\Payment $payment */
                foreach ($order->getPaymentCollection() as $payment)
                {
                    if(
                        $payment->getPaySystem()->getField('ACTION_FILE') === 'robokassapayment'
                        && !$payment->isPaid()
                    )
                    {
                        return $payment->getId();
                    }
                }

                $this->throwSystemErrorResponse(
                    \sprintf(
                        Loc::getMessage('IPOL_ROBOKASSA_HANDLER_ERROR_ORDER_NOT_FOUND'),
                        $orderId
                    )
                );
            }
            catch (\Exception $e)
            {
                $this->throwSystemErrorResponse(
                    \sprintf(
                        Loc::getMessage('IPOL_ROBOKASSA_HANDLER_ERROR_ORDER_NOT_FOUND_EXTEND'),
                        $orderId,
                        $e->getMessage()
                    )
                );
            }
        }

        $paymentId = (int) $request->get('InvId') - $paymentIdModificationCount;

        $payment = Sale\Internals\PaymentTable::getList(
            [
                'filter' => [
                    'ID' => $paymentId,
                ],
                'limit' => 1
            ]
        )->fetch();

        if(empty($payment))
        {
            $this->throwSystemErrorResponse(
                \sprintf(
                    Loc::getMessage('IPOL_ROBOKASSA_HANDLER_ERROR_PAYMENT_NOT_FOUND'),
                    $paymentId
                )
            );
        }

        return $paymentId;
	}


	/**
	 * @param Payment $payment
	 * @return string
	 */
	public function getPaymentMethod(Payment $payment)
	{

        $paymentMethod = $this->getBusinessValue($payment, 'PAYMENT_METHOD');

        return (!empty($paymentMethod)) ? $paymentMethod : 'full_prepayment';
	}

	/**
	 * @param Payment $payment
	 * @return string
	 */
	public function getPaymentObject(Payment $payment)
	{

		$paymentObject = $this->getBusinessValue($payment, 'PAYMENT_OBJECT');

        return (!empty($paymentObject)) ? $paymentObject : 'commodity';
	}

	/**
	 * @param Payment $payment
	 * @return string
	 */
	public function getPaymentObjectDelivery(Payment $payment)
	{

		$paymentObjectDelivery = $this->getBusinessValue($payment, 'PAYMENT_OBJECT_DELIVERY');

        return (!empty($paymentObjectDelivery)) ? $paymentObjectDelivery : 'commodity';
	}

	/**
	 * @return mixed
	 */
	protected function getUrlList()
	{
		return array(
			'pay' => array(
				self::ACTIVE_URL => 'https://auth.robokassa.ru/Merchant/Index.aspx',
			),
		);
	}

	/**
	 * @param Payment $payment
	 * @param Request $request
	 * @return PaySystem\ServiceResult
	 */
	public function processRequest(Payment $payment, Request $request)
	{

        Robokassa\RobokassaLogger::log(
            Robokassa\RobokassaLogger::TYPE_INCOME_REQUEST,
            Robokassa\RobokassaLogger::INCOME_REQUEST_TYPE_RESULT,
            [
                'data' => $_POST
            ]
        );

		if ($this->checkHash($payment, $request))
        {
			return $this->processNoticeAction($payment, $request);
		}
        else
        {

            $result = new PaySystem\ServiceResult();

            PaySystem\ErrorLog::add(
                array(
                    'ACTION'  => 'processRequest',
                    'MESSAGE' => 'Incorrect hash',
			    )
            );

			$result->addError(new Error('Incorrect hash'));
		}

		return $result;
	}

	/**
	 * @param Payment $payment
	 * @param Request $request
	 * @return PaySystem\ServiceResult
	 */
	private function processNoticeAction(Payment $payment, Request $request)
	{

		$result = new PaySystem\ServiceResult();

		$psStatusDescription = Loc::getMessage('SALE_HPS_ROBOXCHANGE_RES_NUMBER') . ": " . $request->get('InvId');
		$psStatusDescription .= "; " . Loc::getMessage('SALE_HPS_ROBOXCHANGE_RES_DATEPAY') . ": " . date("d.m.Y H:i:s");

		if ($request->get("IncCurrLabel") !== null) {
			$psStatusDescription .= "; " . Loc::getMessage('SALE_HPS_ROBOXCHANGE_RES_PAY_TYPE') . ": " . $request->get("IncCurrLabel");
		}

		$fields = array(
			"PS_STATUS"             => "Y",
			"PS_STATUS_CODE"        => "-",
			"PS_STATUS_DESCRIPTION" => $psStatusDescription,
			"PS_STATUS_MESSAGE"     => Loc::getMessage('SALE_HPS_ROBOXCHANGE_RES_PAYED'),
			"PS_SUM"                => $request->get('OutSum'),
			"PS_CURRENCY"           => $this->getBusinessValue($payment, "PAYMENT_CURRENCY"),
			"PS_RESPONSE_DATE"      => new DateTime(),
		);

		$result->setPsData($fields);

		if ($this->checkSum($payment, $request))
        {

			$result->setOperationType(PaySystem\ServiceResult::MONEY_COMING);
            
            if(!empty($payment->getOrder()->getSiteId()))
            {
                
                $orderStatus = Robokassa\RobokassaPaymentService::getAfterPaymentOrderStatus($payment->getOrder()->getSiteId());
                
                if(!empty($orderStatus))
                {
                    $payment->getOrder()->setField('STATUS_ID', $orderStatus);
                }
            }
		}
        else
        {

            PaySystem\ErrorLog::add(
                array(
                    'ACTION'  => 'processNoticeAction',
                    'MESSAGE' => 'Incorrect sum',
			    )
            );

            $result->addError(new Error('Incorrect sum'));
		}

		return $result;
	}

	/**
	 * @param Payment|null $payment
	 * @return bool
	 */
	protected function isTestMode(Payment $payment = null)
	{
		return ($this->getBusinessValue($payment, 'PS_IS_TEST') == 'Y');
	}

	/**
	 * @param PaySystem\ServiceResult $result
	 * @param Request $request
	 * @return mixed
	 */
	public function sendResponse(PaySystem\ServiceResult $result, Request $request)
	{

        global $APPLICATION;

        if ($result->isResultApplied())
        {
			$APPLICATION->RestartBuffer();
			echo 'OK' . $request->get('InvId');
		}
	}

	/**
	 * @param string $message
	 * @param array  $data
	 * @return void
	 */
	private function logToFile(string $message, array $data = []): void
    {

		$message = \sprintf('[%s] ACTION %s', date('Y-m-d H:i:s'), $message);

		if (!empty($data))
        {
			$message .= ". DATA:\n" . \print_r($data, true);
		}

        \file_put_contents(
			$_SERVER['DOCUMENT_ROOT'].'/robokassa.log',
			$message . "\n",
			FILE_APPEND
		);
	}

    /**
     * @param Payment $payment
     * @param $refundableSum
     * @return PaySystem\ServiceResult|void
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ArgumentTypeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function refund(Payment $payment, $refundableSum)
    {

        $result = new PaySystem\ServiceResult();

        try
        {
            Robokassa\RobokassaRefund::refundPayment($payment);
        }
        catch (\Exception $e)
        {
            $result->addError(new Error($e->getMessage()));
            return $result;
        }

        $refund = Robokassa\Internals\RefundOrderTable::getList(
            [
                'filter' => [
                    'ORDER_ID' => $payment->getOrder()->getId(),
                    'PAYMENT_ID' => $payment->getId(),
                ],
                'limit' => 1,
            ]
        )->fetchAll();

        if($refund['REFUND_STATUS'] === 'N')
        {
            $result->addErrors(
                [
                    new Error($refund['REFUND_MESSAGE'])
                ]
            );

            return $result;
        }

        return $result;
    }

    /**
     * @return true
     */
    public function isRefundableExtended()
    {
        return true;
    }

    /**
     * @param array $paySystemList
     * @return array
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     */
    public static function findMyDataRefundablePage(array $paySystemList): array
    {

        $result = [];

        $handler = PaySystem\Manager::getFolderFromClassName(get_called_class());
        $description = PaySystem\Manager::getHandlerDescription($handler);

        foreach($paySystemList as $paySystem)
        {
            $result[] = [
                'EXTERNAL_ID' => $paySystem['PAY_SYSTEM_ID'],
                'NAME' => $description['NAME'],
                'HANDLER' => 'robokassapayment',
                'CONFIGURED' => 'Y'
            ];
        }

        return $result;
    }
}
