{template apiKey Key}
    <a href="/keys/{$Key->Key}">{$Key->OwnerName|escape}</a> <small class="muted key-string">{$Key->Key}</small>
{/template}