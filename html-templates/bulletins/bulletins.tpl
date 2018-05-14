{extends designs/site.tpl}

{block title}Bulletins &mdash; {$dwoo.parent}{/block}

{block content}
    <header class="page-header">
        <h2 class="page-title">Bulletins {if $Endpoint}for {endpoint $Endpoint}{/if}</h2>
        <div class="header-buttons">
            <a class="button primary" href="/bulletins/create">Draft Bulletin</a>
        </div>
    </header>

    <form method="GET">
        <h3 class="section-title">Filters</h3>
        <fieldset class="inline-fields">
            {capture assign=statusSelectHtml}
                <select name="status" class="field-control">
                    <option value="any" {refill field=status selected='any'}>any</option>
                    {foreach item=availableStatus from=Gatekeeper\Bulletins\Bulletin::getFieldOptions('Status', 'values')}
                        <option value="{$availableStatus|escape}" {refill field=status selected=$availableStatus default=$status}>{$availableStatus|escape}</option>
                    {/foreach}
                </select>
            {/capture}
            
            {labeledField html=$statusSelectHtml type=select label='Status' class="auto-width"}

            {capture assign=endpointSelectHtml}
                <select name="endpoint" class="field-control">
                    <option value="">All endpoints</option>
                    <option value="none" {refill field=endpoint selected='none'}>No endpoint (system-wide)</option>
                    {foreach item=AvailableEndpoint from=Gatekeeper\Endpoints\Endpoint::getAll()}
                        <option value="{$AvailableEndpoint->Handle}" {refill field=endpoint selected=$AvailableEndpoint->Handle default=$Endpoint->Handle}>{$AvailableEndpoint->getTitle()|escape}</option>
                    {/foreach}
                </select>
            {/capture}
            
            {labeledField html=$endpointSelectHtml type=select label='Endpoint' class="auto-width"}

            <div class="submit-area">
                <input type="submit" value="Apply Filters">
                <a class="button" href="?">Reset Filters</a>
            </div>
        </fieldset>
    </form>

    <table>
        <thead>
                <th class="col-id">ID</th>
                <th class="col-headline">Headline</th>
                <th class="col-scope">Scope</th>
                <th class="col-status">Status</th>
                <th class="col-published">Published</th>
                <th class="col-publisher">Publisher</th>
                <th class="col-actions"></th>
            </tr>
        </thead>

        <tbody>
        {foreach item=Bulletin from=$data}
            <tr>
                <td class="col-id">{$Bulletin->ID}</td>
                <td class="col-headline"><a href="{$Bulletin->getURL()}">{$Bulletin->Headline|escape}</a></td>
                <td class="col-scope">
                    {if $Bulletin->Endpoint}
                        {endpoint $Bulletin->Endpoint}
                    {else}
                        <small class="muted">global</small>
                    {/if}
                </td>
                <td class="col-status">{$Bulletin->Status}</td>
                <td class="col-published">
                    {if $Bulletin->Published}
                        {$Bulletin->Published|date_format:'%Y-%m-%d %H:%M:%S'}
                    {else}
                        <small class="muted">&mdash;</small>
                    {/if}
                </td>
                <td class="col-publisher">
                    {if $Bulletin->Publisher}
                        {personLink $Bulletin->Publisher}
                    {else}
                        <small class="muted">&mdash;</small>
                    {/if}
                </td>
                <td class="col-actions">
                    <a class="button bulletin-preview" href="{$Bulletin->getUrl('/preview')}">Preview</a>
                    <a class="button bulletin-publish" href="{$Bulletin->getUrl('/publish')}">Publish</a>
                </td>
            </tr>
        {foreachelse}
            <tr>
                <td colspan="8" class="col-empty-text">No {tif $status ? $status} bulletins found {if $Endpoint}for {endpoint $Endpoint}{/if}.</td>
            </tr>
        {/foreach}
        </tbody>
    </table>
{/block}
