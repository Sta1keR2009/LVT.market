<?php


namespace Ipol\Catapulto\Core\Delivery;


use Exception;
use Ipol\Catapulto\Core\Entity\Collection;
use Ipol\Catapulto\Core\Entity\FieldsContainer;
use Ipol\Catapulto\Core\Entity\Money;
use Ipol\Catapulto\Core\Entity\Packing\MebiysDimMerger;

/**
 * Class Cargo
 * @package Ipol\Catapulto\Core
 * @subpackage Delivery
 * Cargo description, consist of basic goods
 * @method false|CargoItem getFirst
 * @method false|CargoItem getNext
 * @method false|CargoItem getLast
 */
class Cargo extends Collection
{
    use FieldsContainer;

    /**
     * @var string
     */
    protected $id = '';

    /**
     * @var string
     */
    protected $ord = '';

    /**
     * @var string|null
     */
    protected $name = '';

    /**
     * @var int
     */
    protected $cargoId = 0;

    /**
     * @var int - mm
     */
    protected $length = 0;

    /**
     * @var int - mm
     */
    protected $width = 0;

    /**
     * @var int - mm
     */
    protected $height = 0;

    /**
     * @var int - gram
     */
    protected $weight = 0;

    /**
     * @var array
     */
    protected $Items;
    /**
     * @var MebiysDimMerger|mixed
     */
    protected $packer;

    /**
     * Cargo constructor.
     * @param false $packer
     */
    public function __construct($packer = false)
    {
        parent::__construct('Items');
        $this->packer = $packer? new $packer : new MebiysDimMerger();
    }

    /**
     * @param bool $setCalculatedToo Do set calculated result in Cargo object or not
     * @return MergerResult
     */
    public function calculateDimensions($setCalculatedToo = false)
    {
        $arGabs = array();

        $this->reset();
        while ($item = $this->getNext()) {
            $arGabs[] = array($item->getLength(), $item->getWidth(), $item->getHeight(), ceil($item->getQuantity()));
        }

        $packer = $this->packer;
        $mergerResult = $packer::getSumDimensions($arGabs);

        if ($setCalculatedToo) {
            $this->length = (int)$mergerResult['L'];
            $this->width  = (int)$mergerResult['W'];
            $this->height = (int)$mergerResult['H'];
        }

        return $mergerResult;
    }

    /**
     * @param CargoItem $item
     * @return $this
     * @throws Exception
     */
    public function add($item)
    {
        //if($item->ready()) {
            parent::add($item);
        /*}else
            throw new Exception('CargoItem is not ready in '.get_class());*/

        return $this;
    }

    /**
     * @return array (L, W, H)
     */
    public function getDimensions()
    {
        $arGabs = array();

        $this->reset();
        while($obItem = $this->getNext())
        {
            $arGabs[] = array($obItem->getLength(), $obItem->getWidth(), $obItem->getHeight(), ceil($obItem->getQuantity()));
        }

        return $this->packer::getSumDimensions($arGabs);
    }

    /**
     * @return float
     */
    public function getVolume()
    {
        $volume = 0;

        $this->reset();
        while($obItem = $this->getNext())
        {
            $volume += $obItem->giveVolume() * $obItem->getQuantity();
        }

        return $volume;
    }

    /**
     * @return int
     */
    public function getWeight()
    {
        if ($this->weight > 0) return $this->weight;
        $weight = 0;

        $this->reset();
        while($obItem = $this->getNext()) {
            $weight += $obItem->getWeight() * $obItem->getQuantity();
        }

        return $weight;
    }

    /**
     * @return array
     */
    public function getGabs()
    {
        return array('W'=>$this->getWeight(), 'V'=>$this->getVolume(), 'G'=>$this->getDimensions());
    }

    /**
     * @return Money total price to be payed for items
     */
    public function getTotalPrice()
    {
        $price = new Money(0);

        $this->reset();
        while($obItem = $this->getNext())
        {
            if($obItem->getPrice())
                $price = Money::sum($price, Money::multiply($obItem->getPrice(), $obItem->getQuantity()));
        }

        return $price;
    }

    /**
     * @return Money total estimated cost for insurance
     */
    public function getTotalCost()
    {
        $cost = new Money(0);

        $this->reset();
        while($obItem = $this->getNext())
        {
            if($obItem->getCost())
                $cost = Money::sum($cost, Money::multiply($obItem->getCost(), $obItem->getQuantity()));
        }

        return $cost;
    }

    /**
     * @return bool
     */
    public function checkOverSize()
    {
        $this->reset();
        while($obItem = $this->getNext())
        {
            if($obItem->getOverSize())
                return true;
        }

        return false;
    }

    /**
     * Makes Cargo from associative array
     * @param array $data
     * @return $this
     */
    public function fromArray($data)
    {
        $this
            ->setName(isset($data['name']) ? $data['name'] : null)
            ->setLength((int)$data['length'])
            ->setWidth((int)$data['width'])
            ->setHeight((int)$data['height'])
            ->setWeight((int)$data['weight']);

        if (!empty($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $cargoItem = new CargoItem();
                $this->add($cargoItem->fromArray($item));
            }
        }

        if (!empty($data['fields']) && is_array($data['fields'])) {
            $this->setFields($data['fields']);
        }

        return $this;
    }

    /**
     * Returns Cargo data as associative array
     * @return array
     */
    public function toArray()
    {
        $data = [
            'id'          => $this->getId(),
            'name'        => $this->getName(),
            'ccargo_id'   => $this->getCargoId(),
            'ord'         => $this->getOrd(),
            'length'      => $this->getLength(),
            'width'       => $this->getWidth(),
            'height'      => $this->getHeight(),
            'weight'      => $this->getWeight(),
            'descr'       => $this->getCargoComment(),
            'items'       => [],
            'fields'      => null,
        ];

        $this->reset();
        while ($item = $this->getNext()) {
            $data['items'][] = $item->toArray();
        }

        if (!empty($this->getContainer())) {
            foreach($this->getContainer() as $key => $val) {
                $data['fields'][$key] = $val;
            }
        }

        return $data;
    }

    // Only getters and setters below this line

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param int $length
     * @return $this
     */
    public function setLength($length)
    {
        $this->length = $length;

        return $this;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param int $width
     * @return $this
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param int $height
     * @return $this
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * @return int
     */
    /*public function getWeight()
    {
        return $this->weight;
    }*/

    /**
     * @param int $weight
     * @return $this
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): Cargo
    {
        $this->id = $id;
        return $this;
    }

    public function getOrd(): string
    {
        return $this->ord;
    }

    public function setOrd(string $ord): Cargo
    {
        $this->ord = $ord;
        return $this;
    }

    public function getCargoId(): int
    {
        return $this->cargoId;
    }

    public function setCargoId(int $cargoId): Cargo
    {
        $this->cargoId = $cargoId;
        return $this;
    }

    public function isSingleProduct(): bool
    {
        if (count($this->Items) > 1) return false;
        $isSingle = true;
        $this->reset();
        while ($item = $this->getNext()) {
            if ($item->getQuantity() > 1) $isSingle = false;
        }
        return $isSingle;
    }

    public function getCargoComment(): string
    {
        $cargoComment = '';
        $this->reset();
        while ($item = $this->getNext()) {
            if (!empty($cargoComment)) $cargoComment .= ';';
            $cargoComment .= $item->getName().'('.$item->getQuantity().')';
        }
        return $cargoComment;
    }



}
