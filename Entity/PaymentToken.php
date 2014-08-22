<?php
namespace Locastic\TcomPaywayPayumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\Token;

/**
 * @ORM\Table(name="payum_payment_token")
 * @ORM\Entity
 */
class PaymentToken extends Token
{
}