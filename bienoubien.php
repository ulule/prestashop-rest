<?php
/**
 * BINSHOPS REST API
 *
 * @author BINSHOPS | Best In Shops
 * @copyright BINSHOPS | Best In Shops
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * Best In Shops eCommerce Solutions Inc.
 *
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/classes/APIRoutes.php';
require_once(dirname(__FILE__) . '/vendor/autoload.php');

class Bienoubien extends PaymentModule
{
    protected $config_form = false;

    //webhook
    protected $form_success = array();
    protected $form_warning = array();
    protected $form_error = array();

    private $hooks = array(
        'actionProductAdd' => 'Product Added',
        'actionProductUpdate' => 'Product Updated',
        'actionProductDelete' => 'Product Deleted',
        'actionOrderHistoryAddAfter' => 'Order status updated',
        'actionValidateOrder' => 'Order created',
        'actionCustomerAccountAdd' => 'Customer created',
        'actionCustomerAccountUpdate' => 'Customer updated',
        'actionObjectCustomerMessageAddAfter' => 'Customer message added',
        'actionCronJob' => '',
        'displayBackOfficeHeader' => '',
    );

    public function __construct()
    {
        $this->name = 'bienoubien';
        $this->tab = 'merchandizing';
        $this->version = '1.0.8';
        $this->author = 'Binshops';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Bien ou Bien');
        $this->description = $this->l('Ce module permet de connecter votre boutique à la place de marché Bien ou Bien');

        $this->confirmUninstall = $this->l('');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('BINSHOPSREST_LIVE_MODE', false);

        include(dirname(__FILE__) . '/sql/install.php');

        //webhook
        $webhook_secure_key = Configuration::get('WEBHOOKS_SECURE_KEY');
        $cron_secure_key = Configuration::get('WEBHOOKS_CRON_SECURE_KEY');
        if (false === $webhook_secure_key) {
            Configuration::updateValue(
                'WEBHOOKS_SECURE_KEY',
                Tools::strtoupper(Tools::passwdGen(32))
            );
        }
        if (false === $cron_secure_key) {
            Configuration::updateValue(
                'WEBHOOKS_CRON_SECURE_KEY',
                Tools::strtoupper(Tools::passwdGen(32))
            );
        }


        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') && $this->registerHook('moduleRoutes') &&
            $this->registerHook(array_keys($this->hooks));
    }

    public function uninstall()
    {
        Configuration::deleteByName('BINSHOPSREST_LIVE_MODE');

        include(dirname(__FILE__) . '/sql/uninstall.php');

        //webhook
        Configuration::deleteByName('WEBHOOKS_SECURE_KEY');
        Configuration::deleteByName('WEBHOOKS_CRON_SECURE_KEY');

        foreach ($this->hooks as $hook => $name) {
            $this->unregisterHook($hook);
        }

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitBinshopsrestModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        //webhook
        // Decode messages in the URL
        $messages = Tools::getValue('messages', false);
        if ($messages) {
            $this->unserializeMessages($messages);
        }

        // Create & update Webhook
        if (((bool) Tools::isSubmit('submitWebhooksModule')) == true) {
            $id_webhook = (int) Tools::getValue('id_webhook');

            if ($id_webhook === 0) {
                $this->createNewWebhook();
            } else {
                $this->updateWebhook($id_webhook);
            }
        }

        // Delete Webhook
        if (((bool) Tools::isSubmit('deleteWebhook')) == true) {
            WebhookLogModel::deleteByWebhookId((int) Tools::getValue('id_webhook'));
            WebhookQueueModel::deleteByWebhookId((int) Tools::getValue('id_webhook'));
            WebhookModel::deleteById((int) Tools::getValue('id_webhook'));
            $this->setSuccessMessage($this->l('Webhook was deleted.'));
            return $this->redirectHome();
        }

        // Toggle Webhook
        if (((bool) Tools::isSubmit('toggleWebhook')) == true) {
            WebhookModel::changeWebhookStatus((int) Tools::getValue('id_webhook'));
            $this->setSuccessMessage($this->l('Webhook status was changed.'));
            return $this->redirectHome();
        }

        // Delete Queue
        if (((bool) Tools::isSubmit('deleteQueue')) == true) {
            WebhookQueueModel::deleteById((int) Tools::getValue('id_queue'));
            $this->setSuccessMessage($this->l('Queued webhook was deleted.'));
            return $this->redirectHome();
        }

        // Delete Log
        if (((bool) Tools::isSubmit('deleteLog')) == true) {
            WebhookLogModel::deleteById((int) Tools::getValue('id_log'));
            $this->setSuccessMessage($this->l('Log entry was deleted.'));
            return $this->redirectHome();
        }

        // View Queue
        if (((bool) Tools::isSubmit('viewQueue')) == true) {
            $id_queue = (int) Tools::getValue('id_queue');
            return $this->renderQueueView($id_queue);
        }

        // View Log
        if (((bool) Tools::isSubmit('viewLog')) == true) {
            $id_log = (int) Tools::getValue('id_log');
            return $this->renderLogView($id_log);
        }

        // Resend Queue
        if (((bool) Tools::isSubmit('resendQueue')) == true) {
            return $this->resendQueue();
        }

        $create_webhook_url = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&token=' .
            Tools::getAdminTokenLite('AdminModules') . '&createWebhook';

        $cronjob_url = $this->context->link->getModuleLink(
            $this->name,
            'cron',
            array('secure_key' => Configuration::get('WEBHOOKS_CRON_SECURE_KEY'))
        );

        $this->context->smarty->assign('module_dir', $this->_path);

        // Messages
        $this->context->smarty->assign('form_success', $this->form_success);
        $this->context->smarty->assign('form_warning', $this->form_warning);
        $this->context->smarty->assign('form_error', $this->form_error);

        // URLS
        $this->context->smarty->assign('create_webhook_url', $create_webhook_url);
        $this->context->smarty->assign('cronjob_url', $cronjob_url);

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/base.tpl');

        if (((bool) Tools::isSubmit('createWebhook')) == true ||
            ((bool) Tools::isSubmit('updateWebhook')) == true ||
            ((bool) Tools::isSubmit('submitWebhooksModule')) == true
        ) {
            $output .= $this->renderCreateWebhookForm();
        } else {
            $output .= $this->context->smarty->fetch($this->local_path . 'views/templates/admin/panel.tpl');
            $output .= $this->renderWebhooksList();
            $output .= $this->renderQueuesList();
            $output .= $this->renderLogsList();
        }

        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitBinshopsrestModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon-key"></i>',
                        'desc' => $this->l('Copy this token and send it as request parameter in your requests'),
                        'name' => 'BINSHOPSREST_API_TOKEN',
                        'label' => $this->l('API Token'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Generate'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'BINSHOPSREST_LIVE_MODE' => Configuration::get('BINSHOPSREST_LIVE_MODE', true),
            'BINSHOPSREST_API_TOKEN' => Configuration::get('BINSHOPSREST_API_TOKEN', 'contact@prestashop.com'),
            'BINSHOPSREST_ACCOUNT_PASSWORD' => Configuration::get('BINSHOPSREST_ACCOUNT_PASSWORD', null),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $token = $this->generateToken();
        Configuration::updateValue('BINSHOPSREST_API_TOKEN', $token);

//        $form_values = $this->getConfigFormValues();
//
//        foreach (array_keys($form_values) as $key) {
//            Configuration::updateValue($key, Tools::getValue($key));
//        }
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path . 'views/js/back.js');
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        }

        if (Tools::getValue('controller') == 'AdminModules' &&
            (Tools::getValue('configure') == $this->name || Tools::getValue('module_name') == $this->name)) {
            $this->context->controller->addJquery();
            $this->context->controller->addJS($this->_path . 'views/js/back.js');
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');
    }

    public function hookModuleRoutes()
    {
        return APIRoutes::getRoutes();
    }

    public function generateToken(){
        return $this->random_str(32);
    }

    function random_str(
        int $length = 64,
        string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
    ): string {
        if ($length < 1) {
            throw new \RangeException("Length must be a positive integer");
        }
        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces []= $keyspace[random_int(0, $max)];
        }
        return implode('', $pieces);
    }

    /**
     * ====================================================================================
     * ====================================================================================
     * Web Hook start
     * ====================================================================================
     * ====================================================================================
     */

    /** ------------------------------------------------------------------------ */
    /** VIEWS                                                                    */
    /** ------------------------------------------------------------------------ */

    /**
     * @param int $id_queue
     * @return mixed
     */
    protected function renderQueueView($id_queue)
    {
        $queue = WebhookQueueModel::getById((int) $id_queue);
        $this->smarty->assign($queue);
        return $this->display($this->local_path, 'views/templates/admin/queue/view.tpl');
    }

    /**
     * @param int $id_queue
     * @return mixed
     */
    protected function renderLogView($id_queue)
    {
        $log = WebhookLogModel::getById((int) $id_queue);
        $this->smarty->assign($log);
        return $this->display($this->local_path, 'views/templates/admin/log/view.tpl');
    }

    /** ------------------------------------------------------------------------ */
    /** LISTS                                                                    */
    /** ------------------------------------------------------------------------ */

    /**
     * Renders webhooks list
     * @return mixed
     */
    protected function renderWebhooksList()
    {
        $fields_list = array(

            'id_webhook' => array(
                'title' => $this->l('ID'),
                'search' => false,
            ),
            'hook' => array(
                'title' => $this->l('Hook'),
                'search' => false,
            ),
            'url' => array(
                'title' => $this->l('URL'),
                'search' => false,
            ),
            'real_time' => array(
                'title' => $this->l('Real-time'),
                'search' => false,
                'type' => 'bool',
            ),
            'retries' => array(
                'title' => $this->l('Retries'),
                'search' => false,
            ),
            'active' => array(
                'title' => $this->l('Status'),
                'search' => false,
                'type' => 'bool',
                'align' => 'center',
                'icon' => array(
                    0 => 'disabled.gif',
                    1 => 'enabled.gif',
                    'default' => 'disabled.gif'
                ),
            ),
            'date_add' => array(
                'title' => $this->l('Created at'),
                'type' => 'datetime',
                'search' => false,
            ),
        );

        if (!Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE')) {
            unset($fields_list['shop_name']);
        }

        $helper_list = new HelperList();
        $helper_list->module = $this;
        $helper_list->title = $this->l('Webhooks');
        $helper_list->no_link = true;
        $helper_list->show_toolbar = true;
        $helper_list->simple_header = false;
        $helper_list->identifier = 'id_webhook';
        $helper_list->table = 'Webhook';
        $helper_list->currentIndex = $this->context->link->getAdminLink('AdminModules', false) .
            '&configure=' . $this->name;
        $helper_list->token = Tools::getAdminTokenLite('AdminModules');
        $helper_list->actions = array('edit', 'toggleStatus', 'delete');
        $helper_list->shopLinkType = '';

        // This is needed for displayEnableLink to avoid code duplication
        $this->_helperlist = $helper_list;

        /* Retrieve list data */
        $helper_list->listTotal = WebhookModel::getWebhooksTotal();

        /* Paginate the result */
        $page = ($page = Tools::getValue('submitFilter' . $helper_list->table)) ? $page : 1;
        $pagination = ($pagination = Tools::getValue($helper_list->table . '_pagination')) ? $pagination : 50;

        $logs = WebhookModel::getWebhooks($page, $pagination);

        return $helper_list->generateList($logs, $fields_list);
    }

    /**
     * Renders webhooks list
     * @return mixed
     */
    protected function renderQueuesList()
    {
        $fields_list = array(

            'id_queue' => array(
                'title' => $this->l('Queue ID'),
                'search' => false,
            ),
            'id_webhook' => array(
                'title' => $this->l('Webhook ID'),
                'search' => false,
            ),
            'url' => array(
                'title' => $this->l('URL'),
                'search' => false,
            ),
            'executed' => array(
                'title' => $this->l('Executed'),
                'search' => false,
                'type' => 'bool',
                'align' => 'center',
                'icon' => array(
                    0 => 'disabled.gif',
                    1 => 'enabled.gif',
                    'default' => 'disabled.gif'
                ),
            ),
            'retry' => array(
                'title' => $this->l('Retries'),
                'search' => false,
            ),
            'date_add' => array(
                'title' => $this->l('Queued at'),
                'type' => 'datetime',
                'search' => false,
            ),
        );

        if (!Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE')) {
            unset($fields_list['shop_name']);
        }

        $helper_list = new HelperList();
        $helper_list->module = $this;
        $helper_list->title = $this->l('Queued webhooks');
        $helper_list->no_link = true;
        $helper_list->show_toolbar = true;
        $helper_list->simple_header = false;
        $helper_list->identifier = 'id_queue';
        $helper_list->table = 'Queue';
        $helper_list->currentIndex = $this->context->link->getAdminLink('AdminModules', false) .
            '&configure=' . $this->name;
        $helper_list->token = Tools::getAdminTokenLite('AdminModules');
        $helper_list->actions = array('view', 'resend', 'delete');
        $helper_list->shopLinkType = '';


        // This is needed for displayEnableLink to avoid code duplication
        $this->_helperlist = $helper_list;

        /* Retrieve list data */
        $helper_list->listTotal = WebhookQueueModel::getQueuesTotal();

        if ($helper_list->listTotal === 0) {
            return "";
        }

        /* Paginate the result */
        $page = ($page = Tools::getValue('submitFilter' . $helper_list->table)) ? $page : 1;
        $pagination = ($pagination = Tools::getValue($helper_list->table . '_pagination')) ? $pagination : 50;

        $logs = WebhookQueueModel::getQueues($page, $pagination);

        return $helper_list->generateList($logs, $fields_list);
    }

    /**
     * Renders the logs list
     * @return mixed
     */
    protected function renderLogsList()
    {
        $fields_list = array(

            'id_log' => array(
                'title' => $this->l('ID'),
                'search' => false,
            ),
            'id_webhook' => array(
                'title' => $this->l('Webhook ID'),
                'search' => false,
            ),
            'real_time' => array(
                'title' => $this->l('Real-time'),
                'search' => false,
                'type' => 'bool',
            ),
            'url' => array(
                'title' => $this->l('URL'),
                'search' => false,
            ),
            'status_code' => array(
                'title' => $this->l('Status'),
                'search' => false,
            ),
            'date_add' => array(
                'title' => $this->l('Created at'),
                'type' => 'datetime',
                'search' => false,
            ),
        );

        if (!Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE')) {
            unset($fields_list['shop_name']);
        }

        $helper_list = new HelperList();
        $helper_list->module = $this;
        $helper_list->title = $this->l('Execution Logs');
        $helper_list->no_link = true;
        $helper_list->show_toolbar = true;
        $helper_list->simple_header = false;
        $helper_list->identifier = 'id_log';
        $helper_list->table = 'Log';
        $helper_list->currentIndex = $this->context->link->getAdminLink('AdminModules', false) .
            '&configure=' . $this->name;
        $helper_list->token = Tools::getAdminTokenLite('AdminModules');
        $helper_list->actions = array('view', 'delete');
        $helper_list->shopLinkType = '';

        // This is needed for displayEnableLink to avoid code duplication
        $this->_helperlist = $helper_list;

        /* Retrieve list data */
        $helper_list->listTotal = WebhookLogModel::getLogsTotal();

        if ($helper_list->listTotal === 0) {
            return "";
        }

        /* Paginate the result */
        $page = ($page = Tools::getValue('submitFilter' . $helper_list->table)) ? $page : 1;
        $pagination = ($pagination = Tools::getValue($helper_list->table . '_pagination')) ? $pagination : 50;

        $logs = WebhookLogModel::getLogs($page, $pagination);

        return $helper_list->generateList($logs, $fields_list);
    }

    /**
     * Resend link in Queues
     * @param null $token
     * @param int $id
     * @param null $name
     * @return mixed
     */
    public function displayResendLink($token = null, $id = -1, $name = null)
    {
        $query = http_build_query(array(
            'configure' => $this->name,
            'id_queue' => (int) $id,
            'token' => $token,
            'resendQueue' => true,
        ));

        $this->context->smarty->assign(array(
            'location_ok' => $this->context->link->getAdminLink('AdminModules', false) . '&' . $query,
            'location_ko' => 'javascript:void(0)',
            'action' => $this->l('Resend'),
            'confirm' => $this->l('Are you sure you want to re-send this webhook?'),
        ));

        return $this->display($this->local_path, 'views/templates/admin/actions/resend.tpl');
    }

    /**
     * Toggle status of webhooks
     * @param null $token
     * @param int $id
     * @param null $name
     * @return mixed
     */
    public function displayToggleStatusLink($token = null, $id = -1, $name = null)
    {
        $query = http_build_query(array(
            'configure' => $this->name,
            'id_webhook' => (int) $id,
            'token' => $token,
            'toggleWebhook' => true,
        ));

        $this->context->smarty->assign(array(
            'location_ok' => $this->context->link->getAdminLink('AdminModules', false) . '&' . $query,
            'location_ko' => 'javascript:void(0)',
            'action' => $this->l('Toggle status'),
            'confirm' => $this->l('Are you sure you want to change this webhook status?'),
        ));

        return $this->display($this->local_path, 'views/templates/admin/actions/toggle.tpl');
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderCreateWebhookForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitWebhooksModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValuesWebhook(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigFormWebhook()));
    }


    /**
     * Creates a new webhook
     */
    protected function createNewWebhook()
    {
        // Sanitize URL
        $url = filter_var(Tools::getValue('WEBHOOKS_URL'), FILTER_SANITIZE_URL);

        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            $this->setErrorMessage($this->l('The URL is invalid'));
        } elseif (!Validate::isHookName(Tools::getValue('WEBHOOKS_HOOK'))) {
            $this->setErrorMessage($this->l('Hook name is invalid'));
        } elseif (!Validate::isUnsignedInt(Tools::getValue('WEBHOOKS_RETRIES')) ||
            ((int) Tools::getValue('WEBHOOKS_RETRIES')) > 99) {
            $this->setErrorMessage($this->l('Please choose a retry number between 0 and 99'));
        } else {
            WebhookModel::insertWebhook(
                $url,
                Tools::getValue('WEBHOOKS_HOOK'),
                Tools::getValue('WEBHOOKS_REAL_TIME', 0),
                (int)Tools::getValue('WEBHOOKS_RETRIES'),
                1
            );

            $this->setSuccessMessage($this->l('Webhook inserted'));

            return $this->redirectHome();
        }
    }

    /**
     * Updates a webhook
     * @param $id_webhook
     * @return mixed
     */
    protected function updateWebhook($id_webhook)
    {
        // Sanitize URL
        $url = filter_var(Tools::getValue('WEBHOOKS_URL'), FILTER_SANITIZE_URL);

        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            $this->setErrorMessage($this->l('The URL is invalid'));
        } elseif (!Validate::isHookName(Tools::getValue('WEBHOOKS_HOOK'))) {
            $this->setErrorMessage($this->l('Hook name is invalid'));
        } elseif (!Validate::isUnsignedInt(Tools::getValue('WEBHOOKS_RETRIES')) ||
            ((int) Tools::getValue('WEBHOOKS_RETRIES')) > 99) {
            $this->setErrorMessage($this->l('Please choose a retry number between 0 and 99'));
        } else {
            WebhookModel::updateWebhook(
                $id_webhook,
                $url,
                Tools::getValue('WEBHOOKS_HOOK'),
                Tools::getValue('WEBHOOKS_REAL_TIME', 0),
                (int)Tools::getValue('WEBHOOKS_RETRIES')
            );

            $this->setSuccessMessage($this->l('Webhook updated'));

            return $this->redirectHome();
        }
    }

    /**
     * @return mixed
     */
    protected function resendQueue()
    {
        $id_queue = (int) Tools::getValue('id_queue');
        $queue = WebhookQueueModel::getById($id_queue);
        $webhook = WebhookModel::getById((int) $queue['id_webhook']);
        $payload = Tools::jsonDecode($queue['payload']);

        WebhookQueueModel::incrementRetry($id_queue);

        try {
            $this->makeRequest($webhook, $payload);
            $this->setSuccessMessage($this->l('Webhook was executed!'));
        } catch (Exception $e) {
            $this->setErrorMessage($this->l('There was an error executing the Webhook! ' . $e->getMessage()));
        }

        return $this->redirectHome();
    }

    /** ------------------------------------------------------------------------ */
    /** REQUESTS & QUEUEING                                                      */
    /** ------------------------------------------------------------------------ */

    /**
     * Actually makes the webhook request
     * @param WebhookModel $webhook
     * @param mixed $payload
     * @throws Exception
     * @return void
     */
    private function makeRequest($webhook, $payload)
    {
        $response = \Httpful\Request::post($webhook['url'])
            ->withoutStrictSsl()
            ->withXSecureKey(Configuration::get('WEBHOOKS_SECURE_KEY'))
            ->withXHook($webhook['hook'])
            ->withXHookId($webhook['id_webhook'])
            ->sendsJson()
            ->body(Tools::jsonEncode($payload))
            ->send();

        WebhookLogModel::insertLog($webhook, $payload, $response->body, $response->code);

        // 200; 201; 202; 203; 204
        if ($response->code < 200 || $response->code > 204) {
            throw new Exception("Error: expected HTTP 2XX status code response but got {$response->code}.");
        }
    }

    /**
     * Fires the webhook and logs or queue if it fails
     *
     * @param WebhookModel $webhook
     * @param mixed $payload
     * @return void
     * @throws Exception
     */
    private function fireWebhook($webhook, $payload)
    {
        try {
            $this->makeRequest($webhook, $payload);
        } catch (Exception $e) {
            $this->queueWebhook($webhook, $payload);
            // PrestaShopLogger::addLog(var_export($e, true), 1);
        }
    }

    /**
     * Dispatch the execution of the webhook depending on the type of webhook
     *
     * @param WebhookModel $webhook
     * @param mixed $payload
     * @throws Exception
     */
    private function dispatchWebhook($webhook, $payload)
    {
        PrestaShopLogger::addLog(
            'dispatchWebhook ' . $webhook['hook'] . ' with ID ' . $webhook['id_webhook'],
            1
        );

        $payload = $this->decoratePayload($webhook['hook'], $payload);

        if ($webhook['real_time']) {
            $this->fireWebhook($webhook, $payload);
        } else {
            $this->queueWebhook($webhook, $payload);
        }
    }

    /**
     * Queues the webhook in the DB for later
     *
     * @param WebhookModel $webhook
     * @param mixed $payload
     * @return void
     */
    private function queueWebhook($webhook, $payload)
    {
        WebhookQueueModel::insertQueue($webhook, $payload);
    }


    /**
     * Decorate payload and transform into stdClass
     *
     * @param string $hook
     * @param mixed $payload
     * @throws Exception
     * @return mixed
     */
    private function decoratePayload($hook, $payload)
    {
        // Transform payload into stdClass
        $payload = Tools::jsonDecode(Tools::jsonEncode($payload));

        if (WebhookDecorator::hasDecorator($hook)) {
            try {
                $decoratorClass = WebhookDecorator::getDecoratorClass($hook);
                $decoratorValue = WebhookDecorator::getDecoratorValue($hook, $payload);

                $enhancer = new $decoratorClass($decoratorValue, $payload);
                $payload = $enhancer->present();

                return $payload;
            } catch (Exception $e) {
                PrestaShopLogger::addLog(var_export($e, true), 1);
            }
        }

        return $payload;
    }

    /**
     * Prestashop own function for cron frequency
     *
     * @return array
     */
    public function getCronFrequency()
    {
        return array(
            'hour' => '-1',
            'day' => '-1',
            'month' => '-1',
            'day_of_week' => '-1'
        );
    }

    /** ------------------------------------------------------------------------ */
    /** ALL HOOKS BELLOW                                                         */
    /** ------------------------------------------------------------------------ */

    public function hookActionValidateOrder($params)
    {
        $webhooks = WebhookModel::getWebhooksByHook('actionValidateOrder');

        foreach ($webhooks as $webhook) {
            $this->dispatchWebhook($webhook, $params);
        }
    }

    public function hookActionOrderHistoryAddAfter($params)
    {
        $webhooks = WebhookModel::getWebhooksByHook('actionOrderHistoryAddAfter');

        foreach ($webhooks as $webhook) {
            $this->dispatchWebhook($webhook, $params);
        }
    }

    public function hookActionProductAdded($params)
    {
        $webhooks = WebhookModel::getWebhooksByHook('actionProductAdd');

        foreach ($webhooks as $webhook) {
            $this->dispatchWebhook($webhook, $params);
        }
    }

    public function hookActionProductUpdate($params)
    {
        $webhooks = WebhookModel::getWebhooksByHook('actionProductUpdate');

        foreach ($webhooks as $webhook) {
            $this->dispatchWebhook($webhook, $params);
        }
    }

    public function hookActionProductDelete($params)
    {
        $webhooks = WebhookModel::getWebhooksByHook('actionProductDelete');

        foreach ($webhooks as $webhook) {
            $this->dispatchWebhook($webhook, $params);
        }
    }

    public function hookActionCustomerAccountAdd($params)
    {
        $webhooks = WebhookModel::getWebhooksByHook('actionCustomerAccountAdd');

        foreach ($webhooks as $webhook) {
            $this->dispatchWebhook($webhook, $params);
        }
    }

    public function hookActionCustomerAccountUpdate($params)
    {
        $webhooks = WebhookModel::getWebhooksByHook('actionCustomerAccountUpdate');

        foreach ($webhooks as $webhook) {
            $this->dispatchWebhook($webhook, $params);
        }
    }

    public function hookActionObjectCustomerMessageAddAfter($params)
    {
        $webhooks = WebhookModel::getWebhooksByHook('actionObjectCustomerMessageAddAfter');

        foreach ($webhooks as $webhook) {
            $this->dispatchWebhook($webhook, $params);
        }
    }

    public function hookActionCronJob($limit)
    {
        $webhooks = array();
        if (Module::isInstalled($this->name)) {
            $queued = WebhookQueueModel::getAllActiveAndNonExecuted();
            $i = 0;
            foreach ($queued as $queue) {
                $webhook = WebhookModel::getById($queue['id_webhook']);
                $payload = Tools::jsonDecode($queue['payload']);
                array_push($webhooks, $queue);
                WebhookQueueModel::incrementRetry($queue['id_queue']);
                try {
                    $this->makeRequest($webhook, $payload);
                    WebhookQueueModel::markExecuted($queue['id_queue']);
                } catch (Exception $e) {
                    PrestaShopLogger::addLog(var_export($e, true), 1);
                }
                $i++;
                if ($i >= $limit) {
                    break;
                }
            }
        }
        return $webhooks;
    }

    /** ------------------------------------------------------------------------ */
    /** MESSAGES & STUFF                                                         */
    /** ------------------------------------------------------------------------ */

    /**
     * @param $message
     */
    protected function setErrorMessage($message)
    {
        $this->form_error[] = $message;
    }

    /**
     * @param $message
     */
    protected function setSuccessMessage($message)
    {
        $this->form_success[] = $message;
    }

    /**
     * @param $message
     */
    protected function setWarningMessage($message)
    {
        $this->form_warning[] = $message;
    }

    /**
     * Serializer messages for redirect
     * @return string
     */
    protected function getSerializedMessages()
    {
        return urlencode(serialize(array(
            'e' => $this->form_error,
            's' => $this->form_success,
            'w' => $this->form_warning,
        )));
    }

    /**
     * Unserialize Messages from URL to local vars
     * @param $serialized_message
     */
    protected function unserializeMessages($serialized_message)
    {
        $messages = unserialize(urldecode($serialized_message));

        $this->form_error = array_merge($this->form_error, $messages['e']);
        $this->form_success = array_merge($this->form_success, $messages['s']);
        $this->form_warning = array_merge($this->form_warning, $messages['w']);
    }

    /**
     * Return home
     */
    protected function redirectHome()
    {
        return Tools::redirectAdmin(
            Context::getContext()->link->getAdminLink('AdminModules') . '&' .
            http_build_query(array('configure' => $this->name, 'messages' => $this->getSerializedMessages()))
        );
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigFormWebhook()
    {
        $options = array();

        foreach ($this->hooks as $hook => $name) {
            if (!empty($name)) {
                $options[] = array(
                    'id_option' => $hook,
                    'name' => $name,
                );
            }
        }

        $is_update = (int) Tools::getValue('id_webhook', 0);

        $this->context->smarty->assign(array(
            'webhooks_retries' => (int) Tools::getValue('WEBHOOKS_RETRIES'),
        ));

        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l($is_update ? 'Update Webhook' : 'New Webhook'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'hidden',
                        'name' => 'id_webhook'
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-link"></i>',
                        'desc' => $this->l('Enter a valid URL address to POST the webhook'),
                        'name' => 'WEBHOOKS_URL',
                        'required' => true,
                        'label' => $this->l('URL'),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Hook event'),
                        'desc' => $this->l('Choose an event to trigger'),
                        'name' => 'WEBHOOKS_HOOK',
                        'required' => true,
                        'options' => array(
                            'query' => $options,
                            'id' => 'id_option',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'col' => 2,
                        'type' => 'html',
                        'desc' => $this->l('Retry this times before stopping (min: 0, max: 99)'),
                        'name' => 'WEBHOOKS_RETRIES',
                        'required' => true,
                        'label' => $this->l('Retries'),
                        'html_content' =>
                            $this->display($this->local_path, 'views/templates/admin/inputs/retries_input.tpl'),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Real time'),
                        'name' => 'WEBHOOKS_REAL_TIME',
                        'is_bool' => true,
                        'required' => true,
                        'desc' => $this->l('Use this webhook in real time mode'),
                        'value' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l($is_update ? 'Update webhook' : 'Save webhook'),
                ),
                'buttons' => array(
                    'go-back' => array(
                        'title' => $this->l('Go back'),
                        'name' => 'goBack',
                        'class' => 'btn btn-default pull-right',
                        'icon' => 'process-icon-back',
                    ),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValuesWebhook()
    {
        $webhook = WebhookModel::getById((int) Tools::getValue('id_webhook'));

        if (!$webhook) {
            return array(
                'id_webhook' => 0,
                'WEBHOOKS_URL' => Tools::getValue('WEBHOOKS_URL'),
                'WEBHOOKS_HOOK' => Tools::getValue('WEBHOOKS_HOOK'),
                'WEBHOOKS_RETRIES' => (int)Tools::getValue('WEBHOOKS_RETRIES'),
                'WEBHOOKS_REAL_TIME' => (int)Tools::getValue('WEBHOOKS_REAL_TIME'),
            );
        }

        // Small hack
        $_GET['WEBHOOKS_RETRIES'] = (int) $webhook['retries'];

        return array(
            'id_webhook' => $webhook['id_webhook'],
            'WEBHOOKS_URL' => $webhook['url'],
            'WEBHOOKS_HOOK' => $webhook['hook'],
            'WEBHOOKS_REAL_TIME' => (int) $webhook['real_time'],
        );
    }
}
