<?php

namespace Ipol\Catapulto\Api\Entity\Response\Part\ShipmentId;

class Document extends \Ipol\Catapulto\Api\Entity\AbstractEntity
{

    /** @var string */
    protected $key;

    /** @var string */
    protected $title;

    /** @var string */
    protected $url;

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     *
     * @return Document
     */
    public function setKey(string $key): Document
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return Document
     */
    public function setTitle(string $title): Document
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     *
     * @return Document
     */
    public function setUrl(string $url): Document
    {
        $this->url = $url;
        return $this;
    }


}