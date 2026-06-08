<?php


namespace Ipol\Catapulto\Catapulto;


use Exception;
use Ipol\Catapulto\Core\Entity\Collection;

class ExceptionCollection extends Collection
{
    public function __construct()
    {
        parent::__construct('errors');
    }

    public function getAllMessages(): string
    {
        $this->reset();
        if ($current = $this->getNext()) {
            /**@var $current Exception*/
            $strReturn = $current->getMessage();
        } else {
            return '';
        }

        while ($current = $this->getNext()) {
            /**@var $current Exception*/
            $strReturn .= ', ' . $current->getMessage();
        }

        return $strReturn;
    }
}