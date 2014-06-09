{template apiKey Key useHostname=no}
    <a href="{tif $useHostname ? cat('http://', Site::getConfig(primary_hostname))}/keys/{$Key->Key}">{$Key->OwnerName|escape}</a> <small class="muted key-string">{$Key->Key}</small>
{/template}