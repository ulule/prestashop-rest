<?php
/**
 * 2020 Wild Fortress, Lda
 *
 * NOTICE OF LICENSE
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 *
 * @author    Hélder Duarte <cossou@gmail.com>
 * @copyright 2020 Wild Fortress, Lda
 * @license   Proprietary and confidential
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class WebhookLogModel extends ObjectModel
{
    public $id_log;
    public $id_webhook;
    public $real_time;
    public $url;
    public $payload;
    public $response;
    public $status_code;
    public $date_add;

    public static $definition = array(
        'table' => 'webhooks_log',
        'primary' => 'id_log',
        'multilang' => false,
        'fields' => array(
            'id_log' => array(
                'type' => self::TYPE_INT
            ),
            'id_webhook' => array(
                'type' => self::TYPE_INT
            ),
            'real_time' => array(
                'type' => self::TYPE_BOOL
            ),
            'url' => array(
                'type' => self::TYPE_STRING
            ),
            'payload' => array(
                'type' => self::TYPE_STRING
            ),
            'response' => array(
                'type' => self::TYPE_STRING
            ),
            'status_code' => array(
                'type' => self::TYPE_INT
            ),
            'date_add' => array(
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
            ),
        )
    );

    /**
     * @param WebhookModel $webhook
     * @param mixed $payload
     * @param mixed $response
     * @param int $status_code
     * @return void
     */
    public static function insertLog($webhook, $payload, $response, $status_code)
    {
        return Db::getInstance()->insert(self::$definition["table"], array(
            'id_webhook' => (int) $webhook['id_webhook'],
            'real_time' => (int) $webhook['real_time'],
            'url' => pSQL($webhook['url']),
            'payload' => pSQL(Tools::jsonEncode($payload)),
            'response' => pSQL(Tools::jsonEncode($response)),
            'status_code' => (int) $status_code,
            'date_add' => date('Y-m-d H:i:s')
        ));
    }

    /**
     * @return int
     */
    public static function getLogsTotal()
    {
        $query = new DbQuery();
        $query->select('COUNT(*)')
            ->from(self::$definition["table"]);

        return Db::getInstance()->getValue($query);
    }

    /**
     * @return mixed
     */
    public static function getLogs($page = 1, $pagination = 50)
    {
        $query = new DbQuery();
        $query->select('*')
            ->from(self::$definition["table"])
            ->orderBy(self::$definition["primary"] . ' DESC')
            ->limit($pagination, ($page - 1) * $pagination);

        return Db::getInstance()->ExecuteS($query);
    }

    /**
     * @param int $id_log
     */
    public static function deleteById($id_log)
    {
        Db::getInstance()->delete(self::$definition["table"], self::$definition["primary"] . ' = ' . (int) $id_log, 1);
    }

    /**
     * @param int $id_webhook
     */
    public static function deleteByWebhookId($id_webhook)
    {
        Db::getInstance()->delete(self::$definition["table"], 'id_webhook' . ' = '. (int) $id_webhook);
    }

    /**
     * @param int $id_log
     * @return mixed
     */
    public static function getById($id_log)
    {
        $sql = "SELECT * FROM `" . _DB_PREFIX_ . self::$definition["table"] . "`
            WHERE id_log = '" . (int) $id_log . "'";
        return Db::getInstance()->getRow($sql);
    }
}
