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

<script type="text/javascript">
    window.onload = function() {
        syntaxHighlight('payload');
        syntaxHighlight('response');
    }
</script>

<div class="panel">
    <h3><i class="icon icon-cog"></i> {l s='Log ID: %d' sprintf=$id_log|intval mod='webhooks'}</h3>
    <button type="button" class="btn btn-default btn btn-default pull-right" name="goBack">
        <i class="process-icon-back"></i> {l s='Go back' mod='webhooks'}
    </button>
    <p><strong>{l s='DATE' mod='webhooks'}:</strong> <code>{$date_add|escape:'html':'UTF-8'}</code></p>
    <p><strong>{l s='POST URL' mod='webhooks'}:</strong> <code>{$url|escape:'html':'UTF-8'}</code></p>
    <p><strong>{l s='REAL TIME' mod='webhooks'}:</strong> <code>{if ($real_time)}{l s='YES' mod='webhooks'}{else}{l s='NO' mod='webhooks'}{/if}</code></p>

    <p><strong>{l s='STATUS' mod='webhooks'}:</strong> <code>{$status_code|escape:'html':'UTF-8'}</code></p>
    <p>
        <strong>{l s='PAYLOAD' mod='webhooks'}:</strong>
        <pre id="payload">{$payload|escape:'html':'UTF-8'}</pre>
    </p>
    <p>
        <strong>{l s='RESPONSE' mod='webhooks'}:</strong>
        <pre id="response">{$response|escape:'html':'UTF-8'}</pre>
    </p>
</div>