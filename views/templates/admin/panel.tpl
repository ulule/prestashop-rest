{*
* 2007-2021 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2021 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div class="panel">
	<h3><i class="icon icon-cog"></i> {l s='Webhooks Integration' mod='webhooks'}</h3>
	<div>
		<a href="{$create_webhook_url|escape:'html':'UTF-8'}" class="btn btn-default pull-right">
			<i class="process-icon-save"></i> {l s='New Webhook' mod='webhooks'}
		</a>
		<a class="btn btn-default pull-right settings">
			<i class="process-icon-cogs"></i> {l s='Settings' mod='webhooks'}
		</a>
		<p>{l s='Welcome to Webhooks Integration. Please refer to the manual for any doubts' mod='webhooks'}</p>
	</div>
	<div class="more-info">
		<h3>{l s='Settings' mod='webhooks'}</h3>
		<p>{l s='Please use the following data to configure your Cronjob (without the Cronjob set you will not be able to automatically retry the failed requests).' mod='webhooks'}</p>
		<p><strong>{l s='Cron Job schedule:' mod='webhooks'}</strong> <pre>(*/5 * * * *)</pre></p>
		<p><strong>{l s='Cron Job command:' mod='webhooks'}</strong> <pre>curl "{$cronjob_url|escape:'html':'UTF-8'}"</pre></p>
		<p><a href="https://documentation.cpanel.net/display/78Docs/Cron+Jobs">{l s='Check this link for more for information on CPanel' mod='webhooks'}</a></p>
	</div>
</div>