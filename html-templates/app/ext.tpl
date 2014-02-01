<!DOCTYPE html>
{$appName = $App->getName()}
{$appTheme = default($.get.theme, $App->getBuildCfg('app.theme'))}
{$jsBuildPath = "build/$mode/all-classes.js"}
{$cssMode = tif($mode == 'development' ? 'production' : $mode)}
{$cssBuildPath = tif($appTheme, "build/$cssMode/resources/$appName-all.css", "build/$cssMode/resources/default/app.css")}
<html>
	<head>
		<meta charset="UTF-8">
		<title>{if $title}{$title}{else}{$appName}-{$mode}{/if}</title>
		
		<script type="text/javascript">window.SiteUser = {$.User->getData()|json_encode};</script>

		{if $mode == 'development' || !$App->getAsset($jsBuildPath)}
			{capture assign=frameworkPath}sdk/ext{tif $.get.frameworkBuild!=core ? '-all'}{tif $mode == 'development' && $.get.frameworkBuild != allmin ? '-dev'}.js{/capture}
			<script type="text/javascript" src="{$App->getVersionedPath($frameworkPath)}"></script>
			
			<script type="text/javascript">
				Ext.Loader.setConfig({
					enabled: true
					,paths: {
						'Ext': 'sdk/src'
						,'Ext.ux': 'sdk/examples/ux'
						,'Emergence': 'x/Emergence'
						,'Jarvus': 'x/Jarvus'
					}
				}); 
			</script>
			
			{sencha_preloader}

			<script type="text/javascript" src="{tif $App->getAsset('app.js') ? $App->getVersionedPath('app.js') : $App->getVersionedPath('app/app.js')}"></script>
		{else}
			<script type="text/javascript" src="{$App->getVersionedPath($jsBuildPath)}"></script>
		{/if}
		
		{if $App->getAsset($cssBuildPath)}
			<link rel="stylesheet" type="text/css" href="{$App->getVersionedPath($cssBuildPath)}" />
		{elseif $appTheme}
			<link rel="stylesheet" type="text/css" href="{$App->getVersionedPath(cat('sdk/packages/$appTheme/build/resources/' $appTheme '-all.css'))}" />
			<script type="text/javascript" src="{$App->getVersionedPath(cat('sdk/packages/$appTheme/build/' $appTheme '.js'))}"></script>
		{else}
			<link rel="stylesheet" type="text/css" href="{$App->getVersionedPath('sdk/resources/css/ext-all.css')}" />
		{/if}
	</head>
	
	<body class="loading">
		{block "js-analytics"}
			<script type="text/javascript">
			{if $.User}
				var clicky_custom = {
					session: {
						username: '{$.User->Username}'
						,email: '{$.User->Email}'
						,full_name: '{$.User->FullName}'
					}
				};
			{/if}
			
			var clicky_site_ids = clicky_site_ids || [];
			clicky_site_ids.push(100671073);
			(function() {
				var s = document.createElement('script');
				s.type = 'text/javascript';
				s.async = true;
				s.src = '//static.getclicky.com/js';
				( document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0] ).appendChild( s );
			})();

			Ext.onReady(function() {
				if (Ext.util && Ext.util.History) {
					Ext.util.History.on('change', function(token) {
						if (window.clicky) {
							clicky.log(location.pathname+'#'+token, document.title);
						}
					});
				}
			});
			</script>
			<noscript><p><img alt="Clicky" width="1" height="1" src="//in.getclicky.com/100671073ns.gif" /></p></noscript>
		{/block}
	</body>
</html>