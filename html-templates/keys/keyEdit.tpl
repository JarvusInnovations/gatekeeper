{extends "designs/site.tpl"}

{block "title"}{tif $data->isPhantom ? "Create Key" : escape("Edit Key for $data->OwnerName")} &mdash; {$dwoo.parent}{/block}

{block "content"}
	{$Key = $data}
	{$errors = $Key->validationErrors}
	
	<form method="POST" class="register-form">
		{if $errors}
			<div class="notify error">
				<strong>Please double-check the fields highlighted below.</strong>
			</div>
		{/if}
	
		<fieldset class="shrink">

			{field name=OwnerName label='Owner Name' default=$Key->OwnerName required=true autofocus=true}

			<div class="inline-fields">
				{field name=ContactName label='Contact Name' error=$errors.ContactName default=$Key->ContactName}
				{field name=ContactEmail label='Contact Email' type=email error=$errors.ContactEmail default=$Key->ContactEmail}
			</div>

			{field name=ExpirationDate label='Expiration Date' type=date default=tif($Ban->ExpirationDate, date('Y-m-d', $Ban->ExpirationDate)) hint="Leave blank if none"}

			{checkbox name=AllEndpoints value=1 unsetValue=0 label='Allow all endpoints?' default=$Key->AllEndpoints}

			<div class="submit-area">
				<input type="submit" class="button submit" value="{tif $Key->isPhantom ? Create : Update} Key">
			</div>
		</fieldset>
	</form>
{/block}