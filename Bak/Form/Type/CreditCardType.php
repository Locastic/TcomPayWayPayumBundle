<?php

namespace Locastic\TcomPaywayPayumBundle\Form\Type;

use Payum\Core\Bridge\Symfony\Form\Type\CreditCardType as CreditCardTypeBase;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CreditCardType extends CreditCardTypeBase
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('holderSurname', 'text', array('label' => 'form.credit_card.holder_surname'))
            ->add('securityCode', 'text', array(
                'label' => 'form.credit_card.security_code',
                'required' => false,
            ))
            ->add('email', 'email', array('label' => 'form.credit_card.email'))
            ->add('address', 'text', array('label' => 'form.credit_card.address'))
            ->add('city', 'text', array('label' => 'form.credit_card.city'))
            ->add('zipCode', 'text', array('label' => 'form.credit_card.zip_code'))
            ->add('country', 'country', array('label' => 'form.credit_card.country'))
            ->add('phoneNumber', 'text', array('required' => false, 'label' => 'form.credit_card.phone_number'));
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver
            ->setDefaults(
                array(
                    'data_class' => 'Locastic\TcomPaywayPayumBundle\Entity\CreditCard',
                    'validation_groups' => array('Locastic'),
                )
            );
    }

    public function getName()
    {
        return 'payum_credit_card';
    }
}