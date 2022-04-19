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

class BinshopsrestCreateorderModuleFrontController extends AbstractRESTController
{
    protected function processGetRequest()
    {
        $this->ajaxRender(json_encode([
            'success' => true,
            'message' => 'GET not supported on this path'
        ]));
        die;
    }

    protected function processPostRequest()
    {
        $_POST = json_decode(Tools::file_get_contents('php://input'), true);

        //get payload
        $_customer = Tools::getValue('customer');
        $_billing_address = Tools::getValue('billing_address');
        $_line_items = Tools::getValue('line_items');
        $_shipping_address = Tools::getValue('shipping_address');
        $_shipping_lines = Tools::getValue('shipping_lines');
        $_note = Tools::getValue('note');
        
        $psdata = [];
        
        /**
         * step 1
         * create customer
         */
        $guestAllowedCheckout = Configuration::get('PS_GUEST_CHECKOUT_ENABLED');
        $customerPresister = new CustomerPersister(
            $this->context,
            $this->get('hashing'),
            $this->getTranslator(),
            $guestAllowedCheckout
        );
        $customer = new Customer();
        $customer->email = $_customer['email'];
        $customer->firstname = $_customer['first_name'];
        $customer->lastname = $_customer['last_name'];
        
        // for later use we can fill password here
        $password = '';

        $psdata['registered'] = $customerPresister->save($customer, $password);

        $this->ajaxRender(json_encode([
            'success' => true,
            'message' => '',
            'psdata' => $psdata,
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
