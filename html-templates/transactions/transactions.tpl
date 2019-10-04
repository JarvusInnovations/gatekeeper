{extends designs/site.tpl}

{block title}Transactions Log &mdash; {$dwoo.parent}{/block}

{block content}
    {load_templates subtemplates/paging.tpl}

    <header class="page-header">
        <h2 class="page-title">Logged Requests {if $Endpoint}for {endpoint $Endpoint}{/if}</h2>
        <div class="page-buttons">
            <span class="button-group">
                <label class="muted">Download this page:&nbsp;</label>
                <a class="button small" href="?{refill_query format=json}">JSON</a>
                <a class="button small" href="?{refill_query format=csv}">CSV</a>
            </span>

            <span class="button-group">
                <label class="muted">Download all:&nbsp;</label>
                <a class="button small" href="?{refill_query format=json limit=0 offset=0}">JSON</a>
                <a class="button small" href="?{refill_query format=csv limit=0 offset=0}">CSV</a>
            </span>
        </div>
    </header>

    <form method="GET" class="filter-list">
        <fieldset class="inline-fields">
            <h4 class="section-title">Filters</h4>

            {capture assign=methodSelectHtml}
                <select name="method" class="field-control">
                    <option value="">any</option>
                    {foreach item=method from=array(GET,POST,PUT,DELETE,OPTIONS)}
                        <option {refill field=method selected=$method}>{$method}</option>
                    {/foreach}
                </select>
            {/capture}
            {labeledField html=$methodSelectHtml type=select label='Method' class="small"}

            {field inputName=path-substring label='Path' fieldClass="medium" placeholder="substring"}

            {field inputName=query-substring label='Query' fieldClass="medium" placeholder="substring"}

            {field inputName=ip label='Client IP' fieldClass="small" placeholder="12.34.56.78"}

            {field inputName=key label='Key' attribs="size=32" fieldClass="medium" placeholder="837c2ceebcd374b1547c3719c4b212cc"}

            {capture assign=endpointSelectHtml}
                <select name="endpoint" class="field-control">
                    <option value="">all endpoints</option>
                    {foreach item=AvailableEndpoint from=Gatekeeper\Endpoints\Endpoint::getAll()}
                        <option value="{$AvailableEndpoint->Handle}" {refill field=endpoint selected=$AvailableEndpoint->Handle default=$Endpoint->Handle}>{$AvailableEndpoint->getTitle()|escape}</option>
                    {/foreach}
                </select>
            {/capture}
            {labeledField html=$endpointSelectHtml type=select label='Endpoint' class="medium"}

            {field inputName=time-max label='Time (max)' attribs="size=19" placeholder="YYYY-MM-DD HH:MM:SS" fieldClass="medium"}
            {field inputName=time-min label='Time (min)' attribs="size=19" placeholder="YYYY-MM-DD HH:MM:SS" fieldClass="medium"}

            {capture assign=typeSelectHtml}
                <select name="type" class="field-control">
                    <option value="">any</option>
                    <option {refill field=type selected=consumer}>consumer</option>
                    <option {refill field=type selected=ping}>ping</option>
                </select>
            {/capture}
            {labeledField html=$typeSelectHtml type=select label='Type' class="small"}

            {field inputName=limit type=number label='Limit' default='20' attribs='min="0"' fieldClass="tiny"}

            <div class="submit-area"><input type="submit" value="Apply Filters"></div>
        </fieldset>
    </form>

    <table>
        <thead>
            <tr>
                <th class="col-request">Request</th>
                <th class="col-timestamp">Timestamp</th>
                <th class="col-response-code"><small>Response</small> Code</th>
                <th class="col-response-time"><small>Response</small> Time</th>
                <th class="col-response-size"><small>Response</small> Size</th>
                <th class="col-client-ip">Client IP</th>
                <th class="col-key">Key</th>
            </tr>
        </thead>

        <tbody>
        {foreach item=Request from=$data}
            <tr>
                <td class="col-request">{$Request->Method} <small>{$Request->Path|default:/}{tif $Request->Query ? "?$Request->Query"|query_string}</small></td>
                <td class="col-timestamp">{$Request->Created|date_format:'%Y-%m-%d %H:%M:%S'}</td>
                <td class="col-response-code">{$Request->ResponseCode}</td>
                <td class="col-response-time">{$Request->ResponseTime|number_format} ms</td>
                <td class="col-response-size">{$Request->ResponseBytes|number_format} B</td>
                <td class="col-client-ip">{$Request->ClientIP|long2ip}</td>
                <td class="col-key">{if $Request->Key}{apiKey $Request->Key}{else}<small class="muted">&mdash;</small>{/if}</td>
            </tr>
        {foreachelse}
            <tr>
                <td colspan="7" class="col-empty-text">No requests logged yet.</td>
            </tr>
        {/foreach}
        </tbody>
    </table>

    {if $limit}
        {$count = count($data)}
        Showing {$offset+1}&ndash;{$offset+$count}
        {if $count == $limit}
            <a class="paging-link next" href="?{refill_query offset=$offset+$limit}">Next&nbsp;&rarr;</a>
        {/if}
    {/if}
{/block}
