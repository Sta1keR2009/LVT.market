<?php

namespace Ipol\Catapulto\Api\Entity\Response\Part\CompanyIcon;

use Ipol\Catapulto\Api\Entity\AbstractEntity;
use Ipol\Catapulto\Api\Entity\Response\Part\AbstractResponsePart;

/**
 * Class Company
 * @package Ipol\Catapulto\Api\Entity\Response\Part\CompanyIcon
 */
class Company extends AbstractEntity
{
    use AbstractResponsePart;

    /** @var string */
    protected $operator_id;

    /** @var string */
    protected $icon;

    /** @var string */
    protected $small_icon;

    /** @var string */
    protected $png_icon;

    /** @var string */
    protected $operator_display;

    /**
     * @return string
     */
    public function getOperatorId(): string
    {
        return $this->operator_id;
    }

    /**
     * @param string $operator_id
     *
     * @return Company
     */
    public function setOperatorId(string $operator_id): Company
    {
        $this->operator_id = $operator_id;
        return $this;
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * @param string $icon
     *
     * @return Company
     */
    public function setIcon(string $icon): Company
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * @return string
     */
    public function getSmallIcon(): string
    {
        return $this->small_icon;
    }

    /**
     * @param string $small_icon
     *
     * @return Company
     */
    public function setSmallIcon(string $small_icon): Company
    {
        $this->small_icon = $small_icon;
        return $this;
    }

    /**
     * @return string
     */
    public function getPngIcon(): string
    {
        return $this->png_icon;
    }

    /**
     * @param string $png_icon
     *
     * @return Company
     */
    public function setPngIcon(string $png_icon): Company
    {
        $this->png_icon = $png_icon;
        return $this;
    }

    /**
     * @return string
     */
    public function getOperatorDisplay(): string
    {
        return $this->operator_display;
    }

    /**
     * @param string $operator_display
     *
     * @return Company
     */
    public function setOperatorDisplay(string $operator_display): Company
    {
        $this->operator_display = $operator_display;
        return $this;
    }



}