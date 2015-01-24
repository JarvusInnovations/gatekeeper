{extends designs/site.tpl}

{block "title"}Developer Portal{/block}

{block "branding"}
<div class="site-branding title-jumbo">
    <a href="/">
        <div class="text">
            <big class="site-name">{$.server.HTTP_HOST}</big>
        </div>
    </a>
</div>
{/block}
{block "header-bottom"}{/block}

{block "js-bottom"}
    {$dwoo.parent}
    <script>
        Ext.onReady(function() {
            var searchPlaceholder = 'Search APIs…',
                searchInput = Ext.getBody().down('.api-search-input');

            searchInput.set({ placeholder: searchPlaceholder });
            searchInput.on('focus', function() { this.set({ placeholder: '' }) });
            searchInput.on('blur',  function() { this.set({ placeholder: searchPlaceholder }) });
        });
    </script>
{/block}

{block "content"}
    {$lipsum = explode(" ", "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec dapibus ante nec dolor tincidunt, in euismod augue molestie. Duis ut tortor suscipit, feugiat est eu, semper ipsum. Integer vehicula lorem eget purus ultricies pellentesque. Phasellus pellentesque vitae enim vel dignissim. Sed condimentum urna ultricies efficitur lobortis. Fusce egestas eros maximus, lobortis velit a, sagittis augue. Nullam fermentum ornare odio non pharetra. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Morbi vestibulum vestibulum justo, vel laoreet dolor rhoncus a. Sed nec felis a est convallis laoreet. Vestibulum quis accumsan nisi. Maecenas risus odio, dictum eu sem sed, vehicula tincidunt nisi. In semper ligula nec purus laoreet, et euismod justo bibendum. Ut quam magna, tempus at semper nec, vulputate in justo. Integer sed velit sagittis, consectetur erat vitae, rhoncus ligula. In eget lacus eu neque imperdiet tristique sed at sem.")}
    {$count = count($lipsum)}

    <header class="page-header">
        <h1 class="header-title">Find an API</h1>
    </header>

    <form class="api-search">
        <input class="api-search-input" type="search">
    </form>

    <section class="page-section">
        <header class="section-header">
            <h2 class="header-title">Browse by category</h2>
        </header>

        <ul class="category-grid">
            {loop array(
                'Agriculture',
                'Business',
                'Climate',
                'Consumer',
                'Ecosystems',
                'Education',
                'Energy',
                'Finance',
                'Health',
                'Local Government',
                'Manufacturing',
                'Ocean',
                'Public Safety',
                'Science & Research'
            )}
            <li class="category-grid-item">
                <a class="category-grid-link" href="/categories/{$|lower|whitespace:_|escape:url}">
                    <img class="category-grid-image" src="http://lorempixel.com/128/128?{$.loop.default.index}" width="64" height="64">
                    <div class="category-grid-title">{$|escape}</div>
                    <div class="category-grid-count">{mt_rand(1, 200)}</div>
                </a>
            </li>
            {/loop}
        </ul>
    </section>

    <section class="page-section">
        <header class="section-header">
            <h2 class="header-title">Top APIs this week</h2>
            <div class="header-buttons">
                <span class="button-group">
                    <label>Sort:</label>
                    <a class="button small active" href="#popularity">Popularity</a>
                    <a class="button small" href="#alpha">Alpha</a>
                    <a class="button small" href="#newest">Newest</a>
                </span>
            </div>
        </header>

        <ul class="endpoint-list">
        {$Endpoints = Gatekeeper\Endpoints\Endpoint::getAll()}
        {foreach item=Endpoint from=$Endpoints}
            <li class="endpoint-list-item">
                {$avgResponseTime = mt_rand(10, 15000)}
                {$desc = lower(join(' ', array_slice($lipsum, mt_rand(0, $count - 1), mt_rand(1, $count) )))}
                {$statuses = array('good', 'mid', 'bad', 'down', '')}
                {$i = array_rand($statuses)}
                {$status = $statuses[$i]}
                <div class="primary-metric {$status}">
                    <strong class="metric">
                        {if $status=='down'}DOWN
                        {else}
                            {if $avgResponseTime>99999}99K+
                            {elseif $avgResponseTime>9999}{floor(math('$avgResponseTime/1000'))}K+
                            {else}{$avgResponseTime|number_format}{/if}
                        {/if}
                    </strong>
                    {if $status!='down'}<span class="unit">ms</span>{/if}
                </div>
                <div class="endpoint-text">
                    <h3 class="endpoint-name">
                        <a class="endpoint-path" href="{$Endpoint->getUrl()}">/{$Endpoint->Path|escape}</a>
                        <small class="endpoint-title">{$Endpoint->getTitle()}</small>
                    </h3>
                    <p class="endpoint-description">{$desc|escape}</p>
                </div>
            </li>
        {/foreach}
        </ul>
    </section>
{/block}