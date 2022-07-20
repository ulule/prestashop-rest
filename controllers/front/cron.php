<?php
/**
 * 2020 Wild Fortress, Lda
 *
 * NOTICE OF LICENSE
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 *
 * @author    Hélder Duarte <cossou@gmail.com>
 * @copyright 2020 Wild Fortress, Lda
 * @license   Proprietary and confidential
 */

class BienoubienCronModuleFrontController extends ModuleFrontController
{
    /** @var bool If set to true, will be redirected to authentication page */
    public $auth = false;

    /** @var bool */
    public $ajax;

    public function display()
    {
        header('Content-Type: ' . "application/json");
        $this->ajax = 1;

        $limit = Tools::getValue('limit', 20);

        if (Tools::getValue('token') !== Configuration::get('BINSHOPSREST_API_TOKEN')){
            $this->ajaxRender(json_encode([
                'success' => false,
                'code' => 340,
                'message' => "Invalid Token"
            ]));
            die;
        }

        $webhooks = Module::getInstanceByName('bienoubien');
        if ($webhooks->active) {
            $results = $webhooks->hookActionCronJob($limit);
            $this->ajaxRender(json_encode([
                'psdata' => $results,
                'code' => 200,
                'success' => true
            ]));
        } else {
            $this->ajaxRender(json_encode([
                'code' => 301,
                'message' => 'Webhooks module is not active'
            ]));
        }
    }
}
