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

class BinshopsrestGetordersModuleFrontController extends AbstractRESTController
{
    protected function processGetRequest()
    {
        //single order
        if (Tools::getIsset('id_order')) {
            $id_order = Tools::getValue('id_order');
            $order = new Order($id_order);

            $order_to_display = (new OrderPresenter())->present($order);
            $this->ajaxRender(json_encode([
                'success' => true,
                'code' => 200,
                'psdata' => $order_to_display
            ]));
            die;
        }else{
            $total = (int) DB::getInstance()->getValue(
                'SELECT COUNT(id_order)
            FROM ' . _DB_PREFIX_ . 'orders'
            );
            $perPage = Tools::getIsset('perPage') ? (int) Tools::getValue('perPage') : 5;
            $page = Tools::getIsset('page') ? (int) Tools::getValue('page') : 1;

            $limit = $perPage;

            $offset = ($page - 1) * $perPage;

            $orders = DB::getInstance()->executeS(
                'SELECT id_order
            FROM ' . _DB_PREFIX_ . 'orders
            LIMIT ' . $limit .  '
            OFFSET ' . $offset
            );

            foreach($orders as &$order){
                $order_obj = new Order($order['id_order']);
                $order = (new OrderPresenter())->present($order_obj);
            }

            $this->ajaxRender(json_encode([
                'success' => true,
                'code' => 200,
                'psdata' => $orders
            ]));
            die;
        }
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
