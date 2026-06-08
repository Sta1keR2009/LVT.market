<?php

namespace Ipol\Catapulto\Api\Entity\Response\Part\Terminal;

class TerminalCollection extends \Ipol\Catapulto\Api\Entity\AbstractCollection
{
    protected $TerminalItems;

    public function __construct()
    {
        parent::__construct('TerminalItems');
        $this->setChildClass(Terminal::class);
    }

    /**
     * @return Terminal
     */
    public function getFirst(){
        return parent::getFirst();
    }

    /**
     * @return Terminal
     */
    public function getNext(){
        return parent::getNext();
    }


}