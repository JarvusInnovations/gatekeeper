{extends "designs/site.tpl"}

{block "title"}{tif $data->isPhantom ? "Create Ban" : escape("Edit Ban")} &mdash; {$dwoo.parent}{/block}

{block "content"}
    {$Ban = $data}
    {$errors = $Ban->validationErrors}

    <header class="page-header">
        <h2 class="header-title">{if $Ban->isPhantom}New Ban{else}Edit Ban{/if}</h2>
        <div class="header-buttons">
            {if $Ban->isPhantom}
                <a class="button destructive" href="/bans/">Cancel</a>
            {else}
                <a class="button destructive" href="/bans/{$Ban->ID}/delete">Remove Ban</a>
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

            <div class="inline-fields">
                {field inputName=IP label='IP Address' error=$errors.IP default=tif($Ban->IP, long2ip($Ban->IP))}
                <div class="or">&mdash;or&mdash;</div>
                {field inputName=KeyID label='API Key' error=$errors.KeyID default=$Ban->Key->Key}
            </div>

            {field inputName=ExpirationDate label='Expiration Date' type=date default=tif($Ban->ExpirationDate, date('Y-m-d', $Ban->ExpirationDate)) hint="Leave blank for indefinate ban"}

            {textarea inputName=Notes label='Notes' default=$Ban->Notes}

            <div class="submit-area">
                <input type="submit" class="button submit" value="{tif $Ban->isPhantom ? Create : Update} Ban">
            </div>
        </fieldset>
    </form>
{/block}