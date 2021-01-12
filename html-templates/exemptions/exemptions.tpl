{extends designs/site.tpl}

{block title}Exemptions &mdash; {$dwoo.parent}{/block}

{block content}

    <header class="page-header">
        <h2 class="header-title">Exemptions</h2>
        <div class="header-buttons">
            <a class="button primary" href="/exemptions/create">Issue Exemption</a>
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

    <section class="exemptions cardlist">

        {foreach item=Exemption from=$data}
            <article class="exemption cardlist-item">
                <div class="details">
                    <header>
                        <h3 class="title">
                            {if $Exemption->IPPattern}
                                IP Pattern: <strong>{$Exemption->IPPattern}</strong>
                            {else}
                                Key: <strong>{apiKey $Exemption->Key}</strong>
                            {/if}
                        </h3>

                        <div class="meta exemption-term">Exempted {if $Exemption->ExpirationDate}until {$Exemption->ExpirationDate|date_format}{else}indefinitely{/if}.</div>
                    </header>
                    {if $Exemption->Notes}
                        <pre class="exemption-notes">{$Exemption->Notes|escape}</pre>
                    {/if}
                </div>
                <footer>
                    <a class="button" href="/exemptions/{$Exemption->ID}/edit">Edit</a>
                    <a class="button destructive" href="/exemptions/{$Exemption->ID}/delete">Remove</a>
                </footer>
            </article>
        {/foreach}

    </section>

{/block}