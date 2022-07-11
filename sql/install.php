<?php
/**
 * BINSHOPS | Best In Shops
 *
 * @author BINSHOPS | Best In Shops
 * @copyright BINSHOPS | Best In Shops
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * Best In Shops eCommerce Solutions Inc.
 *
 */

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'binshopsrest_reset_pass_tokens` (
    `id_pass_tokens` int(11) NOT NULL AUTO_INCREMENT,
    `reset_password_token` varchar(255) NOT NULL,
    `reset_password_validity` varchar(255) NOT NULL,
    `id_customer` int(11) NOT NULL,
    `last_token_gen` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY  (`id_pass_tokens`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}

//webhook
$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'webhooks` (
    `id_webhook` int(11) NOT NULL AUTO_INCREMENT,
    `url` VARCHAR(1500) NULL,
    `hook` VARCHAR(500) NULL,
    `real_time` INT(1) NOT NULL DEFAULT 1,
    `retries` INT(3) NOT NULL DEFAULT 5,
    `active` INT(1) NOT NULL DEFAULT 1,
    `date_add` DATETIME NULL,
    PRIMARY KEY  (`id_webhook`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'webhooks_log` (
    `id_log` INT(11) NOT NULL AUTO_INCREMENT,
    `id_webhook` INT(11) NULL,
    `real_time` INT(1) NOT NULL DEFAULT 1,
    `url` VARCHAR(1500) NULL,
    `payload` TEXT NULL,
    `response` TEXT NULL,
    `status_code` INT(3) NOT NULL DEFAULT 200,
    `date_add` DATETIME NULL,
    PRIMARY KEY  (`id_log`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'webhooks_queue` (
    `id_queue` INT(11) NOT NULL AUTO_INCREMENT,
    `id_webhook` INT(11) NULL,
    `executed` INT(1) NOT NULL DEFAULT 0,
    `retry` INT(3) NOT NULL DEFAULT 0,
    `url` VARCHAR(1500) NULL,
    `payload` TEXT NULL,
    `date_add` DATETIME NULL,
    PRIMARY KEY  (`id_queue`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
