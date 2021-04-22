<div class="crm-block crm-form-block">
    <table class="form-layout">
        <tr>
            <td class="label">{$form.apiKey.label}</td>
            <td class="content">{$form.apiKey.html}<br/>
                <span class="description">{ts}API key for the Paypal business account{/ts}</span>
            </td>
        </tr>
        <tr>
            <td class="label">{$form.importLimit.label}</td>
            <td class="content">{$form.importLimit.html}<br/>
                <span class="description">{ts}The batch limit of the import process{/ts}</span>
            </td>
        </tr>
    </table>
    <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
    </div>
</div>
