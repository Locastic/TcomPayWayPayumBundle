<?php

namespace Locastic\TcomPayWayPayumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\Order as BasePayment;

/**
 * @ORM\Table
 * @ORM\Entity
 */
class Payment extends BasePayment
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var integer $id
     */
    protected $id;
}
