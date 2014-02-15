{template endpoint Endpoint}
    <a href="/endpoints/{$Endpoint->Handle}/v{$Endpoint->Version}">{$Endpoint->Title|escape}&nbsp;<small class="muted">v{$Endpoint->Version}</small></a>
{/template}