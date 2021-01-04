{extends designs/site.tpl}

{block title}Exemption #{$data->ID} &mdash; Exemptions &mdash; {$dwoo.parent}{/block}

{block content}
    {$Exemption = $data}

    <article class="exemption">
        <div class="details">
            <header class="page-header">
                <h3 class="header-title">
                    {if $Exemption->IPPattern}
                        IP Pattern: <strong>{$Exemption->IPPattern}</strong>
                    {else}
                        Key: <strong>{apiKey $Exemption->Key}</strong>
                    {/if}
                </h3>

                <div class="exemption-term">Exempted {if $Exemption->ExpirationDate}until {$Exemption->ExpirationDate|date_format}{else}indefinitely{/if}.</div>
            </header>
            {if $Exemption->Notes}
                <pre class="exemption-notes">{$Exemption->Notes|escape}</pre>
            {/if}
        </div>
        <footer>
            <a class="button" href="/exemptions/{$Exemption->ID}/edit">Edit</a>
            <a class="button" href="/exemptions/{$Exemption->ID}/delete">Remove</a>
        </footer>
    </article>
{/block}