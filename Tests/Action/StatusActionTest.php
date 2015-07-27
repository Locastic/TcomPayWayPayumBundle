<?php
namespace Locastic\TcomPayWayPayumBundle\Tests\Action;

use Locastic\TcomPayWay\AuthorizeDirect\Model\Payment as OnsiteApi;
use Locastic\TcomPayWay\AuthorizeForm\Model\Payment as OffsiteApi;
use Payum\Core\Request\GetHumanStatus;
use Locastic\TcomPayWayPayumBundle\Action\StatusAction;
use Payum\Core\Tests\GenericActionTest;

class StatusActionTest extends GenericActionTest
{
    protected $actionClass = 'Locastic\TcomPayWayPayumBundle\Action\StatusAction';

    protected $requestClass = 'Payum\Core\Request\GetHumanStatus';

    /**
     * @test
     */
    public function shouldMarkNewIfDetailsEmpty()
    {
        $action = new StatusAction();

        $action->execute($status = new GetHumanStatus(array()));

        $this->assertTrue($status->isNew());
    }

    /**
     * @test
     */
    public function shouldMarkNewIfTcomPayWayResponseNotSet()
    {
        $action = new StatusAction();

        $action->execute($status = new GetHumanStatus(array()));

        $this->assertTrue($status->isNew());
    }

    /**
     * @test
     */
    public function shouldMarkOnsiteAuthorizedIfTcomPayWayResponseCodeIsZeroAndAuthorizationTypeIsZero()
    {
        $action = new StatusAction();
        $action->setApi($this->getOnsiteApi());

        $action->execute(
            $status = new GetHumanStatus(
                array(
                    'tcompayway_response' => array('pgw_result_code' => 0),
                )
            )
        );

        $this->assertTrue($status->isAuthorized());
    }

    /**
     * @test
     */
    public function shouldMarkOnsiteCapturedIfTcomPayWayResponseCodeIsZeroAndAuthorizationTypeIsOne()
    {
        $action = new StatusAction();
        $api = $this->getOnsiteApi();
        $api->setPgwAuthorizationType(1);
        $action->setApi($api);

        $action->execute(
            $status = new GetHumanStatus(
                array(
                    'tcompayway_response' => array('pgw_result_code' => 0),
                )
            )
        );

        $this->assertTrue($status->isCaptured());
    }

    /**
     * @test
     */
    public function shouldMarkOnsiteFailedIfTcomPayWayResponseCodeIsMoreThenZeroAndAuthorizationTypeIsZero()
    {
        $action = new StatusAction();
        $action->setApi($this->getOnsiteApi());

        $action->execute(
            $status = new GetHumanStatus(
                array(
                    'tcompayway_response' => array('pgw_result_code' => 1001),
                )
            )
        );

        $this->assertTrue($status->isFailed());
    }

    /**
     * @test
     */
    public function shouldMarkOffsiteAuthorizedIfTcomPayWayResponseCodeIsZeroAndAuthorizationTypeIsZero()
    {
        $action = new StatusAction();
        $action->setApi($this->getOffsiteApi());

        $action->execute(
            $status = new GetHumanStatus(
                array(
                    'tcompayway_response' => array('pgw_result_code' => 0),
                )
            )
        );

        $this->assertTrue($status->isAuthorized());
    }

    /**
     * @test
     */
    public function shouldMarkOffsiteCapturedIfTcomPayWayResponseCodeIsZeroAndAuthorizationTypeIsOne()
    {
        $action = new StatusAction();
        $api = $this->getOffsiteApi();
        $api->setPgwAuthorizationType(1);
        $action->setApi($api);

        $action->execute(
            $status = new GetHumanStatus(
                array(
                    'tcompayway_response' => array('pgw_result_code' => 0),
                )
            )
        );

        $this->assertTrue($status->isCaptured());
    }

    /**
     * @test
     */
    public function shouldMarkOffsiteFailedIfTcomPayWayResponseCodeIsMoreThenZeroAndAuthorizationTypeIsZero()
    {
        $action = new StatusAction();
        $action->setApi($this->getOffsiteApi());

        $action->execute(
            $status = new GetHumanStatus(
                array(
                    'tcompayway_response' => array('pgw_result_code' => 1001),
                )
            )
        );

        $this->assertTrue($status->isFailed());
    }

    private function getOnsiteApi()
    {
        return new OnsiteApi(
            12345,
            'secretkey',
            'order-123',
            102400,
            0,
            'http://www.mojducan.com/success/order-123',
            'http://www.mojducan.com/failure/order-123',
            '111111111111111',
            '1812',
            '123',
            'John',
            'Smith',
            'Street 49',
            'Locastic City',
            '1950',
            'LocasticLand',
            'email@example.com'
        );
    }

    private function getOffsiteApi()
    {
        return new OffsiteApi(
            123,
            'new-secret-key',
            'narudžba456',
            789,
            0,
            'http://www.mojducan.com/success/narudžba456',
            'http://www.mojducan.com/failure/narudžba456'
        );
    }

}
