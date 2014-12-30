{template endpoint Endpoint useHostname=no linkify=yes smallify=yes}{strip}
    {if $linkify}<a href="{tif $useHostname ? cat('http://', Site::getConfig(primary_hostname))}/endpoints/{$Endpoint->Handle}/v{$Endpoint->Version}">{/if}
        {$Endpoint->Title|escape}&nbsp;
        {if $smallify}<small class="muted">{/if}
        v{$Endpoint->Version}
        {if $smallify}</small>{/if}
    {if $linkify}</a>{/if}
{/strip}{/template}