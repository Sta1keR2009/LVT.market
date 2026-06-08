<?php

namespace Ipol\Catapulto\Api\Entity\Request;

class Shipment extends AbstractRequest
{

    /** @var int */
    protected $sender_contact_id;

    /** @var string|null */
    protected $sender_terminal_code;

    /** @var int */
    protected $receiver_contact_id;

    /** @var string|null */
    protected $receiver_terminal_code;

    /** @var int */
    protected $rate_result_id;

    /** @var string */
    protected $webshop_order_number_id;

    /** @var string */
    protected $pickup_day;

    /** @var string */
    protected $delivery_day;

    /** @var bool */
    protected $need_insurance = false;

    /** @var int */
    protected $insurance_value = 0;

    /**
     * @var array Additional services
     * Enum [cod_amount, byhand_amount, inventory_amount, returndoc_amount, hazardous_amount, pod_amount]
     */
    protected $additional_services = [];

    /**
     * @var bool
     */
    protected $generate_sender_poa = false;

    /**
     * @var string|null
     */
    protected $from_name;
    /**
     * @var string|null
     */
    protected $from_passport_series;
    /**
     * @var string|null
     */
    protected $from_passport_number;
    /**
     * @var string|null
     */
    protected $from_passport_date;
    /**
     * @var string|null
     */
    protected $send_receiver_poa_email;

    /**
     * @return int
     */
    public function getSenderContactId(): int
    {
        return $this->sender_contact_id;
    }

    /**
     * @param int $sender_contact_id
     *
     * @return Shipment
     */
    public function setSenderContactId(int $sender_contact_id): Shipment
    {
        $this->sender_contact_id = $sender_contact_id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSenderTerminalCode(): ?string
    {
        return $this->sender_terminal_code;
    }

    /**
     * @param string|null $sender_terminal_code
     *
     * @return Shipment
     */
    public function setSenderTerminalCode(?string $sender_terminal_code): Shipment
    {
        $this->sender_terminal_code = $sender_terminal_code;
        return $this;
    }



    /**
     * @return int
     */
    public function getReceiverContactId(): int
    {
        return $this->receiver_contact_id;
    }

    /**
     * @param int $receiver_contact_id
     *
     * @return Shipment
     */
    public function setReceiverContactId(int $receiver_contact_id): Shipment
    {
        $this->receiver_contact_id = $receiver_contact_id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReceiverTerminalCode(): ?string
    {
        return $this->receiver_terminal_code;
    }

    /**
     * @param string|null $receiver_terminal_code
     *
     * @return Shipment
     */
    public function setReceiverTerminalCode(?string $receiver_terminal_code): Shipment
    {
        $this->receiver_terminal_code = $receiver_terminal_code;
        return $this;
    }


    /**
     * @return int
     */
    public function getRateResultId(): int
    {
        return $this->rate_result_id;
    }

    /**
     * @param int $rate_result_id
     *
     * @return Shipment
     */
    public function setRateResultId(int $rate_result_id): Shipment
    {
        $this->rate_result_id = $rate_result_id;
        return $this;
    }

    /**
     * @return string
     */
    public function getWebshopOrderNumberId(): string
    {
        return $this->webshop_order_number_id;
    }

    /**
     * @param string $webshop_order_number_id
     *
     * @return Shipment
     */
    public function setWebshopOrderNumberId(string $webshop_order_number_id): Shipment
    {
        $this->webshop_order_number_id = $webshop_order_number_id;
        return $this;
    }

    /**
     * @return string
     */
    public function getPickupDay(): string
    {
        return $this->pickup_day;
    }

    /**
     * @param string $pickup_day
     *
     * @return Shipment
     */
    public function setPickupDay(string $pickup_day): Shipment
    {
        $this->pickup_day = $pickup_day;
        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryDay(): string
    {
        return $this->delivery_day;
    }

    /**
     * @param string $delivery_day
     *
     * @return Shipment
     */
    public function setDeliveryDay(string $delivery_day): Shipment
    {
        $this->delivery_day = $delivery_day;
        return $this;
    }

    /**
     * @return bool
     */
    public function isNeedInsurance(): bool
    {
        return $this->need_insurance;
    }

    /**
     * @param bool $need_insurance
     *
     * @return Shipment
     */
    public function setNeedInsurance(bool $need_insurance): Shipment
    {
        $this->need_insurance = $need_insurance;
        return $this;
    }

    /**
     * @return int
     */
    public function getInsuranceValue(): int
    {
        return $this->insurance_value;
    }

    /**
     * @param int $insurance_value
     *
     * @return Shipment
     */
    public function setInsuranceValue(int $insurance_value): Shipment
    {
        $this->insurance_value = $insurance_value;
        return $this;
    }

    /**
     * @return array
     */
    public function getAdditionalServices(): array
    {
        return $this->additional_services;
    }

    /**
     * @param array $additional_services
     *
     * @return Shipment
     */
    public function setAdditionalServices(array $additional_services): Shipment
    {
        $this->additional_services = $additional_services;
        return $this;
    }

    /**
     * @return bool
     */
    public function isGenerateSenderPoa(): bool
    {
        return $this->generate_sender_poa;
    }

    /**
     * @param bool $generate_sender_poa
     * @return Shipment
     */
    public function setGenerateSenderPoa(bool $generate_sender_poa): Shipment
    {
        $this->generate_sender_poa = $generate_sender_poa;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFromName(): ?string
    {
        return $this->from_name;
    }

    /**
     * @param string|null $from_name
     * @return Shipment
     */
    public function setFromName(?string $from_name): Shipment
    {
        $this->from_name = $from_name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFromPassportSeries(): ?string
    {
        return $this->from_passport_series;
    }

    /**
     * @param string|null $from_passport_series
     * @return Shipment
     */
    public function setFromPassportSeries(?string $from_passport_series): Shipment
    {
        $this->from_passport_series = $from_passport_series;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFromPassportNumber(): ?string
    {
        return $this->from_passport_number;
    }

    /**
     * @param string|null $from_passport_number
     * @return Shipment
     */
    public function setFromPassportNumber(?string $from_passport_number): Shipment
    {
        $this->from_passport_number = $from_passport_number;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFromPassportDate(): ?string
    {
        return $this->from_passport_date;
    }

    /**
     * @param string|null $from_passport_date
     * @return Shipment
     */
    public function setFromPassportDate(?string $from_passport_date): Shipment
    {
        $this->from_passport_date = $from_passport_date;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSendReceiverPoaEmail(): ?string
    {
        return $this->send_receiver_poa_email;
    }

    /**
     * @param string|null $send_receiver_poa_email
     * @return Shipment
     */
    public function setSendReceiverPoaEmail(?string $send_receiver_poa_email): Shipment
    {
        $this->send_receiver_poa_email = $send_receiver_poa_email;
        return $this;
    }


}