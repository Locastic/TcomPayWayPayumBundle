TcomPayWayPayumBundle
=====================

## Prerequisites

For TcomPayPayumBundle to work, you have to install Payum. You can read more about Payum at their official website
[payum](http://payum.org/)

On PayumBundle's [get_it_started](https://github.com/Payum/PayumBundle/blob/master/Resources/doc/get_it_started.md)
you can find more about creating security token and payment details.

## Installation

For installation of TcomPaywayPayumBundle just include it in your composer.json file and run Composer's update
command.

Afterwards, register the new bundle in your AppKernel.

    // app/AppKernel.php
    public function registerBundles()
    {
        $bundles = array(
            ...
            new \Locastic\TcomPaywayPayumBundle\LocasticTcomPaywayPayumBundle(),
            ...
        );


Next step is to fill your shop details. Parameters secure3d_template, prepare_template & done_template are exposed
so you can replace them with your own templates.

    # app/config/config.yml
    payum:
        contexts:
            tcompayway:
                tcompayway:
                    preauth_required: 1 # Default is 1 which means preauthorization required
                    secure3d_template: LocasticWebBundle:Frontend/Payum:secure3d.html.twig
                    prepare_template: LocasticWebBundle:Frontend/Payum:prepare.html.twig
                    done_template: LocasticWebBundle:Frontend/Payum:done.html.twig
                    actions:
                        - locastic.tcompayway_payum.action.capture
                        - locastic.tcompayway_payum.action.status


    # app/config/config.yml
    twig:
        paths:
            %kernel.root_dir%/../vendor/payum/core/Payum/Core/Resources/views: PayumCore

## Configuration

Now you need to configure capture_payment service, add tcompayway gateway to sylius_payments and add parameters specific for your webshop.

    # app/config/config.yml
    services:
        payum.tcompayway.action.capture_payment:
            class: Locastic\TcomPaywayPayumBundle\Bridge\Sylius\CapturePaymentAction
            arguments: ["%tcompayway.shop_name%"]
            tags:
                - { name: payum.action, factory: tcompayway, prepend: true }


    # Sylius/Bundle/CoreBundle/Resources/config/app/config.yml
    sylius_payment:
       gateways:
           dummy: Defaultni gateway (dummy)
           tcompayway: T-com PayWay


    # app/config/parameters.yml
    tcompayway.shop_id : 'YOUR_ID'
    tcompayway.shop_username: 'YOUR_USERNAME'
    tcompayway.shop_password: 'YOUR_PASSWORD'
    tcompayway.shop_secret_key: 'YOUR_SECRET'
    tcompayway.shop_name: 'YOUR_SHOP_NAME' # this will be used in transaction description in user's bank account transactions

You also need to configure payment methods in Sylius administration to use T-com PayWay gateway.

## To do:
- decouple labels and add support for translations
- add configuration for installments 
- add javascript validation and automatic pick of credit card by card number
- set custom number of installments