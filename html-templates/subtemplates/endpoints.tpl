{template endpoint Endpoint useHostname=no}
    <a href="{tif $useHostname ? cat('http://', Site::getConfig(primary_hostname))}/endpoints/{$Endpoint->Handle}/v{$Endpoint->Version}">{$Endpoint->Title|escape}&nbsp;<small class="muted">v{$Endpoint->Version}</small></a>
{/template}