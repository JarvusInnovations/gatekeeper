{extends "designs/site.tpl"}

{block "title"}Share key for {$data->OwnerName|escape} &mdash; {$dwoo.parent}{/block}

{block "content"}
    {$Key = $data}

    <header class="page-header">
        <h2 class="header-title">Share API key {apiKey $Key->Key}</h2>
    </header>

    <form method="POST">
        {if $message}
            <div class="notify {tif !$success ? error}">
                <strong>{$message|escape}</strong>
            </div>
        {/if}

        <fieldset class="shrink">

            {field inputName=Email inputType=email label='User Email' required=true autofocus=true hint='Email address for registered user'}

            <div class="submit-area">
                <input type="submit" class="button submit" value="Share key">
            </div>
        </fieldset>
    </form>
{/block}