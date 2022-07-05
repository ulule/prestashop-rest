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

// Call it in the command-line with:
// $ php modules/webhooks/cron.php SECURE_KEY
// SECURE_KEY = the secure key in your module.

$_GET['fc'] = 'module';
$_GET['module'] = 'webhooks';
$_GET['controller'] = 'cron';
$_GET['secure_key'] = isset($argv[1]) ? $argv[1] : null;

require_once dirname(__FILE__) . '/../../index.php';
