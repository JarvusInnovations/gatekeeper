{extends designs/site.tpl}

{block title}{$data->OwnerName|escape} &mdash; Keys &mdash; {$dwoo.parent}{/block}

{block content}
    {$Key = $data}

    <header class="page-header">
        <h2 class="header-title">Key: {apiKey $Key}</h2>
        <div class="header-buttons">
            <a class="button" href="{$Key->getUrl('/edit')}">Edit Key</a>
        </div>
    </header>

    <section class="page-section" id="key-endpoints">
        <h3>Permitted Endpoints</h3>

        {if $Key->AllEndpoints}
            <ul>
                <li>This key can access <strong>all endpoints</strong>.</li>
                <li><a href="{$Key->getUrl('/edit')}">Edit this key</a> to restrict access to an explicit list of endpoints here.</li>
            </ul>
        {else}
            <ul>
                {foreach item=Endpoint from=$Key->Endpoints}
                    <li>{endpoint $Endpoint}&nbsp;<a href="{$Key->getUrl('/endpoints')}/{$Endpoint->ID}/remove" class="button destructive tiny">Remove</a></li>
                {foreachelse}
                    <li><em>No endpoints added yet</em></li>
                {/foreach}
                {$unlinkedEndpoints = $Key->getUnlinkedEndpoints()}
                {if count($unlinkedEndpoints)}
                    <li>
                        <form action="{$Key->getUrl('/endpoints')}" method="POST">
                            <select name="EndpointID" class="field-control inline">
                                <option value="">Select endpoint</option>
                                {foreach item=Endpoint from=$unlinkedEndpoints}
                                    <option value="{$Endpoint->ID}">{$Endpoint->getTitle()|escape}</option>
                                {/foreach}
                            </select>
                            <input type="submit" value="Add">
                        </form>
                    </li>
                {/if}
            </ul>
        {/if}
    </section>

    <section class="page-section" id="key-log">
        <table>
            <caption>
                <h3>Request Log <small>(Last 30)</small></h3>
            </caption>
            <thead>
                <tr>
                    <th class="col-endpoint">Endpoint</th>
                    <th class="col-request">Request</th>
                    <th class="col-timestamp">Timestamp</th>
                    <th class="col-response-code"><small>Response</small> Code</th>
                    <th class="col-response-time"><small>Response</small> Time</th>
                    <th class="col-response-size"><small>Response</small> Size</th>
                    <th class="col-client-ip">Client IP</th>
                </tr>
            </thead>

            <tbody>
            {foreach item=Transaction from=Gatekeeper\Transactions\Transaction::getAllByField('KeyID', $Key->ID, array(order="ID DESC", limit=30))}
                <tr>
                    <td class="col-endpoint">{endpoint $Transaction->Endpoint}</td>
                    <td class="col-request">{$Transaction->Method} <small>{$Transaction->Path}{tif $Request->Query ? "?$Transaction->Query"}</small></td>
                    <td class="col-timestamp">{$Transaction->Created|date_format:'%Y-%m-%d %H:%M:%S'}</td>
                    <td class="col-response-code">{$Transaction->ResponseCode}</td>
                    <td class="col-response-time">{$Transaction->ResponseTime|number_format}&nbsp;ms</td>
                    <td class="col-response-size">{$Transaction->ResponseBytes|number_format}&nbsp;B</td>
                    <td class="col-client-ip">{$Transaction->ClientIP|long2ip}</td>
                </tr>
            {foreachelse}
                <tr>
                    <td colspan="7" class="col-empty-text">No requests logged yet.</td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    </section>
{/block}