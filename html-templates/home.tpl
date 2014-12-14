{extends designs/site.tpl}

{block content}
    <h2>Dashboard</h2>

    {$endpointCount = Gatekeeper\Endpoint::getCount()}
    <p><a href="/endpoints"><strong>{$endpointCount|number_format}</strong> endpoint{tif $endpointCount != 1 ? s}</a></p>

    {$keyCount = Gatekeeper\Key::getCount()}
    <p><a href="/keys"><strong>{$keyCount|number_format}</strong> key{tif $keyCount != 1 ? s}</a></p>

    {$banCount = Gatekeeper\Ban::getCount()}
    <p><a href="/bans"><strong>{$banCount|number_format}</strong> ban{tif $banCount != 1 ? s}</a></p>
{/block}