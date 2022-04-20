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

use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;

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
        header('Content-Type: ' . "application/json");

        if (Tools::getValue('token') !== Configuration::get('BINSHOPSREST_API_TOKEN')){
            $this->ajaxRender(json_encode([
                'success' => false,
                'code' => 340,
                'psdata' => "Invalid Token"
            ]));
            die;
        }

        $_POST = json_decode(Tools::file_get_contents('php://input'), true);

        //get payload
        $this->_customer = Tools::getValue('customer');
        $this->_billing_address = Tools::getValue('billing_address');
        $this->_line_items = Tools::getValue('line_items');
        $this->_shipping_address = Tools::getValue('shipping_address');
        $this->_shipping_lines = Tools::getValue('shipping_lines');
        $this->_note = Tools::getValue('note');

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
        //Step 0 - Create Cart
        $cart = new Cart();
        $cart->id_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        $cart->id_currency = (int) Configuration::get('PS_CURRENCY_DEFAULT');
        $cart->id_guest = (int) 0;
        $cart->id_shop_group = (int) $this->context->shop->id_shop_group;
        $cart->id_shop = $this->context->shop->id;
        $cart->id_address_delivery = 0;
        $cart->id_address_invoice = 0;

        // Needed if the merchant want to give a free product to every visitors
        $cart->save();
        $this->context->cart = $cart;

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

        $customer->note = $this->_note;

        // for later use we can fill password here
        $password = '';

        $psdata['registered'] = $customerPresister->save($customer, $password);

        /**
         * step 2
         * login
         */

        //actually no need to do this, already logged in
        $customer = $this->context->customer;
        $psdata['logged'] = true;

        /**
         * step 3
         * create billing address
         */
        $id_country = Country::getByIso($this->_billing_address['country_code']);
        $country = new Country($id_country);
        $availableCountries = Country::getCountries($this->context->language->id, true);
        $formatter = new CustomerAddressFormatter(
            $country,
            $this->getTranslator(),
            $availableCountries
        );
        $country = $formatter->getCountry();
        if ($country->need_zip_code){
            if (!$country->checkZipCode($this->_billing_address['zip'])) {
                $this->ajaxRender(json_encode([
                    'success' => false,
                    'code' => 303,
                    'psdata' => [],
                    'message' => $this->translator->trans(
                        'Invalid postcode - should look like "%zipcode%"',
                        ['%zipcode%' => $country->zip_code_format],
                        'Shop.Forms.Errors'
                    )
                ]));
                die;
            }
        }

        $billing_address = new Address();
        $billing_address->alias = 'Alias';
        $billing_address->id_country = $id_country;
        $billing_address->country = $country->name[$this->context->language->id];
        $billing_address->postcode = $this->_billing_address['zip'];
        $billing_address->city = $this->_billing_address['city'];
        $billing_address->address1 = $this->_billing_address['address1'];
        $billing_address->address2 = $this->_billing_address['address2'];
        $billing_address->firstname = $this->_billing_address['first_name'];
        $billing_address->lastname = $this->_billing_address['last_name'];
        $billing_address->id_state = State::getIdByIso($this->_billing_address['province'], $id_country);

        Hook::exec('actionSubmitCustomerAddressForm', ['address' => &$billing_address]);

        $persister = new CustomerAddressPersister(
            $this->context->customer,
            $this->context->cart,
            Tools::getToken(true, $this->context)
        );

        $saved_billing_address = $persister->save(
            $billing_address,
            Tools::getToken(true, $this->context)
        );

        $psdata['saved_billing_address'] = $saved_billing_address;


        if (!$saved_billing_address) {
            $this->ajaxRender(json_encode([
                'success' => false,
                'code' => 302,
                'psdata' => "internal-server-error-save-billing-address"
            ]));
            die;
        }

        /**
         * step 4
         * create shipping address
         */

        $id_country = Country::getByIso($this->_shipping_address['country_code']);
        $country = new Country($id_country);
        $availableCountries = Country::getCountries($this->context->language->id, true);
        $formatter = new CustomerAddressFormatter(
            $country,
            $this->getTranslator(),
            $availableCountries
        );
        $country = $formatter->getCountry();
        if ($country->need_zip_code){
            if (!$country->checkZipCode($this->_shipping_address['zip'])) {
                $this->ajaxRender(json_encode([
                    'success' => false,
                    'code' => 303,
                    'psdata' => [],
                    'message' => $this->translator->trans(
                        'Invalid postcode - should look like "%zipcode%"',
                        ['%zipcode%' => $country->zip_code_format],
                        'Shop.Forms.Errors'
                    )
                ]));
                die;
            }
        }

        $shipping_address = new Address();
        $shipping_address->alias = 'Alias';
        $shipping_address->id_country = $id_country;
        $shipping_address->country = $country->name[$this->context->language->id];
        $shipping_address->postcode = $this->_shipping_address['zip'];
        $shipping_address->city = $this->_shipping_address['city'];
        $shipping_address->address1 = $this->_shipping_address['address1'];
        $shipping_address->address2 = $this->_shipping_address['address2'];
        $shipping_address->firstname = $this->_shipping_address['first_name'];
        $shipping_address->lastname = $this->_shipping_address['last_name'];
        $shipping_address->id_state = State::getIdByIso($this->_shipping_address['province'], $id_country);


        Hook::exec('actionSubmitCustomerAddressForm', ['address' => &$shipping_address]);

        $persister = new CustomerAddressPersister(
            $this->context->customer,
            $this->context->cart,
            Tools::getToken(true, $this->context)
        );

        $saved_shipping_address = $persister->save(
            $shipping_address,
            Tools::getToken(true, $this->context)
        );

        $psdata['saved_shipping_address'] = $saved_shipping_address;

        if (!$saved_shipping_address) {
            $this->ajaxRender(json_encode([
                'success' => false,
                'code' => 302,
                'psdata' => "internal-server-error-save-shipping-address"
            ]));
            die;
        }

        /**
         * step 5
         * add to cart
         */
        $cartProducts = $this->context->cart->getProducts();
        $_GET['update'] = 1;
        $_GET['op'] = 'up';
        $_GET['action'] = 'update';
        // $this->id_product = $this->_line_items[0]['product_id'];
        // $this->id_product_attribute = $this->_line_items[0]['id_product_attribute'];
        // $this->qty = $this->_line_items[0]['quantity'];

        foreach($this->_line_items as $item){
            //needed in updateCart
            // $_GET['update'] = 1;
            // $_GET['op'] = 'up';
            // $_GET['action'] = 'update';
            $this->id_product = $item['product_id'];
            $this->id_product_attribute = $item['id_product_attribute'];
            $this->qty = $item['quantity'];
            // $this->id_product = $this->_line_items[0]['product_id'];
            // $this->id_product_attribute = $this->_line_items[0]['id_product_attribute'];
            // $this->qty = $this->_line_items[0]['quantity'];


            $this->updateCart();
            // $cartProducts = $this->context->cart->getProducts();
        }
        // $this->updateCart();
        $cartProducts = $this->context->cart->getProducts();
        $psdata['addToCart'] = true;

        $this->context->cart->checkAndUpdateAddresses();

        /**
         * step 6
         * set check out addresses
         */
        $session = $this->getCheckoutSession();

        $session->setIdAddressDelivery($shipping_address->id);
        $session->setIdAddressInvoice($billing_address->id);

        $psdata['setAddresses'] = true;

        /**
         * step 6
         * set check out carrier
         */
        $delivery_option = [];
        $delivery_option[$shipping_address->id] = $this->_shipping_lines['carrier_id'] . ',';
        $session->setDeliveryOption($delivery_option);
        $session->getSelectedDeliveryOption();

        $psdata['setCarrier'] = true;

        /**
         * step 7
         * process payment
         */
        if (Validate::isLoadedObject($this->context->cart) && $this->context->cart->OrderExists() == false){
            $total = (float)$cart->getOrderTotal(true, Cart::BOTH);

            $mailVars = array(
                '{bankwire_owner}' => Configuration::get('BANK_WIRE_OWNER'),
                '{bankwire_details}' => nl2br(Configuration::get('BANK_WIRE_DETAILS')),
                '{bankwire_address}' => nl2br(Configuration::get('BANK_WIRE_ADDRESS'))
            );

            $this->module = Module::getInstanceByName('binshopsrest');

            $this->module->validateOrder($cart->id, 2, $total, $this->module->displayName, NULL, $mailVars, (int)$this->context->currency->id, false, $customer->secure_key);

        }else{
            $this->ajaxRender(json_encode([
                'success' => false,
                'message' => 'Order already exists',
                'psdata' => null,
            ]));
            die;
        }

        $this->ajaxRender(json_encode([
            'success' => true,
            'code' => 200,
            'message' => 'order successfully created',
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

    protected function getCheckoutSession()
    {
        $deliveryOptionsFinder = new DeliveryOptionsFinder(
            $this->context,
            $this->getTranslator(),
            $this->objectPresenter,
            new PriceFormatter()
        );

        $session = new CheckoutSession(
            $this->context,
            $deliveryOptionsFinder
        );

        return $session;
    }
}
