<?php
namespace Locastic\TcomPayWayPayumBundle\Request;

use Locastic\TcomPayWay\AuthorizeDirect\Api;

class GetApi
{
    /**
     * @var Api
     */
    private $api;

    /**
     * @return Api
     */
    public function getApi()
    {
        return $this->api;
    }

    /**
     * @param Api $api
     */
    public function setApi(Api $api)
    {
        $this->api = $api;
    }
}
