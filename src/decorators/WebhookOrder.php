<?php
/**
 * 2020 Wild Fortress, Lda
 *
 * NOTICE OF LICENSE
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 *
 *  @author    Hélder Duarte <cossou@gmail.com>
 *  @copyright 2020 Wild Fortress, Lda
 *  @license   Proprietary and confidential
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class WebhookOrder
{
    private $params;
    private $id_order;

    /**
     * WebhookOrder constructor.
     * @param int $id_order
     * @param array $params
     * @throws Exception
     */
    public function __construct($id_order, $params = array())
    {
        $this->id_order = $id_order;
        $this->params = $params;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function present()
    {
        if (!is_int($this->id_order)) {
            return $this->params;
        }

        $order = new Order($this->id_order);

        if (!is_a($order, 'Order')) {
            return $this->params;
        }

        $amounts = $this->getAmounts($order);

        $data = array(
            'order' => $order,

            'customer' => $this->getCustomer($order),
            'addresses' => $this->getAddresses($order),

            'products' => $this->getProducts($order),
            'products_count' => count($this->getProducts($order)),

            'details' => $this->getDetails($order),
            'history' => $this->getHistory($order),
            'messages' => $this->getMessages($order),
            'carrier' => $this->getCarrier($order),
            'follow_up' => $this->getFollowUp($order),
            'shipping' => $this->getShipping($order),

            'invoices' => $this->getInvoices($order),
            'delivery_slips' => $this->getDeliverySlips($order),
            'slips' => $this->getSlips($order),

            'discounts' => $this->getOrderDiscounts($order),

            'id_address_delivery' => $order->id_address_delivery,
            'id_address_invoice' => $order->id_address_invoice,

            'totals' => $amounts['totals'],
            'subtotals' => $amounts['subtotals'],

            'extra_params' => $this->params
        );

        return $data;
    }

    /**
     * @param Order $order
     *
     * @return array
     */
    private function getCustomer(Order $order)
    {
        $customerArray = array();
        $customer = new Customer((int) $order->id_customer);
        $stats = $customer->getStats();
        $gender = new Gender((int) $customer->id_gender, $order->id_lang);

        $customerArray['id'] = $customer->id;
        $customerArray['id_shop'] = $customer->id_shop;
        $customerArray['id_shop_group'] = $customer->id_shop_group;
        $customerArray['note'] = $customer->note;
        $customerArray['id_gender'] = $customer->id_gender;
        $customerArray['id_default_group'] = $customer->id_default_group;
        $customerArray['id_lang'] = $customer->id_lang;
        $customerArray['lastname'] = $customer->lastname;
        $customerArray['firstname'] = $customer->firstname;
        $customerArray['birthday'] = $customer->birthday;
        $customerArray['email'] = $customer->email;
        $customerArray['newsletter'] = $customer->newsletter;
        $customerArray['ip_registration_newsletter'] = $customer->ip_registration_newsletter;
        $customerArray['newsletter_date_add'] = $customer->newsletter_date_add;
        $customerArray['optin'] = $customer->optin;
        $customerArray['website'] = $customer->website;
        $customerArray['company'] = $customer->company;
        $customerArray['siret'] = $customer->siret;
        $customerArray['ape'] = $customer->ape;
        $customerArray['outstanding_allow_amount'] = $customer->outstanding_allow_amount;
        $customerArray['show_public_prices'] = $customer->show_public_prices;
        $customerArray['id_risk'] = $customer->id_risk;
        $customerArray['max_payment_days'] = $customer->max_payment_days;
        $customerArray['active'] = $customer->active;
        $customerArray['is_guest'] = $customer->is_guest;
        $customerArray['deleted'] = $customer->deleted;
        $customerArray['date_add'] = $customer->date_add;
        $customerArray['date_upd'] = $customer->date_upd;
        $customerArray['years'] = $customer->years;
        $customerArray['days'] = $customer->days;
        $customerArray['months'] = $customer->months;
        $customerArray['geoloc_id_country'] = $customer->geoloc_id_country;
        $customerArray['geoloc_id_state'] = $customer->geoloc_id_state;
        $customerArray['geoloc_postcode'] = $customer->geoloc_postcode;
        $customerArray['logged'] = $customer->logged;
        $customerArray['id_guest'] = $customer->id_guest;
        $customerArray['groupBox'] = $customer->groupBox;
        $customerArray['id_shop_list'] = $customer->id_shop_list;
        $customerArray['force_id'] = $customer->force_id;

        // Customer Gender
        $customerArray['gender'] = array(
            'id' => $gender->id,
            'id_gender' => $gender->id_gender,
            'type' => $gender->type,
            'name' => $gender->name,
        );

        // Customer Stats
        $customerArray['stats'] = $stats;

        return $customerArray;
    }

    /**
     * @param Order $order
     *
     * @return array
     */
    private function getProducts(Order $order)
    {
        $orderProducts = $order->getCartProducts();

        foreach ($orderProducts as &$orderProduct) {
            $orderProduct['name'] = $orderProduct['product_name'];
            $orderProduct['price'] = $orderProduct['product_price'];
            $orderProduct['quantity'] = $orderProduct['product_quantity'];
            $orderProduct['total'] = $orderProduct['total_price'];
            $orderProduct['tags'] = Tag::getProductTags($orderProduct['product_id']);

            if ($orderProduct['is_virtual']) {
                $id_product_download = ProductDownload::getIdFromIdProduct($orderProduct['product_id']);
                $product_download = new ProductDownload($id_product_download);
                if ($product_download->display_filename != '') {
                    $orderProduct['download_link'] =
                        $product_download->getTextLink(false, $orderProduct['download_hash'])
                        . '&id_order=' . (int)$order->id
                        . '&secure_key=' . $order->secure_key;
                }
            }

            if ($orderProduct['image'] != null) {
                $name = 'product_mini_' . (int) $orderProduct['product_id'] .
                    (isset($orderProduct['product_attribute_id']) ?
                        '_' . (int) $orderProduct['product_attribute_id'] : ''
                    ) . '.jpg';

                if (file_exists(_PS_TMP_IMG_DIR_ . $name)) {
                    $orderProduct['image_size'] = getimagesize(_PS_TMP_IMG_DIR_ . $name);
                } else {
                    $orderProduct['image_size'] = false;
                }

                $orderProduct['images'] = Image::getImages((int) $order->id_lang, (int) $orderProduct['product_id']);

                $id_image = Product::getCover($orderProduct['product_id']);

                if (sizeof($id_image) > 0) {
                    $image = new Image($id_image['id_image']);
                    $orderProduct['cover_image_url'] = _PS_BASE_URL_ .
                        _THEME_PROD_DIR_ . $image->getExistingImgPath().".jpg";
                }
            }

            if (Feature::isFeatureActive()) {
                $orderProduct['features'] = Product::getFrontFeaturesStatic(
                    $order->id_lang,
                    $orderProduct['product_id']
                );
            }
        }

        return $orderProducts;
    }

    /**
     * @param Order $order
     *
     * @return array
     */
    private function getAmounts(Order $order)
    {
        $amounts = array();
        $subtotals = array();

        $total_products = ($this->includeTaxes()) ? $order->total_products_wt : $order->total_products;

        $subtotals['products'] = array(
            'type' => 'products',
            'label' => 'Subtotal',
            'amount' => (float) $total_products,
            'value' => $total_products,
        );

        $discount_amount = ($this->includeTaxes())
            ? $order->total_discounts_tax_incl
            : $order->total_discounts_tax_excl;

        if ((float) $discount_amount) {
            $subtotals['discounts'] = array(
                'type' => 'discount',
                'label' => 'Discount',
                'amount' => (float) $discount_amount,
                'value' => $discount_amount,
            );
        }

        $cart = new Cart($order->id_cart);
        if (!$cart->isVirtualCart()) {
            $shippingCost = ($this->includeTaxes()) ? $order->total_shipping_tax_incl : $order->total_shipping_tax_excl;
            $subtotals['shipping'] = array(
                'type' => 'shipping',
                'label' => 'Shipping and handling',
                'amount' => (float) $shippingCost,
                'value' => $shippingCost != 0 ? $shippingCost : 'Free',
            );
        }

        $tax = $order->total_paid_tax_incl - $order->total_paid_tax_excl;
        $subtotals['tax'] = array(
            'type' => 'tax',
            'label' => null,
            'amount' => (float) null,
            'value' => '',
        );
        if ((float) $tax && Configuration::get('PS_TAX_DISPLAY')) {
            $subtotals['tax'] = array(
                'type' => 'tax',
                'label' => 'Tax',
                'amount' => (float) $tax,
                'value' => $tax,
            );
        }

        if ($order->gift) {
            $giftWrapping = ($this->includeTaxes())
                ? $order->total_wrapping_tax_incl
                : $order->total_wrapping_tax_incl;
            $subtotals['gift_wrapping'] = array(
                'type' => 'gift_wrapping',
                'label' => 'Gift wrapping',
                'amount' => (float) $giftWrapping,
                'value' => $giftWrapping,
            );
        }

        $amounts['subtotals'] = $subtotals;

        $amounts['totals'] = array();
        $amounts['totals']['total'] = array(
            'type' => 'total',
            'label' => 'Total',
            'amount' => (float) $order->total_paid,
            'value' => $order->total_paid,
        );

        $amounts['totals']['total_paid'] = array(
            'type' => 'total_paid',
            'label' => 'Total paid',
            'amount' => (float) $order->total_paid_real,
            'value' => $order->total_paid_real,
        );

        return $amounts;
    }

    /**
     * @param Order $order
     *
     * @return array
     */
    private function getDetails(Order $order)
    {
        $context = Context::getContext();
        $cart = new Cart($order->id_cart);

        return array(
            'id' => $order->id,
            'reference' => $order->reference,
            'order_date' => $order->date_add,
            'details_url' => $context->link->getPageLink('order-detail', true, null, 'id_order='.$order->id),
            'gift_message' => nl2br($order->gift_message),
            'is_returnable' => (int) $order->isReturnable(),
            'is_virtual' => $cart->isVirtualCart(),
            'id_cart' => $cart->id,
            'payment' => $order->payment,
            'recyclable' => (bool) $order->recyclable,
            'shipping' => $this->getShipping($order),
            'is_valid' => $order->valid,
            'has_invoice' => $order->hasInvoice(),
            'has_been_delivered' => $order->hasBeenDelivered(),
            'currency' => new Currency($order->id_currency),
            'iso_code_lang' => $context->language->iso_code,
            'id_lang' => $context->language->id,
        );
    }

    /**
     * @param Order $order
     *
     * @return array
     */
    private function getHistory(Order $order)
    {
        $context = Context::getContext();
        return Db::getInstance()->ExecuteS('SELECT ' .
            ' os.*, ' .
            ' oh.*, ' .
            ' e.`firstname` as employee_firstname, ' .
            ' e.`lastname` as employee_lastname, ' .
            ' oh.date_add as history_date, ' .
            ' osl.`name` as ostate_name ' .
            ' FROM' .
            '    `' . _DB_PREFIX_ . 'orders` o' .
            ' LEFT JOIN `' . _DB_PREFIX_ . 'order_history` oh ON' .
            '    (o.`id_order` = oh.`id_order`)' .
            ' LEFT JOIN `' . _DB_PREFIX_ . 'order_state` os ON' .
            '    (os.`id_order_state` = oh.`id_order_state`)' .
            ' LEFT JOIN `' . _DB_PREFIX_ . 'order_state_lang` osl ON' .
            '    (os.`id_order_state` = osl.`id_order_state` AND  ' .
            '     osl.`id_lang` = ' . (int) $context->language->id . ')' .
            ' LEFT JOIN `' . _DB_PREFIX_ . 'employee` e ON' .
            '    (e.`id_employee` = oh.`id_employee`)' .
            ' WHERE' .
            '    oh.`id_order` = ' . (int) $order->id .
            ' ORDER BY oh.date_add DESC, oh.id_order_history DESC');
    }

    /**
     * @param Order $order
     *
     * @return array
     */
    private function getShipping(Order $order)
    {
        $shippingList = $order->getShipping();
        $orderShipping = array();

        foreach ($shippingList as $shippingId => $shipping) {
            if (isset($shipping['carrier_name']) && $shipping['carrier_name']) {
                $orderShipping[$shippingId] = $shipping;
                $orderShipping[$shippingId]['shipping_date'] = $shipping['date_add'];
                $orderShipping[$shippingId]['shipping_weight'] = ($shipping['weight'] > 0) ?
                    sprintf('%.3f', $shipping['weight']).' '.Configuration::get('PS_WEIGHT_UNIT') :
                    '-';
                $shippingCost = (!$order->getTaxCalculationMethod()) ? $shipping['shipping_cost_tax_excl'] :
                    $shipping['shipping_cost_tax_incl'];
                $orderShipping[$shippingId]['shipping_cost'] = $shippingCost;

                $tracking_line = '-';
                if ($shipping['tracking_number']) {
                    if ($shipping['url'] && $shipping['tracking_number']) {
                        $tracking_line = '<a href="' .
                            str_replace('@', $shipping['tracking_number'], $shipping['url']) .
                            '">' . $shipping['tracking_number'] . '</a>';
                    } else {
                        $tracking_line = $shipping['tracking_number'];
                    }
                }

                $orderShipping[$shippingId]['tracking'] = $tracking_line;
            }
        }

        return $orderShipping;
    }

    /**
     * @param Order $order
     *
     * @return array
     */
    private function getMessages(Order $order)
    {
        $messages = array();
        $customerMessages = CustomerMessage::getMessagesByOrderId((int) $order->id, false);

        foreach ($customerMessages as $cmId => $customerMessage) {
            $messages[$cmId] = $customerMessage;
            $messages[$cmId]['message'] = nl2br($customerMessage['message']);
            $messages[$cmId]['message_date'] = $customerMessage['date_add'];
            if (isset($customerMessage['elastname']) && $customerMessage['elastname']) {
                $messages[$cmId]['name'] = $customerMessage['efirstname'].' '.$customerMessage['elastname'];
            } elseif ($customerMessage['clastname']) {
                $messages[$cmId]['name'] = $customerMessage['cfirstname'].' '.$customerMessage['clastname'];
            } else {
                $messages[$cmId]['name'] = Configuration::get('PS_SHOP_NAME');
            }
        }

        return $messages;
    }

    /**
     * @param Order $order
     *
     * @return array
     */
    private function getCarrier(Order $order)
    {
        $carrier = new Carrier((int) $order->id_carrier, (int) $order->id_lang);

        $orderCarrier = array();

        $orderCarrier['id_reference'] = $carrier->id_reference;
        $orderCarrier['name'] = ($carrier->name == '0') ? Configuration::get('PS_SHOP_NAME') : $carrier->name;
        $orderCarrier['url'] = $carrier->url;
        $orderCarrier['delay'] = $carrier->delay;
        $orderCarrier['active'] = $carrier->active;
        $orderCarrier['deleted'] = $carrier->deleted;
        $orderCarrier['shipping_handling'] = $carrier->shipping_handling;
        $orderCarrier['range_behavior'] = $carrier->range_behavior;
        $orderCarrier['is_module'] = $carrier->is_module;
        $orderCarrier['is_free'] = $carrier->is_free;
        $orderCarrier['shipping_method'] = $carrier->shipping_method;
        $orderCarrier['shipping_external'] = $carrier->shipping_external;
        $orderCarrier['external_module_name'] = $carrier->external_module_name;
        $orderCarrier['need_range'] = $carrier->need_range;
        $orderCarrier['position'] = $carrier->position;
        $orderCarrier['max_width'] = $carrier->max_width;
        $orderCarrier['max_height'] = $carrier->max_height;
        $orderCarrier['max_depth'] = $carrier->max_depth;
        $orderCarrier['max_weight'] = $carrier->max_weight;
        $orderCarrier['grade'] = $carrier->grade;
        $orderCarrier['id'] = $carrier->id;
        $orderCarrier['id_shop_list'] = $carrier->id_shop_list;
        $orderCarrier['force_id'] = $carrier->force_id;
        $orderCarrier['id_tax_rules_group'] = isset($carrier->id_tax_rules_group) ? $carrier->id_tax_rules_group : 0;

        return $orderCarrier;
    }

    /**
     * @param Order $order
     *
     * @return array
     */
    private function getAddresses(Order $order)
    {
        $orderAddresses = array(
            'delivery' => array(),
            'invoice' => array(),
        );

        $addressInvoice = new Address((int) $order->id_address_invoice, (int) $order->id_lang);
        $addressInvoice->state = State::getNameById((int) $addressInvoice->id_state);

        $orderAddresses['invoice'] = $addressInvoice;

        if (!$order->isVirtual()) {
            $addressDelivery = new Address((int) $order->id_address_delivery, (int) $order->id_lang);
            $addressDelivery->state = State::getNameById((int) $addressDelivery->id_state);

            $orderAddresses['delivery'] = $addressDelivery;
        }

        return $orderAddresses;
    }

    /**
     * @param Order $order
     *
     * @return string
     */
    private function getFollowUp(Order $order)
    {
        $carrier = $this->getCarrier($order);

        if (!empty($carrier->url) && !empty($order->shipping_number)) {
            return str_replace('@', $order->shipping_number, $carrier->url);
        }

        return '';
    }

    private function includeTaxes()
    {
        return Tools::getValue('TaxMethod');
    }

    private function getInvoices(Order $order)
    {
        $invoices_array = array();
        $invoices = $order->getInvoicesCollection()->getResults();

        foreach ($invoices as $invoice) {
            $invoices_array[] = array(
                'id' => $invoice->id,
                'number' => $invoice->number,
                'delivery_number' => $invoice->delivery_number,
                'delivery_date' => $invoice->delivery_date,
                'total_discount_tax_excl' => $invoice->total_discount_tax_excl,
                'total_discount_tax_incl' => $invoice->total_discount_tax_incl,
                'total_paid_tax_excl' => $invoice->total_paid_tax_excl,
                'total_paid_tax_incl' => $invoice->total_paid_tax_incl,
                'total_products' => $invoice->total_products,
                'total_products_wt' => $invoice->total_products_wt,
                'total_shipping_tax_excl' => $invoice->total_shipping_tax_excl,
                'total_shipping_tax_incl' => $invoice->total_shipping_tax_incl,
                'shipping_tax_computation_method' => $invoice->shipping_tax_computation_method,
                'total_wrapping_tax_excl' => $invoice->total_wrapping_tax_excl,
                'total_wrapping_tax_incl' => $invoice->total_wrapping_tax_incl,
                'shop_address' => $invoice->shop_address,
                'note' => $invoice->note,
                'date_add' => $invoice->date_add,
            );
        }

        return $invoices_array;
    }

    private function getSlips($order)
    {
        $slips_array = array();
        $slips = $order->getOrderSlipsCollection()->getResults();

        foreach ($slips as $slip) {
            $slips_array[] = array(
                'id' => $slip->id,
                'id_customer' => $slip->id_customer,
                'conversion_rate' => $slip->conversion_rate,
                'total_products_tax_excl' => $slip->total_products_tax_excl,
                'total_products_tax_incl' => $slip->total_products_tax_incl,
                'total_shipping_tax_excl' => $slip->total_shipping_tax_excl,
                'total_shipping_tax_incl' => $slip->total_shipping_tax_incl,
                'amount' => $slip->amount,
                'shipping_cost' => $slip->shipping_cost,
                'shipping_cost_amount' => $slip->shipping_cost_amount,
                'partial' => $slip->partial,
                'order_slip_type' => $slip->order_slip_type,
                'date_add' => $slip->date_add,
                'date_upd' => $slip->date_upd,
            );
        }

        return $slips_array;
    }

    private function getDeliverySlips(Order $order)
    {
        $delivery_slips_array = array();
        $delivery_slips = $order->getDeliverySlipsCollection()->getResults();

        foreach ($delivery_slips as $delivery_slip) {
            $delivery_slips_array[] = array(
                'id' => $delivery_slip->id,
                'id_shop_list' => $delivery_slip->id_shop_list,
                'id_order' => $delivery_slip->id_order,
                'number' => $delivery_slip->number,
                'delivery_number' => $delivery_slip->delivery_number,
                'delivery_date' => $delivery_slip->delivery_date,
                'total_discount_tax_excl' => $delivery_slip->total_discount_tax_excl,
                'total_discount_tax_incl' => $delivery_slip->total_discount_tax_incl,
                'total_paid_tax_excl' => $delivery_slip->total_paid_tax_excl,
                'total_paid_tax_incl' => $delivery_slip->total_paid_tax_incl,
                'total_products' => $delivery_slip->total_products,
                'total_products_wt' => $delivery_slip->total_products_wt,
                'total_shipping_tax_excl' => $delivery_slip->total_shipping_tax_excl,
                'total_shipping_tax_incl' => $delivery_slip->total_shipping_tax_incl,
                'shipping_tax_computation_method' => $delivery_slip->shipping_tax_computation_method,
                'total_wrapping_tax_excl' => $delivery_slip->total_wrapping_tax_excl,
                'total_wrapping_tax_incl' => $delivery_slip->total_wrapping_tax_incl,
                'shop_address' => $delivery_slip->shop_address,
                'note' => $delivery_slip->note,
                'date_add' => $delivery_slip->date_add,
            );
        }

        return $delivery_slips_array;
    }

    private function getOrderDiscounts(Order $order)
    {
        return Db::getInstance()->ExecuteS('SELECT' .
           '    ocr.`id_order_cart_rule`, ' .
           '    ocr.`id_cart_rule`, ' .
           '    ocr.`id_order_invoice`, ' .
           '    ocr.`name`, ' .
           '    ocr.`value`, ' .
           '    ocr.`value_tax_excl`, ' .
           '    ocr.`free_shipping`, ' .
           '    cr.`date_from`, ' .
           '    cr.`date_to`, ' .
           '    cr.`description`, ' .
           '    cr.`quantity`, ' .
           '    cr.`quantity_per_user`, ' .
           '    cr.`priority`, ' .
           '    cr.`partial_use`, ' .
           '    cr.`code`, ' .
           '    cr.`minimum_amount`, ' .
           '    cr.`minimum_amount_tax`, ' .
           '    cr.`minimum_amount_currency`, ' .
           '    cr.`minimum_amount_shipping`, ' .
           '    cr.`country_restriction`, ' .
           '    cr.`carrier_restriction`, ' .
           '    cr.`group_restriction`, ' .
           '    cr.`cart_rule_restriction`, ' .
           '    cr.`product_restriction`, ' .
           '    cr.`shop_restriction`, ' .
           '    cr.`free_shipping`, ' .
           '    cr.`reduction_percent`, ' .
           '    cr.`reduction_amount`, ' .
           '    cr.`reduction_tax`, ' .
           '    cr.`reduction_currency`, ' .
           '    cr.`reduction_product`, ' .
           '    cr.`gift_product`, ' .
           '    cr.`gift_product_attribute`, ' .
           '    cr.`highlight`, ' .
           '    cr.`active` ' .
           ' FROM' .
           '    `' . _DB_PREFIX_ . 'order_cart_rule` ocr' .
           ' LEFT JOIN `' . _DB_PREFIX_ . 'cart_rule` cr ON' .
           '    (cr.`id_cart_rule` = ocr.`id_cart_rule`)' .
           ' WHERE' .
           '    ocr.`id_order` = ' . (int) $order->id);
    }
}
