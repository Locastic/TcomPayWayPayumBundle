TcomPayWayPayumBundle
=====================

## Prerequisites

For TcomPayPayumBundle to work, you have to install Payum. You can read more about Payum at their official website
[payum](http://payum.org/)

On PayumBundle's [get_it_started](https://github.com/Payum/PayumBundle/blob/master/Resources/doc/get_it_started.md)
you can find more about creating security token and payment details.

## Installation

For installation of TcomPayWayPayumBundle just include it in your composer.json file and run Composer's update
command.

Afterwards, register the new bundle in your AppKernel.

```php
<?php
// app/AppKernel.php
public function registerBundles()
{
    $bundles = [
        // ...
        new \Locastic\TcomPayWayPayumBundle\LocasticTcomPayWayPayumBundle(),
    ];
}
````


Next step is to fill your shop details. Parameters secure3d_template, prepare_template & done_template are exposed
so you can replace them with your own templates.

```yaml
# app/config/config.yml
payum:
    gateways:
        tcompayway:
            factory: tcompayway_direct # or tcompayway_direct
            shop_id: EDITME
            shop_username: EDITME
            shop_password: EDITME
            shop_name: EDITME
            shop_secret_key: EDITME
            secret_key: EDITME
            authorization_type: EDITME
            sandbox: true
            disable_installments: EDITME
            
        # or
        tcompayway_offsite:
            factory: tcompayway_offsite
            shop_id: EDITME
            secret_key: EDITME
            authorization_type: EDITME
            sandbox: true
            disable_installments: EDITME
```

## Sylius Configuration

Now you need to configure capture_payment service, add tcompayway gateway to sylius_payments.

```yaml
# app/config/config.yml
services:
    payum.tcompayway.action.convert_payment_to_tcompayway:
        class: Locastic\TcomPayWayPayumBundle\Bridge\Sylius\ConvertPaymentToTcomPayWayAction
        tags:
            - { name: payum.action, factory: tcompayway_direct, prepend: true }
            - { name: payum.action, factory: tcompayway_offsite, prepend: true }

# Sylius/Bundle/CoreBundle/Resources/config/app/config.yml
sylius_payment:
   gateways:
       dummy: Defaultni gateway (dummy)
       tcompayway: T-com PayWay
```

You also need to configure payment methods in Sylius administration to use T-com PayWay gateway.

## To do:
- decouple labels and add support for translations
- add configuration for installments 
- add javascript validation and automatic pick of credit card by card number
- set custom number of installments
