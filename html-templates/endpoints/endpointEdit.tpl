{extends "designs/site.tpl"}

{block "title"}{tif $data->isPhantom ? "New Endpoint" : escape("Edit $data->Title")} &mdash; {$dwoo.parent}{/block}

{block "content"}
	{$Endpoint = $data}
	{$errors = $Endpoint->validationErrors}
	
	<form method="POST" class="register-form">
		{if $errors}
			<div class="notify error">
				<strong>Please double-check the fields highlighted below.</strong>
			</div>
		{/if}
	
		<fieldset class="shrink">
			{* field name label='' error='' type=text placeholder='' hint='' required=false autofocus=false *}

			{field name=Title label=Title default=$Endpoint->Title required=true autofocus=true}

			{capture assign=urlInputHtml}{strip}
				http://{$.server.HTTP_HOST}/api/&thinsp;
				<input type="text" size=15 name="Handle" required value="{refill field=Handle default=$Endpoint->Handle}">
				&nbsp;/v&nbsp;
				<input type="number" class="tiny" size=2 name="Version" required value="{refill field=Version default=$Endpoint->Version}">
			{/strip}{/capture}
			{labeledField html=$urlInputHtml type=compound label='Public Handle and Version' error=default($errors.ForwardEndpoint, $errors.Version) required=true}

			{field name=InternalEndpoint label='Internal Endpoint' type=url default=$Endpoint->InternalEndpoint required=true}

			<div class="inline-fields">
				{field name=AdminName label='Admin Name' error=$errors.AdminName default=$Endpoint->AdminName}
				{field name=AdminEmail label='Admin Email' type=email error=$errors.AdminEmail hint="Alerts will be sent here" default=$Endpoint->AdminEmail}
			</div>

			{field name=DeprecationDate label='Deprecation Date' type=date default=tif($Endpoint->DeprecationDate, date('Y-m-d', $Endpoint->DeprecationDate)) hint="Leave blank if none"}

			{capture assign=maxRequestsInputHtml}{strip}
				<input type="number" class="tiny" size=2 name="MaxRequestsCount" value="{refill field=MaxRequestsCount default=$Endpoint->MaxRequestsCount}">
				&nbsp;per&nbsp;
				<select name="MaxRequestsPeriod">
					<option value="">Select</option>
					{foreach item=period from=Endpoint::getFieldOptions(MaxRequestsPeriod, values)}
						<option {refill field=MaxRequestsPeriod default=$Endpoint->MaxRequestsPeriod selected=$period}>{$period}</option>
					{/foreach}
				</select>
			{/strip}{/capture}
			{labeledField html=$maxRequestsInputHtml type=compound label='Maximum Requests (Global)' error=default($errors.MaxRequestsCount, $errors.MaxRequestsPeriod) hint="Leave blank if none"}

			{checkbox name=KeyRequired value=1 unsetValue=0 label='API Key Required' default=$Endpoint->KeyRequired}

			{checkbox name=CachingEnabled value=1 unsetValue=0 label='Enable Response Caching' default=$Endpoint->CachingEnabled hint='Internal API must activate via HTTP headers'}

			{checkbox name=AlertOnError value=1 unsetValue=0 label='Alert Admin on Error' default=$Endpoint->AlertOnError}

			{field name=AlertNearMaxRequests label='Alert Admin at % of Max Requests' type=number default=$Endpoint->AlertNearMaxRequests*100 attribs='min=0 max=100 step=1' hint="Leave blank for no alerts" class="tiny"}

			<div class="submit-area">
				<input type="submit" class="button submit" value="{tif $Endpoint->isPhantom ? Create : Update} Endpoint">
			</div>
		</fieldset>
	</form>
{/block}