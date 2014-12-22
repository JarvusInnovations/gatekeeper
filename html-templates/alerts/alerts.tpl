{extends designs/site.tpl}

{block title}Alerts &mdash; {$dwoo.parent}{/block}

{block content}
    <header class="page-header">
        <h2 class="page-title">Alerts {if $Endpoint}for {endpoint $Endpoint}{/if}</h2>
    </header>

    <form method="GET">
        <h3 class="section-title">Filters</h3>
        <fieldset class="inline-fields">
            {capture assign=statusSelectHtml}
                <select name="status" class="field-control">
                    {foreach item=availableStatus from=Gatekeeper\Alerts\AbstractAlert::getFieldOptions('Status', 'values')}
                        <option value="{$availableStatus|escape}" {refill field=status selected=$availableStatus default=$status}>{$availableStatus|escape}</option>
                    {/foreach}
                    <option value="any" {refill field=status selected='any' default=$status}>any</option>
                </select>
            {/capture}
            
        	{labeledField html=$statusSelectHtml type=select label='Status'}

            {capture assign=endpointSelectHtml}
                <select name="endpoint" class="field-control">
                    <option value="">All endpoints</option>
                    {foreach item=AvailableEndpoint from=Gatekeeper\Endpoint::getAll()}
                        <option value="{$AvailableEndpoint->ID}" {refill field=endpoint selected=$AvailableEndpoint->ID default=$Endpoint->ID}>{$AvailableEndpoint->Title|escape} v{$AvailableEndpoint->Version|escape}</option>
                    {/foreach}
                </select>
            {/capture}
        	
        	{labeledField html=$endpointSelectHtml type=select label='Endpoint'}

            <div class="submit-area">
                <input type="submit" value="Apply Filters">
                <a class="button" href="?">Reset Filters</a>
            </div>
        </fieldset>
    </form>

    <table>
        <thead>
                <th class="col-id">ID</th>
                <th class="col-class">Type</th>
                <th class="col-status">Status</th>
                <th class="col-opened">Opened</th>
                <th class="col-repetitions">Repetitions</th>
                <th class="col-closed">Closed</th>
                <th class="col-endpoint">Endpoint</th>
                <th class="col-acknowledger">Acknowledger</th>
                <th class="col-actions"></th>
            </tr>
        </thead>

        <tbody>
        {foreach item=Alert from=$data}
            <tr>
                <td class="col-id">{$Alert->ID}</td>
                <td class="col-class">{$Alert->Class|regex_replace:'/^(.+\\\\)([^\\\\]+)\$/':'<small>\$1</small><br>\$2'}</td>
                <td class="col-status">{$Alert->Status}</td>
                <td class="col-opened">{$Alert->Opened|date_format:'%Y-%m-%d %H:%M:%S'}</td>
                <td class="col-repetitions">{$Alert->Repetitions|number_format}</td>
                <td class="col-closed">{$Alert->Closed|date_format:'%Y-%m-%d %H:%M:%S'|default:'<small class="muted">&mdash;</small>'}</td>
                <td class="col-endpoint">{if $Alert->Endpoint}{endpoint $Alert->Endpoint}{else}<small class="muted">&mdash;</small>{/if}</td>
                <td class="col-acknowledger">{if $Alert->Acknowledger}{personLink $Alert->Acknowledger}{else}<small class="muted">&mdash;</small>{/if}</td>
                <td class="col-actions">
                    <a class="button alert-acknowledge" href="{$Alert->getUrl()}/acknowledge">Acknowledge</a>
                    <a class="button alert-dismiss" href="{$Alert->getUrl()}/dismiss">Dismiss</a>
                </td>
            </tr>
        {foreachelse}
            <tr>
                <td colspan="8" class="col-empty-text">No {tif $status ? $status} alerts found {if $Endpoint}for {endpoint $Endpoint}{/if}.</td>
            </tr>
        {/foreach}
        </tbody>
    </table>
{/block}
