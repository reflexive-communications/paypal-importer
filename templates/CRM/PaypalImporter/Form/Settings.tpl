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
    </table>
    <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
    </div>
</div>
