{extends "designs/site.tpl"}

{block "title"}{tif $data->isPhantom ? "Create Exemption" : escape("Edit Exemption")} &mdash; {$dwoo.parent}{/block}

{block "content"}
    {$Exemption = $data}
    {$errors = $Exemption->validationErrors}

    <header class="page-header">
        <h2 class="header-title">{if $Exemption->isPhantom}New Exemption{else}Edit Exemption{/if}</h2>
        <div class="header-buttons">
            {if $Exemption->isPhantom}
                <a class="button destructive" href="/exemptions/">Cancel</a>
            {else}
                <a class="button destructive" href="/exemptions/{$Exemption->ID}/delete">Remove Exemption</a>
            {/if}
        </div>
    </header>

    <form method="POST">
        {if $errors}
            <div class="notify error">
                <strong>Please double-check the fields highlighted below.</strong>
            </div>
        {/if}

        <fieldset class="shrink">

            <div class="inline-fields">
                {field inputName=IPPattern label='IP Pattern' error=$errors.IPPattern default=$Exemption->IPPattern hint="192.168.1.1,192.168.1.*,192.168.1.1/24"}
                <div class="or">&mdash;or&mdash;</div>
                {field inputName=KeyID label='API Key' error=$errors.KeyID default=$Exemption->Key->Key}
            </div>

            {field inputName=ExpirationDate label='Expiration Date' type=date default=tif($Exemption->ExpirationDate, date('Y-m-d', $Exemption->ExpirationDate)) hint="Leave blank for indefinate exemption"}

            {textarea inputName=Notes label='Notes' default=$Exemption->Notes}

            <div class="submit-area">
                <input type="submit" class="button submit" value="{tif $Exemption->isPhantom ? Create : Update} Exemption">
            </div>
        </fieldset>
    </form>
{/block}