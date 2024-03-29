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

require_once dirname(__FILE__) . '/AbstractRESTController.php';

/**
 * Any REST request which needs authentication must extend this class
 */
abstract class AbstractAuthRESTController extends AbstractRESTController
{
    public $auth = true;
    public $ssl = true;

    public function init()
    {
        header('Content-Type: ' . "application/json");
        if (!$this->context->customer->isLogged() && $this->php_self != 'authentication' && $this->php_self != 'password') {
            $this->ajaxRender(json_encode([
                'code' => 410,
                'success' => false,
                'message' => 'User not authenticated'
            ]));
            die;
        }

        parent::init();
    }
}
