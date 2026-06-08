<?php

namespace Ipol\Catapulto\Api\Entity\Request;

class Rate extends AbstractRequest
{

    /** @var int */
    protected $sender_contact_id;

    /** @var int */
    protected $receiver_contact_id;

    /** @var int */
    protected $sender_locality_id;

    /** @var int */
    protected $receiver_locality_id;

    /**
     * @var array|null dadata selected variant
     */
    protected $dadata_selected_choice;

    /** @var array cargoes id's [int, int] */
    protected $cargoes;

    /** @var int */
    protected $pickup_days_shift;

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
     * @return Rate
     */
    public function setSenderContactId(int $sender_contact_id): Rate
    {
        $this->sender_contact_id = $sender_contact_id;
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
     * @return Rate
     */
    public function setReceiverContactId(int $receiver_contact_id): Rate
    {
        $this->receiver_contact_id = $receiver_contact_id;
        return $this;
    }

    /**
     * @return int
     */
    public function getSenderLocalityId(): int
    {
        return $this->sender_locality_id;
    }

    /**
     * @param int $sender_locality_id
     *
     * @return Rate
     */
    public function setSenderLocalityId(int $sender_locality_id): Rate
    {
        $this->sender_locality_id = $sender_locality_id;
        return $this;
    }

    /**
     * @return int
     */
    public function getReceiverLocalityId(): int
    {
        return $this->receiver_locality_id;
    }

    /**
     * @param int $receiver_locality_id
     *
     * @return Rate
     */
    public function setReceiverLocalityId(int $receiver_locality_id): Rate
    {
        $this->receiver_locality_id = $receiver_locality_id;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getDadataSelectedChoice(): ?array
    {
        return $this->dadata_selected_choice;
    }

    /**
     * @param array|null $dadata_selected_choice
     * @return Rate
     */
    public function setDadataSelectedChoice(?array $dadata_selected_choice): Rate
    {
        $this->dadata_selected_choice = $dadata_selected_choice;
        return $this;
    }

    /**
     * @return array
     */
    public function getCargoes(): array
    {
        return $this->cargoes;
    }

    /**
     * @param array $cargoes
     *
     * @return Rate
     */
    public function setCargoes(array $cargoes): Rate
    {
        $this->cargoes = $cargoes;
        return $this;
    }

    /**
     * @return int
     */
    public function getPickupDaysShift(): int
    {
        return $this->pickup_days_shift;
    }

    /**
     * @param int $pickup_days_shift
     * @return Rate
     */
    public function setPickupDaysShift(int $pickup_days_shift): Rate
    {
        $this->pickup_days_shift = $pickup_days_shift;
        return $this;
    }


}
