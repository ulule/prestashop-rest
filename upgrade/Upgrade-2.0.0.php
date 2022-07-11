<?php
/**
 * 2021 Wild Fortress, Lda
 *
 * NOTICE OF LICENSE
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 *
 * @author    Hélder Duarte <cossou@gmail.com>
 * @copyright 2021 Wild Fortress, Lda
 * @license   Proprietary and confidential
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/*
* File: /upgrade/Upgrade-2.0.0.php
*/
function upgrade_module_2_0_0($module)
{
    return Db::getInstance()->execute("UPDATE `" . _DB_PREFIX_ . "webhooks` SET `hook` = 'actionOrderHistoryAddAfter' WHERE `hook` = 'actionOrderStatusPostUpdate'") &&
        $module->registerHook('actionOrderHistoryAddAfter') &&
        $module->unregisterHook('actionOrderStatusPostUpdate');
}
