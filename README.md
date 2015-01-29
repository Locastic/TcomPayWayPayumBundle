TcomPayWayPayumBundle
=====================


## For Sylius integration

Add following code to your app/config.yml

services:
    payum.tcompayway.action.capture_payment:
        class: Locastic\TcomPaywayPayumBundle\Bridge\Sylius\CapturePaymentAction
        tags:
            - { name: payum.action, factory: tcompayway, prepend: true }
