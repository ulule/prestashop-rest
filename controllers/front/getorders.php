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
            $total = 0;
            $orders = array();
            $perPage = Tools::getValue('perPage', 20);
            $page = Tools::getValue('page', 1);
            $limit = $perPage;
            $offset = ($page - 1) * $perPage;
            $test = array();

            if (Tools::getIsset('ids')) {
                $ordersids = preg_split ("/\,/", Tools::getValue('ids'));
                foreach($ordersids as $orderid){
                    $order_obj = new Order($orderid);
                    $order = (new OrderPresenter())->present($order_obj);
                    array_push($orders, $order);
                }
            } else {
                $total = (int) DB::getInstance()->getValue(
                    'SELECT COUNT(id_order)
                    FROM ' . _DB_PREFIX_ . 'orders
                    WHERE reference != \'\''
                );
                $orders = DB::getInstance()->executeS(
                    'SELECT id_order, reference
                    FROM ' . _DB_PREFIX_ . 'orders
                    WHERE reference != \'\'
                    ORDER BY id_order DESC
                    LIMIT ' . $limit .  '
                    OFFSET ' . $offset
                );
                foreach($orders as &$order){
                    $order_obj = new Order($order['id_order']);
                    $order = (new OrderPresenter())->present($order_obj);
                }
            }

            $next = '';
            if (!Tools::getIsset('ids') && $total > $page * $perPage) {
                $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                $next = $this->change_query($actual_link, array('page'=>$page + 1));
            }

            $this->ajaxRender(json_encode([
                'success' => true,
                'code' => 200,
                'psdata' => [
                    'orders' => $orders,
                    'ids' => $total,
                    'pagination' => [
                        'next' => $next,
                    ]
                ]
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
