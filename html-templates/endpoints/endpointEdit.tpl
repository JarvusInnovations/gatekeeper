{extends "designs/site.tpl"}

{block "title"}{tif $data->isPhantom ? "New Endpoint" : escape("Edit $data->Title")} &mdash; {$dwoo.parent}{/block}

{block "content"}
    {$Endpoint = $data}
    {$errors = $Endpoint->validationErrors}
    {load_templates subtemplates/rateFields.tpl}

    <header class="page-header">
        <h2 class="header-title">{if $Endpoint->isPhantom}New Endpoint{else}Edit {endpoint $Endpoint}{/if}</h2>
        <div class="header-buttons">
            {if $Endpoint->isPhantom}
                <a class="button destructive" href="/endpoints/">Cancel</a>
            {else}
                <a class="button destructive" href="{$Endpoint->getURL('/delete')}">Delete Endpoint</a>
            {/if}
        </div>
    </header>

    <form method="POST">
        {if $errors}
            <div class="notify error">
                <strong>Please double-check the fields highlighted below.</strong>
            </div>
        {/if}

        <fieldset class="shrink-break">
            {field inputName=Title label=Title default=$Endpoint->Title required=true autofocus=true error=$errors.Title}

            {textarea inputName=Description label=Description default=$Endpoint->Description error=$errors.Description}

            {checkbox inputName=Public value=1 unsetValue=0 label='Public' default=$Endpoint->Public hint='Endpoint should be published in the public developer portal' error=$errors.Public}
        </fieldset>

        <fieldset class="shrink-break">
            <h4>Routing</h4>

            {capture assign=pathInputHtml}{strip}
                http://{tif Gatekeeper\Gatekeeper::$apiHostname ? Gatekeeper\Gatekeeper::$apiHostname : "$.server.HTTP_HOST/api"}/&thinsp;
                <input type="text" size=20 name="Path" required value="{refill field=Path default=$Endpoint->Path}" placeholder="transitview/v1">
            {/strip}{/capture}
            {labeledField html=$pathInputHtml type=compound label='Public Path' error=$errors.Path required=true}

            {field inputName=InternalEndpoint label='Internal Endpoint' type=url default=$Endpoint->InternalEndpoint error=$errors.InternalEndpoint required=true}

            {checkbox inputName=CachingEnabled value=1 unsetValue=0 label='Enable Response Caching' default=$Endpoint->CachingEnabled hint='Internal API must activate via HTTP headers' error=$errors.CachingEnabled}
        </fieldset>

        <fieldset class="shrink-break">
            <h4>Administration</h4>

            <div class="inline-fields">
                {field inputName=AdminName label='Admin Name' error=$errors.AdminName default=$Endpoint->AdminName}
                {field inputName=AdminEmail label='Admin Email' type=email error=$errors.AdminEmail hint="Alerts will be sent here" default=$Endpoint->AdminEmail}
            </div>

            {checkbox inputName=AlertOnError value=1 unsetValue=0 label='Alert Admin on Error' default=$Endpoint->AlertOnError error=$errors.AlertOnError}

            {field inputName=AlertNearMaxRequests label='Alert Admin at % of Max Requests' type=number default=$Endpoint->AlertNearMaxRequests*100 attribs='min=0 max=100 step=1' hint="Leave blank for no alerts" class="tiny" error=$errors.AlertNearMaxRequests}
        </fieldset>

        <fieldset class="shrink-break">
            <h4>Access Control</h4>

            {checkbox inputName=KeyRequired value=1 unsetValue=0 label='API Key Required' default=$Endpoint->KeyRequired error=$errors.KeyRequired}

            {checkbox inputName=KeySelfRegistration value=1 unsetValue=0 label='API Key Self Registration' default=$Endpoint->KeySelfRegistration error=$errors.KeySelfRegistration hint="Users can register their own keys without approval"}

            {field inputName=DeprecationDate label='Deprecation Date' type=date default=tif($Endpoint->DeprecationDate, date('Y-m-d', $Endpoint->DeprecationDate)) hint="Leave blank if none" error=$errors.DeprecationDate}
        </fieldset>

        <fieldset class="shrink-break">
            <h4>Rate Control</h4>
            {ratefields baseName=GlobalRate countDefault=$Endpoint->GlobalRateCount periodDefault=$Endpoint->GlobalRatePeriod label='Rate Limit (Global)' error=default($errors.GlobalRateCount, $errors.GlobalRatePeriod) hint="Leave blank if none"}

            {ratefields baseName=UserRate countDefault=$Endpoint->UserRateCount periodDefault=$Endpoint->UserRatePeriod label='Rate Limit (Per User)' error=default($errors.UserRateCount, $errors.UserRatePeriod) hint="Leave blank if none"}

            {ratefields baseName=GlobalBandwidth countDefault=$Endpoint->GlobalBandwidthCount periodDefault=$Endpoint->GlobalBandwidthPeriod unit='bytes' numberClass='' numberSize=7 numberStep=1000 label='Bandwidth Limit (Global)' error=default($errors.GlobalBandwidthCount, $errors.GlobalBandwidthPeriod) hint="Leave blank if none"}
        </fieldset>

        <fieldset class="shrink-break">
            <h4>Monitoring</h4>

            {capture assign=pingFrequencyHtml}{strip}
                {$frequencyPresets = array(5, 10, 15, 20, 25, 30, 45, 60, 120)}
                {if $Endpoint->PingFrequency && !in_array($Endpoint->PingFrequency, $frequencyPresets)}
                    <input type="number" name="PingFrequency" value="{refill field=PingFrequency default=$Endpoint->PingFrequency}"> minutes
                {else}
                    <select name="PingFrequency">
                        <option value="">Never</option>
                        {foreach item=frequencyValue from=$frequencyPresets}
                            <option value="{$frequencyValue}" {refill field=PingFrequency default=$Endpoint->PingFrequency selected=$frequencyValue}>{$frequencyValue} minutes</option>
                        {/foreach}
                    </select>
                {/if}
            {/strip}{/capture}

            {labeledField html=$pingFrequencyHtml label='Ping Frequency'}

            {field inputName=PingURI label='Ping URI' default=$Endpoint->PingURI error=$errors.PingURI hint='Will be appended to internal endpoint URL' placeholder='/foo/bar?foo=bar'}

            {field inputName=PingTestPattern label='Ping Test Pattern' default=$Endpoint->PingTestPattern error=$errors.PingTestPattern hint='Optional <a href="http://php.net/preg_match">PCRE</a> pattern to test response body' placeholder='/"success":\s*true/i'}

        </fieldset>

        <div class="submit-area clear">
            <input type="submit" class="button submit" value="{tif $Endpoint->isPhantom ? Create : Update} Endpoint">
        </div>
    </form>
{/block}