{extends "designs/site.tpl"}

{block "title"}Create Bans in Bulk &mdash; {$dwoo.parent}{/block}

{block "content"}
    {$Ban = $data}
    {$errors = $Ban->validationErrors}

    <header class="page-header">
        <h2 class="header-title">New Bans</h2>
        <div class="header-buttons">
            <a class="button destructive" href="/bans/">Cancel</a>
        </div>
    </header>

    <form method="POST">
        {if $errors}
            <div class="notify error">
                <strong>Please double-check the following lines highlighted below.</strong>

                {foreach from=$invalidPatterns item=invalidPattern}
                    <p><u>{$invalidPattern}</u></p>
                {/foreach}
            </div>
        {/if}

        <fieldset class="shrink">
            {textarea inputName=IPPatterns label='IP Patterns' hint="Separate IP Patterns by line"}

            <div class="submit-area">
                <input type="submit" class="button submit" value="Create Bans">
            </div>
        </fieldset>
    </form>
{/block}