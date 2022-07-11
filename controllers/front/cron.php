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

class BinshopsrestCronModuleFrontController extends ModuleFrontController
{
    /** @var bool If set to true, will be redirected to authentication page */
    public $auth = false;

    /** @var bool */
    public $ajax;

    public function display()
    {
        $this->ajax = 1;

        if (Tools::getIsset('secure_key')) {
            $secure_key = Configuration::get('WEBHOOKS_CRON_SECURE_KEY');
            if (!empty($secure_key) && Tools::getValue('secure_key') === $secure_key) {
                $webhooks = Module::getInstanceByName('webhooks');
                if ($webhooks->active) {
                    $webhooks->hookActionCronJob();
                    $this->ajaxDie("SUCCESS\n");
                } else {
                    $this->ajaxDie("ERROR: Webhooks module is not active.\n");
                }
            } else {
                $this->ajaxDie("ERROR: Wrong secure key.\n");
            }
        } else {
            $this->ajaxDie("ERROR: No secure key.\n");
        }
    }
}
