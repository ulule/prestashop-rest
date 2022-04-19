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

require_once dirname(__FILE__) . '/../AbstractCartRESTController.php';

class BinshopsrestCreateorderModuleFrontController extends AbstractCartRESTController
{
    protected $_customer;
    protected $_billing_address;
    protected $_line_items;
    protected $_shipping_address;
    protected $_shipping_lines;
    protected $_note;

    public function init(){
        $_POST = json_decode(Tools::file_get_contents('php://input'), true);
        
        //get payload
        $this->_customer = Tools::getValue('customer');
        $this->_billing_address = Tools::getValue('billing_address');
        $this->_line_items = Tools::getValue('line_items');
        $this->_shipping_address = Tools::getValue('shipping_address');
        $this->_shipping_lines = Tools::getValue('shipping_lines');
        $this->_note = Tools::getValue('note');
         
        //needed in updateCart
        $_GET['update'] = 1;
        $_GET['op'] = 'up';
        $_GET['action'] = 'update';
        $_GET['id_product'] = $this->_line_items[0]['product_id']; 
        $_GET['id_product_attribute'] = $this->_line_items[0]['id_product_attribute']; 
        $_GET['qty'] = $this->_line_items[0]['quantity'];

        parent::init();
    }

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
        $customer->email = $this->_customer['email'];
        $customer->firstname = $this->_customer['first_name'];
        $customer->lastname = $this->_customer['last_name'];
        
        // for later use we can fill password here
        $password = '';

        $psdata['registered'] = $customerPresister->save($customer, $password);

        /**
         * step 2
         * login
         */

        //actually no need to do this, already logged in
        $customer = $this->context->customer;
        
        /**
         * step 3
         * create address
         */

        $address = 

        /**
         * step 4
         * add to cart
         */

        $this->updateCart();

        $cartProducts = $this->context->cart->getProducts();


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

    protected function updateCart()
    {
        // Update the cart ONLY if $this->cookies are available, in order to avoid ghost carts created by bots
        if ($this->context->cookie->exists()
            && !$this->errors)
        {
            if (Tools::getIsset('add') || Tools::getIsset('update')) {
                $this->processChangeProductInCart();
            } elseif (Tools::getIsset('delete')) {
                $this->processDeleteProductInCart();
            } elseif (CartRule::isFeatureActive()) {
                if (Tools::getIsset('addDiscount')) {
                    if (!($code = trim(Tools::getValue('discount_name')))) {
                        $this->errors[] = $this->trans(
                            'You must enter a voucher code.',
                            [],
                            'Shop.Notifications.Error'
                        );
                    } elseif (!Validate::isCleanHtml($code)) {
                        $this->errors[] = $this->trans(
                            'The voucher code is invalid.',
                            [],
                            'Shop.Notifications.Error'
                        );
                    } else {
                        if (($cartRule = new CartRule(CartRule::getIdByCode($code)))
                            && Validate::isLoadedObject($cartRule)
                        ) {
                            if ($error = $cartRule->checkValidity($this->context, false, true)) {
                                $this->errors[] = $error;
                            } else {
                                $this->context->cart->addCartRule($cartRule->id);
                            }
                        } else {
                            $this->errors[] = $this->trans(
                                'This voucher does not exist.',
                                [],
                                'Shop.Notifications.Error'
                            );
                        }
                    }
                } elseif (($id_cart_rule = (int) Tools::getValue('deleteDiscount'))
                    && Validate::isUnsignedId($id_cart_rule)
                ) {
                    $this->context->cart->removeCartRule($id_cart_rule);
                    CartRule::autoAddToCart($this->context);
                }
            }
        } elseif (!$this->isTokenValid() && Tools::getValue('action') !== 'show' && !Tools::getValue('ajax')) {
            $this->ajaxRender(json_encode([
                'code' => 301,
                'success' => false,
                'message' => 'cookie is not set',
            ]));
            die;
        }
    }
}
