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

class WebhookDecorator
{
    /**
     * @param string $hook_name
     * @return string
     * @throws Exception
     */
    public static function getDecoratorClass($hook_name)
    {
        switch ($hook_name) {
            case 'actionProductAdd':
            case 'actionProductUpdate':
            case 'actionProductDelete':
                return WebhookProduct::class;
            case 'actionOrderHistoryAddAfter':
            case 'actionValidateOrder':
                return WebhookOrder::class;
            case 'actionCustomerAccountAdd':
            case 'actionCustomerAccountUpdate':
                return WebhookCustomer::class;
            case 'actionObjectCustomerMessageAddAfter':
                return WebhookCustomerMessage::class;
            default:
                throw new Exception("Invalid $hook_name");
        }
    }

    /**
     * @param string $hook_name
     * @return bool
     * @throws Exception
     */
    public static function hasDecorator($hook_name)
    {
        return self::getDecoratorClass($hook_name) !== false;
    }

    /**
     * @param string $hook_name
     * @param string $payload
     * @return int
     * @throws Exception
     */
    public static function getDecoratorValue($hook_name, $payload)
    {
        switch ($hook_name) {
            case 'actionProductAdd':
            case 'actionProductUpdate':
            case 'actionProductDelete':
                return (int) $payload->id_product;
            case 'actionOrderHistoryAddAfter':
                return (int) $payload->order_history->id_order;
            case 'actionValidateOrder':
                return (int) $payload->order->id;
            case 'actionCustomerAccountAdd':
                return (int) $payload->newCustomer->id;
            case 'actionCustomerAccountUpdate':
                return (int) $payload->customer->id;
            case 'actionObjectCustomerMessageAddAfter':
                return (int) $payload->object->id_customer_thread;
            default:
                throw new Exception("Invalid $hook_name");
        }
    }
}
