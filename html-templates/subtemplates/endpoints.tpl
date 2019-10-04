{template endpoint Endpoint useHostname=no linkify=yes}{strip}
    {if $linkify}<a href="{tif $useHostname ? cat('http://', Site::getConfig(primary_hostname))}{$Endpoint->getURL()}">{/if}
        {$Endpoint->getTitle()|escape}
    {if $linkify}</a>{/if}
{/strip}{/template}