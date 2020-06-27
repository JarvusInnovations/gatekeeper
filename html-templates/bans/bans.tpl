{extends designs/site.tpl}

{block title}Bans &mdash; {$dwoo.parent}{/block}

{block content}

    <header class="page-header">
        <h2 class="header-title">Bans</h2>
        <div class="header-buttons">
            <a class="button primary" href="/bans/create">Issue Ban</a>
            <a class="button primary" href="/bans/create/bulk">Bulk Bans</a>
        </div>
    </header>

    <form method="GET">
        <label>
            Sort by
            <select name="sort" onchange="this.form.submit()">
                <option value="">No sort</option>
                <option value="created" {refill field=sort selected=created}>Created date</option>
                <option value="expiration" {refill field=sort selected=expiration}>Expiration date</option>
            </select>
        </label>
    </form>

    <section class="bans cardlist">

        {foreach item=Ban from=$data}
            <article class="ban cardlist-item">
                <div class="details">
                    <header>
                        <h3 class="title">
                            {if $Ban->IPPattern}
                                IP Pattern: <strong>{$Ban->IPPattern}</strong>
                            {else}
                                Key: <strong>{apiKey $Ban->Key}</strong>
                            {/if}
                        </h3>

                        <div class="meta ban-term">Banned {if $Ban->ExpirationDate}until {$Ban->ExpirationDate|date_format}{else}indefinitely{/if}.</div>
                    </header>
                    {if $Ban->Notes}
                        <pre class="ban-notes">{$Ban->Notes|escape}</pre>
                    {/if}
                </div>
                <footer>
                    <a class="button" href="/bans/{$Ban->ID}/edit">Edit</a>
                    <a class="button destructive" href="/bans/{$Ban->ID}/delete">Remove</a>
                </footer>
            </article>
        {/foreach}

    </section>

{/block}