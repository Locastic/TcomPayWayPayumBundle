<?php

namespace Locastic\TcomPayWayPayumBundle\Request;

use Payum\Core\Request\ObtainCreditCard as BaseObtainCreditCard;
use Payum\Core\Bridge\Spl\ArrayObject;

class ObtainCreditCard extends BaseObtainCreditCard
{
    /**
     * @var ArrayObject
     */
    protected $model;

    /**
     * @param ArrayObject $model
     */
    public function setModel(ArrayObject $model)
    {
        $this->model = $model;
    }

    /**
     * @return ArrayObject
     */
    public function getModel()
    {
        return $this->model;
    }
}
