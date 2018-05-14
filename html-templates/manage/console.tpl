<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http: //www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<title>Manage {$.server.HTTP_HOST}</title>
		
		<script>
			{* Print configuration data directly into page with Dwoo plugin template_variables *}
			window.consoleConfig = {template_variables};
		</script>
		
		{jscout use=$viewportLoader|replace:'.':'::'}
		
	</head>
	<body>
	
		<p style="border:1px solid #000;padding:0.5em;margin:1em;width:9em;text-align:center;" id="staticLoadingMsg">Loading console&hellip;</p>
	
	</body>
</html>