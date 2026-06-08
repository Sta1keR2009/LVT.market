<?php

namespace Ipol\Catapulto\Api\Entity\Response;

use Ipol\Catapulto\Api\BadResponseException;
use Ipol\Catapulto\Api\Entity\Common\Part\Address;
use Ipol\Catapulto\Api\Entity\Response\Part\ShipmentId\DocumentsCollection;

/**
 * Class ShipmentId
 * @package Ipol\Catapulto\Api\Entity\Response
 */
class ShipmentId extends AbstractResponse
{

    /** @var string|null */
    protected $id;

    /** @var string|null */
    protected $number;

    /** @var string|null */
    protected $tracking_number;

    /** @var string|null */
    protected $key;

    /** @var string|null */
    protected $main_status;

    /** @var string|null */
    protected $main_status_display;

    /** @var string|null */
    protected $updated_at;

    /** @var string|null */
    protected $pickup_day;

    /** @var string|null */
    protected $delivery_day;

    /** @var string|null */
    protected $price;

    /** @var string|null */
    protected $weight;

    /** @var string|null */
    protected $operator;

    /** @var string|null */
    protected $sender_company;

    /** @var string|null */
    protected $receiver_locality;

    /** @var string|null */
    protected $receiver_name;

    /** @var string|null */
    protected $receiver_company;

    /** @var string|null */
    protected $receiver_phone;

    /** @var Address */
    protected $receiver_address;

    /** @var string|null */
    protected $description;

    /** @var bool|null */
    protected $with_insurance;

    /** @var DocumentsCollection|null */
    protected $documents;

    /** @var string|null */
    protected $last_tracking_text;

    /** @var string|null */
    protected $owner_email;

    /** @var string|null */
    protected $problem_text;

    /** @var bool|null */
    protected $is_pod;

    /** @var string|null */
    protected $tracking_status;

    /** @var string|null */
    protected $tracking_link;

    /** @var string|array|null */
    protected $pod;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     *
     * @return ShipmentId
     */
    public function setId(?string $id): ShipmentId
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNumber(): ?string
    {
        return $this->number;
    }

