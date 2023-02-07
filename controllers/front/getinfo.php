<?php

/**
 * BINSHOPS
 *
 * @author BINSHOPS
 * @copyright BINSHOPS
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * Best In Shops eCommerce Solutions Inc.
 *
 */

require_once dirname(__FILE__) . '/../AbstractRESTController.php';

use PrestaShop\PrestaShop\Adapter\Presenter\Order\OrderPresenter;

class BienoubiengetinfoModuleFrontController extends AbstractRESTController
{
    protected function processGetRequest()
    {
        $module = Module::getInstanceByName('bienoubien');
        $version = $module->version;
        $this->ajaxRender(json_encode([
            'success' => true,
            'code' => 200,
            'psdata' => [
                'version' => $version,
            ]
        ]));
        die;
    }

    protected function processPostRequest()
    {
        $this->ajaxRender(json_encode([
            'success' => true,
            'message' => 'POST not supported on this path'
        ]));
        die;
    }

    protected function processPutRequest()
    {
        $this->ajaxRender(json_encode([
            'success' => true,
            'message' => 'put not supported on this path'
        ]));
        die;
    }

    protected function processDeleteRequest()
    {
        $this->ajaxRender(json_encode([
            'success' => true,
            'message' => 'delete not supported on this path'
        ]));
        die;
    }
}
