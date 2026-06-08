<?php

namespace Ipol\Catapulto\Bitrix\Entity;

use Ipol\Catapulto\Others\Tag;

/**
 * Class Table
 * @package Ipol\Catapulto\Bitrix\Entity
 * Хтоническая штука для рисования таблиц в интерфейсе Битрикса. Использовать на свой страх и риск.
 */
class Table extends Tag
{
    public function __construct()
    {
        parent::__construct('table');

        $this->addClass('adm-list-table');

        $obHead = new Tag('thead');
        $obTr   = new Tag('tr');

        $obHead->getContent()->add($obTr->setClass('adm-list-table-header'));

        $obBody = new Tag('tbody');

        $this->getContent()->add($obHead)
                           ->add($obBody);


    }


    /**
     * @param Tag $obTag
     * @return $this
     * добавляет заголовок в тело таблицы
     */
    public function addHeaderCell($obTag)
    {
        $this->getHead()->getContent()->getFirst()->addTag(
            $obTag->addClass('adm-list-table-cell')
        );

        return $this;
    }

    /**
     * @param $text
     * @param $class
     * @return $this;
     */
    public function easyAddHeaderCell($text, $class = '')
    {
        $obTH = new Tag('td');
        $obTH->setText($text)
             ->setClass($class);

        $this->addHeaderCell($obTH);

        return $this;
    }

    /**
     * @param $obTag
     * @return $this
     * добавляет строку в тело таблицы
     */
    public function addBodyRow($obTag)
    {
        $this->getBody()->addTag($obTag);

        return $this;
    }

    public function getHead()
    {
        $this->getContent()->reset();

        /** @var Tag $obTag */
        while($obTag = $this->getContent()->getNext()){
            if($obTag->getName() == 'thead'){
                return $obTag;
            }
        }

        return false;
    }

    public function getBody()
    {
        $this->getContent()->reset();

        /** @var Tag $obTag */
        while($obTag = $this->getContent()->getNext()){
            if($obTag->getName() == 'tbody'){
                return $obTag;
            }
        }

        return false;
    }

    /**
     * @return Tag
     */
    public static function fastTD()
    {
        $obTD = new Tag('td');
        return $obTD;
    }
}