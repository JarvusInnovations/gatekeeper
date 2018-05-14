<!DOCTYPE HTML>
<html {if $mode=='production'}manifest="/app/{$App->getName()}/cache.appcache"{/if} lang="en-US">
    <head>
        {block meta}
            <meta charset="UTF-8">
            <title>{if $title}{$title}{else}{$App->getName()}-{$mode}{/if}</title>
        {/block}

        {block base}
            {if $mode == 'production' || $mode == 'testing'}
                <base href="/app/{$App->getName()}/build/production/">
            {else}
                <base href="/app/{$App->getName()}/">
            {/if}
        {/block}

        {block css-loader}
            <style type="text/css">
                html, body {
                    height: 100%;
                    background-color: {$loaderBgColor|default:"#1985D0"};
                }

                #appLoadingIndicator {
                    position: absolute;
                    top: 50%;
                    margin-top: -15px;
                    text-align: center;
                    width: 100%;
                    height: 30px;
                    -webkit-animation-name: appLoadingIndicator;
                    -webkit-animation-duration: 0.5s;
                    -webkit-animation-iteration-count: infinite;
                    -webkit-animation-direction: linear;
                }

                #appLoadingIndicator > * {
                    background-color: {$loaderFgColor|default:"#FFFFFF"};
                    display: inline-block;
                    height: 30px;
                    -webkit-border-radius: 15px;
                    margin: 0 5px;
                    width: 30px;
                    opacity: 0.8;
                }

                @-webkit-keyframes appLoadingIndicator{
                    0% {
                        opacity: 0.8
                    }
                    50% {
                        opacity: 0
                    }
                    100% {
                        opacity: 0.8
                    }
                }
            </style>
        {/block}
    </head>
    <body class="{block body-class}{/block}">
        {block body}
        <div id="appLoadingIndicator">
            <div></div>
            <div></div>
            <div></div>
        </div>
        {/block}

        {block js-data}
            <script type="text/javascript">
                var SiteEnvironment = SiteEnvironment || { };
                SiteEnvironment.user = {JSON::translateObjects($.User)|json_encode};
                SiteEnvironment.appName = {$App->getName()|json_encode};
                SiteEnvironment.appMode = {$mode|json_encode};
                SiteEnvironment.appBaseUrl = '/app/{$App->getName()}/{tif $mode == production || $mode == testing ? "build/$mode/"}';
            </script>
        {/block}

        {block css-app}
            {if $mode != 'production'}
                {if !$App->getAsset('build/production/resources/css/app.css')}
                    <link rel="stylesheet" href="{$App->getVersionedPath('sdk/resources/css/sencha-touch.css')}">
                {else}
                    <link rel="stylesheet" href="{$App->getVersionedPath('build/production/resources/css/app.css')}">
                {/if}
            {/if}
        {/block}

        {block js-app}
            <script type="text/javascript">
                {$App->getMicroloader($mode)}
            </script>
            {if $mode == 'production'}
                <script type="text/javascript">
                    Ext.blink({ "id":"{$App->getAppId()}" });
                </script>
            {else}
                {if $mode == 'development' || !$App->getAsset('build/production/app.js')}
                    {block js-app-devenv}
                        {capture assign=frameworkPath}sdk/sencha-touch{tif $.get.frameworkBuild!=core ? '-all'}{tif $mode == 'development' && $.get.frameworkBuild != allmin ? '-debug'}.js{/capture}
                        <script type="text/javascript" src="{$App->getVersionedPath($frameworkPath)}"></script>

                        {sencha_bootstrap}

                        {$scriptRoot = ''}
                    {/block}
                {else}
                    {$scriptRoot = 'build/production/'}
                {/if}

                {block js-app-local}
                    {foreach item=script from=$App->getAppCfg('js')}
                        {if !$script['x-bootstrap'] && !$script.remote}
                            <script type="text/javascript" src="{$App->getVersionedPath(cat($scriptRoot, $script.path))}"></script>
                        {/if}
                    {/foreach}
                {/block}

                {block js-app-remote}
                    {foreach item=script from=$App->getAppCfg('js')}
                        {if $script.remote}
                            <script src="{$script.path|escape}"></script>
                        {/if}
                    {/foreach}
                {/block}
            {/if}
        {/block}

        {block "js-analytics"}
            {include includes/site.analytics.tpl}
        {/block}
    </body>
</html>