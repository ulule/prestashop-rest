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
require_once(dirname(__FILE__) . '/../../vendor/autoload.php');

class BinshopsrestWebhookModuleFrontController extends AbstractRESTController
{
    protected function processGetRequest()
    {
        if (Tools::getValue('token') !== Configuration::get('BINSHOPSREST_API_TOKEN')){
            $this->ajaxRender(json_encode([
                'success' => false,
                'code' => 340,
                'psdata' => "Invalid Token"
            ]));
            die;
        }

        $page = ($page = Tools::getValue('page')) ? $page : 1;
        $pagination = ($pagination = Tools::getValue('pagination')) ? $pagination : 50;

        $this->ajaxRender(json_encode([
            'code' => 200,
            'success' => true,
            'message' => 'success',
            'psdata' => WebhookModel::getWebhooks($page, $pagination)
        ]));
        die;
    }

    protected function processPostRequest()
    {
        $_POST = json_decode(Tools::file_get_contents('php://input'), true);

        if (Tools::getValue('token') !== Configuration::get('BINSHOPSREST_API_TOKEN')){
            $this->ajaxRender(json_encode([
                'success' => false,
                'code' => 340,
                'psdata' => "Invalid Token"
            ]));
            die;
        }

        $url = filter_var(Tools::getValue('webhooks_url'), FILTER_SANITIZE_URL);

        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            $message = 'The URL is invalid';
            $code = 310;
        } elseif (!Validate::isHookName(Tools::getValue('webhooks_hook'))) {
            $message = 'Hook name is invalid';
            $code = 320;
        } elseif (!Validate::isUnsignedInt(Tools::getValue('webhooks_retries')) ||
            ((int) Tools::getValue('webhooks_retries')) > 99) {
            $message = 'Please choose a retry number between 0 and 99';
            $code = 330;
        } else {
            WebhookModel::insertWebhook(
                $url,
                Tools::getValue('webhooks_hook'),
                Tools::getValue('webhooks_real_time', 0),
                (int)Tools::getValue('webhooks_retries'),
                1
            );

            $code = 200;
            $message = 'success';
        }

        $this->ajaxRender(json_encode([
            'success' => true,
            'message' => $message,
            'code' => $code
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
        $_POST = json_decode(Tools::file_get_contents('php://input'), true);

        if (Tools::getValue('token') !== Configuration::get('BINSHOPSREST_API_TOKEN')){
            $this->ajaxRender(json_encode([
                'success' => false,
                'code' => 340,
                'psdata' => "Invalid Token"
            ]));
            die;
        }

        WebhookLogModel::deleteByWebhookId((int) Tools::getValue('id_webhook'));
        WebhookQueueModel::deleteByWebhookId((int) Tools::getValue('id_webhook'));
        WebhookModel::deleteById((int) Tools::getValue('id_webhook'));

        $this->ajaxRender(json_encode([
            'code' => 200,
            'success' => true,
            'message' => 'sucess'
        ]));
        die;
    }
}
