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

class WebhookQueueModel extends ObjectModel
{
    public $id_queue;
    public $id_webhook;
    public $executed;
    public $retry;
    public $url;
    public $payload;
    public $date_add;

    public static $definition = array(
        'table' => 'webhooks_queue',
        'primary' => 'id_queue',
        'multilang' => false,
        'fields' => array(
            'id_queue' => array(
                'type' => self::TYPE_INT
            ),
            'id_webhook' => array(
                'type' => self::TYPE_INT
            ),
            'executed' => array(
                'type' => self::TYPE_BOOL
            ),
            'retry' => array(
                'type' => self::TYPE_INT
            ),
            'url' => array(
                'type' => self::TYPE_STRING
            ),
            'payload' => array(
                'type' => self::TYPE_STRING
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
     * @param int $executed
     * @return void
     */
    public static function insertQueue($webhook, $payload, $executed = 0)
    {
        return Db::getInstance()->insert(self::$definition["table"], array(
            'id_webhook' => (int) $webhook['id_webhook'],
            'executed' => (int) $executed,
            'url' => pSQL($webhook['url']),
            'payload' => pSQL(Tools::jsonEncode($payload)),
            'date_add' => date('Y-m-d H:i:s')
        ));
    }

    /**
     * @return mixed
     */
    public static function getAllActiveAndNonExecuted()
    {
        $sql = "SELECT q.*
            FROM `" . _DB_PREFIX_ . self::$definition["table"] . "` AS q
            LEFT JOIN `" . _DB_PREFIX_ . "webhooks` AS w ON w.id_webhook = q.id_webhook
            WHERE q.executed = 0 AND q.retry <= w.retries AND w.active = 1";

        return Db::getInstance()->executeS($sql);
    }

    /**
     * @param int $id_queue
     * @return mixed
     */
    public static function markExecuted($id_queue)
    {
        $sql = "UPDATE `" . _DB_PREFIX_ . self::$definition["table"] . "`
            SET executed = 1
            WHERE " . self::$definition["primary"] . " = '" . (int) $id_queue . "'";
        return Db::getInstance()->execute($sql);
    }

    /**
     * @param int $id_queue
     * @return mixed
     */
    public static function incrementRetry($id_queue)
    {
        $sql = "UPDATE `" . _DB_PREFIX_ . self::$definition["table"] . "`
            SET retry = retry + 1
            WHERE " . self::$definition["primary"] . " = '" . (int) $id_queue . "'";
        return Db::getInstance()->execute($sql);
    }

    /**
     * @return int
     */
    public static function getQueuesTotal()
    {
        $query = new DbQuery();
        $query->select('COUNT(*)')
            ->from(self::$definition["table"]);

        return Db::getInstance()->getValue($query);
    }

    /**
     * @return mixed
     */
    public static function getQueues($page = 1, $pagination = 50)
    {
        $query = new DbQuery();
        $query->select('*')
            ->from(self::$definition["table"])
            ->orderBy(self::$definition["primary"] . ' DESC')
            ->limit($pagination, ($page - 1) * $pagination);

        return Db::getInstance()->ExecuteS($query);
    }

    /**
     * @param int $id_queue
     */
    public static function deleteById($id_queue)
    {
        Db::getInstance()->delete(
            self::$definition["table"],
            self::$definition["primary"] . ' = ' . (int) $id_queue,
            1
        );
    }

    /**
     * @param int $id_webhook
     */
    public static function deleteByWebhookId($id_webhook)
    {
        Db::getInstance()->delete(self::$definition["table"], 'id_webhook' . ' = '. (int) $id_webhook);
    }

    /**
     * @param int $id_queue
     * @return mixed
     */
    public static function getById($id_queue)
    {
        $sql = "SELECT * FROM `" . _DB_PREFIX_ . self::$definition["table"] . "`
            WHERE id_queue = '" . (int) $id_queue . "'";
        return Db::getInstance()->getRow($sql);
    }
}
