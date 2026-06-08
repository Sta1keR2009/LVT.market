<?php

use Bitrix\Main\Application;
use Bitrix\Main\Mail\Mail;
use Bitrix\Sale\Order;
use Bitrix\Sale\Internals\OrderTable;
use Rbs\Moysklad\ApiNew;

class MoySkladInvoiceHandler
{
    private const ORDER_FILE_PROP_CODE = 'INVOICE_FILE_ID';
    private const ORDER_READY_PROP_CODE = 'INVOICE_READY_AT';
    private const ORDER_MS_ID_PROP_CODE = 'INVOICE_MS_ID';
    private const ORDER_NUMBER_PROP_CODE = 'INVOICE_NUMBER';
    private const MAIL_EVENT = 'SALE_INVOICE_READY';
    private const LOG_FILE = '/local/logs/moysklad_invoice_hook.log';

    public static function processEvent(object $eventHook): array
    {
        try {
            $href = (string)($eventHook->meta->href ?? '');
            if ($href === '') {
                return self::fail('Empty event href');
            }

            $invoice = ApiNew::get($href, ['expand' => 'positions.assortment,agent,organization,customerOrder']);
            if (!is_object($invoice) || !empty($invoice->hasErrors)) {
                return self::fail('Failed to load invoiceout: ' . self::toJson($invoice));
            }

            $externalCode = self::extractExternalCode($invoice);
            if ($externalCode === '') {
                return self::fail('Unable to resolve customerOrder.externalCode');
            }

            $order = self::findBitrixOrder($externalCode);
            if (!$order instanceof Order) {
                return self::fail('Bitrix order not found by externalCode=' . $externalCode);
            }

            $invoiceMsId = (string)($invoice->id ?? '');
            if (self::isDuplicateEvent($order, $invoiceMsId)) {
                self::log('SKIP_DUPLICATE', [
                    'order_id' => $order->getId(),
                    'external_code' => $externalCode,
                    'invoice_ms_id' => $invoiceMsId,
                ]);

                return [
                    'ok' => true,
                    'skipped' => true,
                    'reason' => 'Invoice already published',
                    'order_id' => $order->getId(),
                ];
            }

            $invoiceData = self::buildInvoiceData($invoice, $order);
            $pdfPath = InvoicePdfGenerator::generate($invoiceData, 'invoice_' . $order->getId() . '_' . date('Ymd_His'));

            $fileId = self::savePdfToBitrixFile($pdfPath);
            self::storeInvoicePublicationData(
                $order,
                $fileId,
                $invoiceMsId,
                (string)($invoiceData['invoice_number'] ?? '')
            );

            $email = self::resolveOrderEmail($order, $invoiceData);
            self::sendInvoiceEmail($email, $order, $invoiceData, $pdfPath);

            self::log('SUCCESS', [
                'order_id' => $order->getId(),
                'external_code' => $externalCode,
                'invoice' => $invoiceData['invoice_number'],
                'file_id' => $fileId,
            ]);

            return ['ok' => true, 'order_id' => $order->getId(), 'file_id' => $fileId];
        } catch (\Throwable $e) {
            self::log('ERROR', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return self::fail('Exception: ' . $e->getMessage());
        }
    }

    private static function extractExternalCode(object $invoice): string
    {
        if (!empty($invoice->customerOrder) && is_object($invoice->customerOrder)) {
            if (!empty($invoice->customerOrder->externalCode)) {
                return (string)$invoice->customerOrder->externalCode;
            }
            if (!empty($invoice->customerOrder->meta->href)) {
                $orderMs = ApiNew::get((string)$invoice->customerOrder->meta->href);
                if (is_object($orderMs) && empty($orderMs->hasErrors) && !empty($orderMs->externalCode)) {
                    return (string)$orderMs->externalCode;
                }
            }
        }
        return '';
    }

    private static function findBitrixOrder(string $externalCode): ?Order
    {
        if (ctype_digit($externalCode)) {
            $order = Order::load((int)$externalCode);
            if ($order instanceof Order) {
                return $order;
            }
        }

        $row = OrderTable::getList([
            'select' => ['ID'],
            'filter' => [
                [
                    'LOGIC' => 'OR',
                    '=XML_ID' => $externalCode,
                    '=ACCOUNT_NUMBER' => $externalCode,
                ],
            ],
            'limit' => 1,
        ])->fetch();

        if (!empty($row['ID'])) {
            return Order::load((int)$row['ID']);
        }

        return null;
    }

    private static function buildInvoiceData(object $invoice, Order $order): array
    {
        $positions = [];
        $rows = $invoice->positions->rows ?? [];
        if (is_array($rows)) {
            foreach ($rows as $row) {
                $qty = (float)($row->quantity ?? 0);
                $priceMinor = (float)($row->price ?? 0);
                $price = self::fromMinorUnit($priceMinor);
                $sum = $qty * $price;
                $positions[] = [
                    'name' => (string)($row->assortment->name ?? $row->name ?? 'Позиция'),
                    'quantity' => $qty,
                    'price' => $price,
                    'sum' => $sum,
                ];
            }
        }

        $total = self::fromMinorUnit((float)($invoice->sum ?? 0));
        if ($total <= 0 && !empty($positions)) {
            $total = array_sum(array_column($positions, 'sum'));
        }

        return [
            'invoice_number' => (string)($invoice->name ?? ''),
            'invoice_date' => self::formatDate((string)($invoice->moment ?? '')),
            'currency' => (string)($invoice->rate->currency->code ?? 'RUB'),
            'positions' => $positions,
            'total' => $total,
            'company' => [
                'name' => (string)($invoice->organization->name ?? ''),
                'inn' => (string)($invoice->organization->inn ?? ''),
                'kpp' => (string)($invoice->organization->kpp ?? ''),
                'bank' => '',
                'account' => '',
            ],
            'buyer' => [
                'name' => (string)($invoice->agent->name ?? ''),
                'inn' => (string)($invoice->agent->inn ?? ''),
                'email' => self::resolveOrderEmail($order, []),
            ],
            'order_id' => (string)$order->getId(),
            'order_number' => (string)$order->getField('ACCOUNT_NUMBER'),
        ];
    }

    private static function savePdfToBitrixFile(string $pdfPath): int
    {
        if (!is_readable($pdfPath)) {
            throw new RuntimeException('Generated PDF file not found: ' . $pdfPath);
        }

        $fileArray = \CFile::MakeFileArray($pdfPath);
        $fileArray['MODULE_ID'] = 'sale';
        $fileId = (int)\CFile::SaveFile($fileArray, 'invoices');
        if ($fileId <= 0) {
            throw new RuntimeException('Unable to save PDF into CFile');
        }
        return $fileId;
    }

    private static function isDuplicateEvent(Order $order, string $invoiceMsId): bool
    {
        if ($invoiceMsId === '') {
            return false;
        }

        $savedMsId = trim(self::readOrderPropertyValue($order, self::ORDER_MS_ID_PROP_CODE));
        if ($savedMsId === '') {
            $savedMsId = trim(self::readMarkerFromDescription(
                (string)$order->getField('USER_DESCRIPTION'),
                self::ORDER_MS_ID_PROP_CODE
            ));
        }

        $fileId = (int)self::readOrderPropertyValue($order, self::ORDER_FILE_PROP_CODE);
        if ($fileId <= 0) {
            $fileId = (int)self::readMarkerFromDescription(
                (string)$order->getField('USER_DESCRIPTION'),
                self::ORDER_FILE_PROP_CODE
            );
        }

        return $savedMsId !== '' && $savedMsId === $invoiceMsId && $fileId > 0;
    }

    private static function storeInvoicePublicationData(Order $order, int $fileId, string $invoiceMsId, string $invoiceNumber): void
    {
        $collection = $order->getPropertyCollection();
        $hasFileProp = false;

        $fileProp = $collection->getItemByOrderPropertyCode(self::ORDER_FILE_PROP_CODE);
        if ($fileProp) {
            $fileProp->setValue($fileId);
            $hasFileProp = true;
        }

        $readyAt = date('c');
        $readyProp = $collection->getItemByOrderPropertyCode(self::ORDER_READY_PROP_CODE);
        if ($readyProp) {
            $readyProp->setValue($readyAt);
        }

        $msIdProp = $collection->getItemByOrderPropertyCode(self::ORDER_MS_ID_PROP_CODE);
        if ($msIdProp && $invoiceMsId !== '') {
            $msIdProp->setValue($invoiceMsId);
        }

        $numberProp = $collection->getItemByOrderPropertyCode(self::ORDER_NUMBER_PROP_CODE);
        if ($numberProp && $invoiceNumber !== '') {
            $numberProp->setValue($invoiceNumber);
        }

        $desc = (string)$order->getField('USER_DESCRIPTION');
        if (!$hasFileProp) {
            $desc = self::upsertMarkerInDescription($desc, self::ORDER_FILE_PROP_CODE, (string)$fileId);
        }
        if ($invoiceMsId !== '') {
            $desc = self::upsertMarkerInDescription($desc, self::ORDER_MS_ID_PROP_CODE, $invoiceMsId);
        }
        if ($invoiceNumber !== '') {
            $desc = self::upsertMarkerInDescription($desc, self::ORDER_NUMBER_PROP_CODE, $invoiceNumber);
        }
        $desc = self::upsertMarkerInDescription($desc, self::ORDER_READY_PROP_CODE, $readyAt);
        $order->setField('USER_DESCRIPTION', trim($desc));

        $result = $order->save();
        if (!$result->isSuccess()) {
            throw new RuntimeException('Order save failed: ' . implode('; ', $result->getErrorMessages()));
        }
    }

    private static function readOrderPropertyValue(Order $order, string $code): string
    {
        $collection = $order->getPropertyCollection();
        $prop = $collection->getItemByOrderPropertyCode($code);
        if (!$prop) {
            return '';
        }

        $value = $prop->getValue();
        if (is_array($value)) {
            $value = reset($value);
        }

        return trim((string)$value);
    }

    private static function readMarkerFromDescription(string $description, string $code): string
    {
        $pattern = '/\[' . preg_quote($code, '/') . '\]\:\s*(.+)/u';
        if (!preg_match($pattern, $description, $matches)) {
            return '';
        }

        return trim((string)($matches[1] ?? ''));
    }

    private static function upsertMarkerInDescription(string $description, string $code, string $value): string
    {
        $line = '[' . $code . ']: ' . $value;
        $pattern = '/^\[' . preg_quote($code, '/') . '\]\:\s*.*$/mu';
        if (preg_match($pattern, $description)) {
            return (string)preg_replace($pattern, $line, $description);
        }

        $description = trim($description);
        if ($description === '') {
            return $line;
        }

        return $description . PHP_EOL . $line;
    }

    private static function resolveOrderEmail(Order $order, array $data): string
    {
        if (!empty($data['buyer']['email'])) {
            return (string)$data['buyer']['email'];
        }

        $collection = $order->getPropertyCollection();
        foreach ($collection as $property) {
            if ($property->getField('CODE') === 'EMAIL') {
                $value = trim((string)$property->getValue());
                if ($value !== '') {
                    return $value;
                }
            }
        }

        $userId = (int)$order->getUserId();
        if ($userId > 0) {
            $user = \CUser::GetByID($userId)->Fetch();
            if (!empty($user['EMAIL'])) {
                return (string)$user['EMAIL'];
            }
        }

        return '';
    }

    private static function sendInvoiceEmail(string $email, Order $order, array $invoiceData, string $pdfPath): void
    {
        if ($email === '') {
            self::log('WARNING', ['order_id' => $order->getId(), 'message' => 'Client email not found']);
            return;
        }

        $fields = [
            'EMAIL' => $email,
            'ORDER_ID' => (string)$order->getField('ACCOUNT_NUMBER'),
            'ORDER_REAL_ID' => (string)$order->getId(),
            'INVOICE_NUMBER' => (string)$invoiceData['invoice_number'],
            'INVOICE_DATE' => (string)$invoiceData['invoice_date'],
            'INVOICE_TOTAL' => (string)$invoiceData['total'],
            'SITE_NAME' => (string)Application::getInstance()->getContext()->getServer()->getServerName(),
        ];

        $eventId = \CEvent::Send(
            self::MAIL_EVENT,
            $order->getSiteId(),
            $fields,
            'Y',
            '',
            [$pdfPath]
        );

        if (!$eventId) {
            Mail::send([
                'TO' => $email,
                'SUBJECT' => 'Счет на оплату по заказу ' . $order->getField('ACCOUNT_NUMBER'),
                'BODY' => 'Здравствуйте! Направляем счет на оплату. Номер счета: ' . $invoiceData['invoice_number'],
                'HEADER' => 'Content-Type: text/plain; charset=UTF-8',
            ]);
        }
    }

    private static function fromMinorUnit(float $value): float
    {
        return round($value / 100, 2);
    }

    private static function formatDate(string $date): string
    {
        if ($date === '') {
            return date('d.m.Y');
        }
        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return $date;
        }
        return date('d.m.Y', $timestamp);
    }

    private static function fail(string $message): array
    {
        self::log('ERROR', ['message' => $message]);
        return ['ok' => false, 'error' => $message];
    }

    private static function log(string $level, array $context = []): void
    {
        $path = $_SERVER['DOCUMENT_ROOT'] . self::LOG_FILE;
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $row = '[' . date('Y-m-d H:i:s') . '][' . $level . '] ' . self::toJson($context) . PHP_EOL;
        file_put_contents($path, $row, FILE_APPEND);
    }

    private static function toJson($data): string
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return $json === false ? 'json_encode_error' : $json;
    }
}
