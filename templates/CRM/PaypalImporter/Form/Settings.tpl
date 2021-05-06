<div class="crm-block crm-form-block">
    <div class="action-link">
        <a class="button new-option" href="{$reloadPage}"><span><i class="crm-i fa-refresh"></i> {ts}Reload{/ts}</span></a>
    </div>
</div>
<div class="crm-block crm-form-block">
    {ts}Current state: {/ts} {$currentState} {if $currentState == 'error'}{$lastLogError}{/if}
</div>
<div class="crm-block crm-form-block">
    {if isset($lastStatsUser)}<div>{ts}Number of imported users in the last iteration:{/ts} {$lastStatsUser}</div>{/if}
    {if isset($lastStatsTransaction)}<div>{ts}Number of imported transactions in the last iteration:{/ts} {$lastStatsTransaction}</div>{/if}
    <ul>
    {foreach from=$lastStatsErrors key=id item=error}
        <li>{$error}</li>
    {/foreach}
    </ul>
</div>
<div class="crm-block crm-form-block">
    <table class="form-layout">
        <tr>
            <td class="label">{$form.clientId.label}</td>
            <td class="content">{$form.clientId.html}<br/>
                <span class="description">{ts}Client ID of the Paypal application{/ts}</span>
            </td>
        </tr>
        <tr>
            <td class="label">{$form.clientSecret.label}</td>
            <td class="content">{$form.clientSecret.html}<br/>
                <span class="description">{ts}Client secret of the Paypal application{/ts}</span>
            </td>
        </tr>
        <tr>
            <td class="label">{$form.paypalHost.label}</td>
            <td class="content">{$form.paypalHost.html}<br/>
                <span class="description">{ts}Hostname of the paypal environment{/ts}</span>
            </td>
        </tr>
        <tr>
            <td class="label">{$form.startDate.label}</td>
            <td class="content">{$form.startDate.html}<br/>
                <span class="description">{ts}The start_date param of the transaction search{/ts}</span>
            </td>
        </tr>
        <tr>
            <td class="label">{$form.importLimit.label}</td>
            <td class="content">{$form.importLimit.html}<br/>
                <span class="description">{ts}The batch limit of the import process{/ts}</span>
            </td>
        </tr>
        <tr>
            <td class="label">{$form.requestLimit.label}</td>
            <td class="content">{$form.requestLimit.html}<br/>
                <span class="description">{ts}The limit of the requests{/ts}</span>
            </td>
        </tr>
        <tr>
            <td class="label">{$form.paymentInstrumentId.label}</td>
            <td class="content">{$form.paymentInstrumentId.html}<br/>
                <span class="description">{ts}Payment Method{/ts}</span>
            </td>
        </tr>
        <tr>
            <td class="label">{$form.financialTypeId.label}</td>
            <td class="content">{$form.financialTypeId.html}<br/>
                <span class="description">{ts}Financial Type{/ts}</span>
            </td>
        </tr>
        <tr>
            <td class="label">{$form.tagId.label}</td>
            <td class="content">{$form.tagId.html}<br/>
                <span class="description">{ts}Optional tag that will be added to the new contacts.{/ts}</span>
            </td>
        </tr>
        <tr>
            <td class="label">{$form.groupId.label}</td>
            <td class="content">{$form.groupId.html}<br/>
                <span class="description">{ts}Optional group. The contact will be added to this group.{/ts}</span>
            </td>
        </tr>
        <tr>
            <td class="label">{$form.action.label}</td>
            <td class="content">{$form.action.html}</td>
        </tr>
    </table>
    <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
    </div>
</div>
