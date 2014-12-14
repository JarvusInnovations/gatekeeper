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
                <a class="button destructive" href="/endpoints/{$Endpoint->Handle}/v{$Endpoint->Version}/delete">Delete Endpoint</a>
            {/if}
        </div>
    </header>

    <form method="POST" class="register-form">
        {if $errors}
            <div class="notify error">
                <strong>Please double-check the fields highlighted below.</strong>
            </div>
        {/if}

        <fieldset class="shrink">
            {field inputName=Title label=Title default=$Endpoint->Title required=true autofocus=true error=$errors.Title}

            {capture assign=urlInputHtml}{strip}
                http://{tif Gatekeeper\Gatekeeper::$apiHostname ? Gatekeeper\Gatekeeper::$apiHostname : "$.server.HTTP_HOST/api"}/&thinsp;
                <input type="text" size=15 name="Handle" required value="{refill field=Handle default=$Endpoint->Handle}">
                &nbsp;/v&nbsp;
                <input type="text" class="tiny" size=2 name="Version" required value="{refill field=Version default=$Endpoint->Version}">
            {/strip}{/capture}
            {labeledField html=$urlInputHtml type=compound label='Public Handle and Version' error=default($errors.Handle, $errors.Version) required=true}

            {field inputName=InternalEndpoint label='Internal Endpoint' type=url default=$Endpoint->InternalEndpoint error=$errors.InternalEndpoint required=true}

            {checkbox inputName=DefaultVersion value=1 unsetValue=0 label='Default Version' default=$Endpoint->DefaultVersion hint='This endpoint should be the default for its handle if no version is requested' error=$errors.DefaultVersion}

            <div class="inline-fields">
                {field inputName=AdminName label='Admin Name' error=$errors.AdminName default=$Endpoint->AdminName}
                {field inputName=AdminEmail label='Admin Email' type=email error=$errors.AdminEmail hint="Alerts will be sent here" default=$Endpoint->AdminEmail}
            </div>

            {field inputName=DeprecationDate label='Deprecation Date' type=date default=tif($Endpoint->DeprecationDate, date('Y-m-d', $Endpoint->DeprecationDate)) hint="Leave blank if none" error=$errors.DeprecationDate}

            {ratefields baseName=GlobalRate countDefault=$Endpoint->GlobalRateCount periodDefault=$Endpoint->GlobalRatePeriod label='Rate Limit (Global)' error=default($errors.GlobalRateCount, $errors.GlobalRatePeriod) hint="Leave blank if none"}

            {ratefields baseName=UserRate countDefault=$Endpoint->UserRateCount periodDefault=$Endpoint->UserRatePeriod label='Rate Limit (Per User)' error=default($errors.UserRateCount, $errors.UserRatePeriod) hint="Leave blank if none"}

            {checkbox inputName=KeyRequired value=1 unsetValue=0 label='API Key Required' default=$Endpoint->KeyRequired error=$errors.KeyRequired}

            {checkbox inputName=CachingEnabled value=1 unsetValue=0 label='Enable Response Caching' default=$Endpoint->CachingEnabled hint='Internal API must activate via HTTP headers' error=$errors.CachingEnabled}

            {checkbox inputName=AlertOnError value=1 unsetValue=0 label='Alert Admin on Error' default=$Endpoint->AlertOnError error=$errors.AlertOnError}

            {field inputName=AlertNearMaxRequests label='Alert Admin at % of Max Requests' type=number default=$Endpoint->AlertNearMaxRequests*100 attribs='min=0 max=100 step=1' hint="Leave blank for no alerts" class="tiny" error=$errors.AlertNearMaxRequests}

            <div class="submit-area">
                <input type="submit" class="button submit" value="{tif $Endpoint->isPhantom ? Create : Update} Endpoint">
            </div>
        </fieldset>
    </form>
{/block}