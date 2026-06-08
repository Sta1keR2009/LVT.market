<?php

namespace Ipol\Catapulto\Api\Entity\Response\Part\Terminal;

class Data extends \Ipol\Catapulto\Api\Entity\AbstractEntity
{

    /** @var string */
    protected $status;

    /** @var int */
    protected $count;

    /** @var int|null */
    protected $next;

    /** @var int|null */
    protected $previous;

    /** @var array */
    protected $data;

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return Data
     */
    public function setStatus(string $status): Data
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * @param int $count
     *
     * @return Data
     */
    public function setCount(int $count): Data
    {
        $this->count = $count;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getNext(): ?int
    {
        return $this->next;
    }

    /**
     * @param int|null $next
     *
     * @return Data
     */
    public function setNext(?int $next): Data
    {
        $this->next = $next;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getPrevious(): ?int
    {
        return $this->previous;
    }

    /**
     * @param int|null $previous
     *
     * @return Data
     */
    public function setPrevious(?int $previous): Data
    {
        $this->previous = $previous;
        return $this;
    }

    /**
     * @return array|TerminalCollection
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     *
     * @return Data
     */
    public function setData(array $data): Data
    {
        $collection = new TerminalCollection();
        $this->data = $collection->fillFromArray($data);
        return $this;
    }



}