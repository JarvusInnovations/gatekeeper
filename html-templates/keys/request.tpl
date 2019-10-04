{extends "designs/site.tpl"}

{block "title"}{tif $data->isPhantom ? "Create Key" : escape("Edit Key for $data->OwnerName")} &mdash; {$dwoo.parent}{/block}

{block "content"}
    {$Key = $data}

    <header class="page-header">
        <h2 class="header-title">Request API key for <a href="/api-docs/{$Endpoint->Path}" title="{$Endpoint->getTitle()|escape}">/{$Endpoint->Path|escape}</a></h2>
    </header>

    <form method="POST">
        {if $message}
            <div class="notify {tif !$success ? error}">
                <strong>{$message|escape}</strong>
            </div>
        {/if}

        <fieldset class="shrink">

            {field inputName=OwnerName label='Owner Name' default=$Key->OwnerName required=true autofocus=true hint='Name of application or organization that will use this key'}

            <div class="submit-area">
                <input type="submit" class="button submit" value="Request key">
            </div>
        </fieldset>
    </form>
{/block}