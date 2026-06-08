<?php

namespace Ipol\Catapulto\Api\Entity\Response\Part\CompanyIcon;

use \Ipol\Catapulto\Api\Entity\AbstractCollection;
use Ipol\Catapulto\Api\Entity\Response\Part\CompanyIcon\Company;


class CompanyCollection extends AbstractCollection
{
    protected $Companies;

    public function __construct()
    {
        parent::__construct('Companies');
        $this->setChildClass(Company::class);
    }

    /**
     * @return Company
     */
    public function getFirst(){
        return parent::getFirst();
    }

    /**
     * @return Company
     */
    public function getNext(){
        return parent::getNext();
    }

}