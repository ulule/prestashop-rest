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

class BienoubienSearchordersModuleFrontController extends AbstractRESTController
{
    protected function processGetRequest()
    {
            $orders = array();;

          if (Tools::getIsset('q')) {
                $q =  $_GET['q'];
                $orders = DB::getInstance()->executeS(
                    "SELECT id_order, reference
                    FROM " . _DB_PREFIX_ . "orders
                    JOIN " . _DB_PREFIX_ . "customer ON " . _DB_PREFIX_ . "orders.id_customer = " . _DB_PREFIX_ . "customer.id_customer
                    WHERE reference != ''
                    AND " . _DB_PREFIX_ . "customer.email = '". $q . "'"
                );
                foreach($orders as &$order){
                    $order_obj = new Order($order['id_order']);
                    $order = (new OrderPresenter())->present($order_obj);
                }
            } else {
              $this->ajaxRender(json_encode([
                  'success' => false,
                  'code' => 404,
                  'message' => 'q params is required'
              ]));
          }

            $this->ajaxRender(json_encode([
                'success' => true,
                'code' => 200,
                'psdata' => [
                    'orders' => $orders,
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

    public function change_query( $url , $array ) {
        $url_decomposition = parse_url ($url);
        $cut_url = explode('?', $url);
        $queries = array_key_exists('query',$url_decomposition)?$url_decomposition['query']:false;
        $queries_array = array ();
        if ($queries) {
            $cut_queries   = explode('&', $queries);
            foreach ($cut_queries as $k => $v) {
                if ($v)
                {
                    $tmp = explode('=', $v);
                    if (sizeof($tmp ) < 2) $tmp[1] = true;
                    $queries_array[$tmp[0]] = urldecode($tmp[1]);
                }
            }
        }
        $newQueries = array_merge($queries_array,$array);
        return $cut_url[0].'?'.http_build_query($newQueries);
    }
}