    /**
     * @param string|null $number
     *
     * @return ShipmentId
     */
    public function setNumber(?string $number): ShipmentId
    {
        $this->number = $number;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTrackingNumber(): ?string
    {
        return $this->tracking_number;
    }

    /**
     * @param string|null $tracking_number
     *
     * @return ShipmentId
     */
    public function setTrackingNumber(?string $tracking_number): ShipmentId
    {
        $this->tracking_number = $tracking_number;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getKey(): ?string
    {
        return $this->key;
    }

    /**
     * @param string|null $key
     *
     * @return ShipmentId
     */
    public function setKey(?string $key): ShipmentId
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMainStatus(): ?string
    {
        return $this->main_status;
    }

    /**
     * @param string|null $main_status
     *
     * @return ShipmentId
     */
    public function setMainStatus(?string $main_status): ShipmentId
    {
        $this->main_status = $main_status;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMainStatusDisplay(): ?string
    {
        return $this->main_status_display;
    }

    /**
     * @param string|null $main_status_display
     *
     * @return ShipmentId
     */
    public function setMainStatusDisplay(?string $main_status_display): ShipmentId
    {
        $this->main_status_display = $main_status_display;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUpdatedAt(): ?string
    {
        return $this->updated_at;
    }

    /**
     * @param string|null $updated_at
     *
     * @return ShipmentId
     */
    public function setUpdatedAt(?string $updated_at): ShipmentId
    {
        $this->updated_at = $updated_at;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPickupDay(): ?string
    {
        return $this->pickup_day;
    }

    /**
     * @param string|null $pickup_day
     *
     * @return ShipmentId
     */
    public function setPickupDay(?string $pickup_day): ShipmentId
    {
        $this->pickup_day = $pickup_day;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDeliveryDay(): ?string
    {
        return $this->delivery_day;
    }

    /**
     * @param string|null $delivery_day
     *
     * @return ShipmentId
     */
    public function setDeliveryDay(?string $delivery_day): ShipmentId
    {
        $this->delivery_day = $delivery_day;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPrice(): ?string
    {
        return $this->price;
    }

    /**
     * @param string|null $price
     *
     * @return ShipmentId
     */
    public function setPrice(?string $price): ShipmentId
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getWeight(): ?string
    {
        return $this->weight;
    }

    /**
     * @param string|null $weight
     *
     * @return ShipmentId
     */
    public function setWeight(?string $weight): ShipmentId
    {
        $this->weight = $weight;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getOperator(): ?string
    {
        return $this->operator;
    }

    /**
     * @param string|null $operator
     *
     * @return ShipmentId
     */
    public function setOperator(?string $operator): ShipmentId
    {
        $this->operator = $operator;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSenderCompany(): ?string
    {
        return $this->sender_company;
    }

    /**
     * @param string|null $sender_company
     *
     * @return ShipmentId
     */
    public function setSenderCompany(?string $sender_company): ShipmentId
    {
        $this->sender_company = $sender_company;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReceiverLocality(): ?string
    {
        return $this->receiver_locality;
    }

    /**
     * @param string|null $receiver_locality
     *
     * @return ShipmentId
     */
    public function setReceiverLocality(?string $receiver_locality): ShipmentId
    {
        $this->receiver_locality = $receiver_locality;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReceiverName(): ?string
    {
        return $this->receiver_name;
    }

    /**
     * @param string|null $receiver_name
     *
     * @return ShipmentId
     */
    public function setReceiverName(?string $receiver_name): ShipmentId
    {
        $this->receiver_name = $receiver_name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReceiverCompany(): ?string
    {
        return $this->receiver_company;
    }

    /**
     * @param string|null $receiver_company
     *
     * @return ShipmentId
     */
    public function setReceiverCompany(?string $receiver_company): ShipmentId
    {
        $this->receiver_company = $receiver_company;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReceiverPhone(): ?string
    {
        return $this->receiver_phone;
    }

    /**
     * @param string|null $receiver_phone
     *
     * @return ShipmentId
     */
    public function setReceiverPhone(?string $receiver_phone): ShipmentId
    {
        $this->receiver_phone = $receiver_phone;
        return $this;
    }

    /**
     * @return Address
     */
    public function getReceiverAddress(): Address
    {
        return $this->receiver_address;
    }

    /**
     * @param array $receiver_address
     *
     * @return ShipmentId
     */
    public function setReceiverAddress(array $receiver_address): ShipmentId
    {
        $address = new Address();
        $this->receiver_address = $address->setFields($receiver_address);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     *
     * @return ShipmentId
     */
    public function setDescription(?string $description): ShipmentId
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getWithInsurance(): ?bool
    {
        return $this->with_insurance;
    }

    /**
     * @param bool|null $with_insurance
     *
     * @return ShipmentId
     */
    public function setWithInsurance(?bool $with_insurance): ShipmentId
    {
        $this->with_insurance = $with_insurance;
        return $this;
    }

    /**
     * @return DocumentsCollection|null
     */
    public function getDocuments(): ?DocumentsCollection
    {
        return $this->documents;
    }

    /**
     * @param array|null $documents
     *
     * @return ShipmentId
     */
    public function setDocuments(?array $documents): ShipmentId
    {
        $collection = new DocumentsCollection();
        $this->documents = $collection->fillFromArray($documents);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLastTrackingText(): ?string
    {
        return $this->last_tracking_text;
    }

    /**
     * @param string|null $last_tracking_text
     *
     * @return ShipmentId
     */
    public function setLastTrackingText(?string $last_tracking_text): ShipmentId
    {
        $this->last_tracking_text = $last_tracking_text;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getOwnerEmail(): ?string
    {
        return $this->owner_email;
    }

    /**
     * @param string|null $owner_email
     *
     * @return ShipmentId
     */
    public function setOwnerEmail(?string $owner_email): ShipmentId
    {
        $this->owner_email = $owner_email;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getProblemText(): ?string
    {
        return $this->problem_text;
    }

    /**
     * @param string|null $problem_text
     *
     * @return ShipmentId
     */
    public function setProblemText(?string $problem_text): ShipmentId
    {
        $this->problem_text = $problem_text;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIsPod(): ?bool
    {
        return $this->is_pod;
    }

    /**
     * @param bool|null $is_pod
     *
     * @return ShipmentId
     */
    public function setIsPod(?bool $is_pod): ShipmentId
    {
        $this->is_pod = $is_pod;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTrackingStatus(): ?string
    {
        return $this->tracking_status;
    }

    /**
     * @param string|null $tracking_status
     *
     * @return ShipmentId
     */
    public function setTrackingStatus(?string $tracking_status): ShipmentId
    {
        $this->tracking_status = $tracking_status;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTrackingLink(): ?string
    {
        return $this->tracking_link;
    }

    /**
     * @param string|null $tracking_link
     * @return ShipmentId
     */
    public function setTrackingLink(?string $tracking_link): ShipmentId
    {
        $this->tracking_link = $tracking_link;
        return $this;
    }

    /**
     * @return string|array|null
     */
    public function getPod()
    {
        return $this->pod;
    }

    /**
     * @param string|array|null $pod
     *
     * @return ShipmentId
     */
    public function setPod($pod): ShipmentId
    {
        $this->pod = $pod;
        return $this;
    }

}
