<?php

namespace Ipol\Catapulto\Api\Entity\Request;

class Terminal extends AbstractRequest
{

    /** @var int */
    protected $sender_contact_id;

    /** @var int */
    protected $receiver_contact_id;

    /** @var int */
    protected $sender_locality_id;

    /** @var int */
    protected $receiver_locality_id;

    /** @var string|null */
    protected $company;

    /** @var int */
    protected $limit = 100;

    /** @var int */
    protected $page = 1;

    /** @var bool */
    protected $is_sender_shown = false;

    /**
     * @var string
     */
    protected $services_filter;

    /**
     * @var array|null
     */
    protected $cargoes;

    /**
     * @var float|null
     */
    protected $lat;
    /**
     * @var float|null
     */
    protected $lon;

    /**
     * @var float|null
     */
    protected $radius_km;

    /**
     * @var string|null
     */
    protected $iso;

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
     * @return Terminal
     */
    public function setSenderContactId(int $sender_contact_id): Terminal
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
     * @return Terminal
     */
    public function setReceiverContactId(int $receiver_contact_id): Terminal
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
     * @return Terminal
     */
    public function setSenderLocalityId(int $sender_locality_id): Terminal
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
     * @return Terminal
     */
    public function setReceiverLocalityId(int $receiver_locality_id): Terminal
    {
        $this->receiver_locality_id = $receiver_locality_id;
        return $this;
    }


    /**
     * @return string|null
     */
    public function getCompany(): ?string
    {
        return $this->company;
    }

    /**
     * @param string|null $company
     *
     * @return Terminal
     */
    public function setCompany(?string $company): Terminal
    {
        $this->company = $company;
        return $this;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     *
     * @return Terminal
     */
    public function setLimit(int $limit): Terminal
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param int $page
     *
     * @return Terminal
     */
    public function setPage(int $page): Terminal
    {
        $this->page = $page;
        return $this;
    }

    /**
     * @return bool
     */
    public function isIsSenderShown(): bool
    {
        return $this->is_sender_shown;
    }

    /**
     * @param bool $is_sender_shown
     *
     * @return Terminal
     */
    public function setIsSenderShown(bool $is_sender_shown): Terminal
    {
        $this->is_sender_shown = $is_sender_shown;
        return $this;
    }

    /**
     * @return string
     */
    public function getServices_filter(): string
    {
        return $this->services_filter;
    }

    /**
     * @param string $services_filter
     * @return Terminal
     */
    public function setServices_filter(string $services_filter): Terminal
    {
        $this->services_filter = $services_filter;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getCargoes(): ?array
    {
        return $this->cargoes;
    }

    /**
     * @param array|null $cargoes
     * @return Terminal
     */
    public function setCargoes(?array $cargoes): Terminal
    {
        $this->cargoes = $cargoes;
        return $this;
    }

    public function getLat(): ?float
    {
        return $this->lat;
    }

    public function setLat(?float $lat): Terminal
    {
        $this->lat = $lat;
        return $this;
    }

    public function getLon(): ?float
    {
        return $this->lon;
    }

    public function setLon(?float $lon): Terminal
    {
        $this->lon = $lon;
        return $this;
    }

    public function getRadius_km(): ?float
    {
        return $this->radius_km;
    }

    public function setRadius_km(?float $radius_km): Terminal
    {
        $this->radius_km = $radius_km;
        return $this;
    }

    public function getIso(): ?string
    {
        return $this->iso;
    }

    public function setIso(?string $iso): Terminal
    {
        $this->iso = $iso;
        return $this;
    }

}
