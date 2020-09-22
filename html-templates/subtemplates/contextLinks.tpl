{load_templates subtemplates/keys.tpl}
{load_templates subtemplates/endpoints.tpl}
{load_templates subtemplates/alerts.tpl}

{template contextLink Context prefix='' suffix='' class=''}{strip}
    {if !$Context}
        <em>[context deleted]</em>
    {elseif is_a($Context, Emergence\People\Person::class)}
        <a href="/people/{$Context->Handle}" class="{$class}">{$prefix}{$Context->FullNamePossessive|escape} Profile{$suffix}</a>
    {elseif is_a($Context, Media::class)}
        <a href="{$Context->getThumbnailRequest(1000,1000)}" class="attached-media-link {$class}" title="{$Context->Caption|escape}">
            {$prefix}
            <img src="{$Context->getThumbnailRequest(25,25)}" alt="{$Context->Caption|escape}">
            &nbsp;{$Context->Caption|escape}
            {$suffix}
        </a>
    {elseif is_a($Context, Gatekeeper\Bans\Ban::class)}
        <a href="/bans/{$Context->Handle}" class="{$class}">
            {$prefix}Ban #{$Context->ID}
            &mdash;
            {if $Context->IPPattern}
                IP Pattern: <strong>{$Context->IPPattern}</strong>
            {else}
                Key: <strong>{$Context->Key->OwnerName|escape} <small class="muted key-string">{$Context->Key->Key}</small></strong>
            {/if}{$suffix}
        </a>
    {elseif is_a($Context, Gatekeeper\Keys\Key::class)}
        {$prefix}{apiKey $Context}{$suffix}
    {elseif is_a($Context, Gatekeeper\Endpoints\Endpoint::class)}
        {$prefix}{endpoint $Context}{$suffix}
    {elseif is_a($Context, Gatekeeper\Alerts\AbstractAlert::class)}
        {$prefix}{alert $Context}{$suffix}
    {else}
        <a href="{$Context->getURL()|escape}" class="{$class}">{$prefix}{$Context->getTitle()|escape}{$suffix}</a>
    {/if}
{/strip}{/template}