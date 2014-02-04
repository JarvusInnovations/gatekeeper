{template avatar Member size=32 pixelRatio=2}
	{$pixels = $size}
	{if $pixelRatio}
		{$pixels = $size * $pixelRatio}
	{/if}
	{if $Member->PrimaryPhoto}
		{$src = $Member->PrimaryPhoto->getThumbnailRequest($pixels, $pixels)}
	{elseif $Member->Email}
		{$src = cat("//www.gravatar.com/avatar/", md5(strtolower($Member->Email)), "?s=", $pixels, "&d=identicon")}
	{/if}

	<img height={$size} alt=" " src="{$src}" class="avatar">
{/template}

{template nav navItems mobileHidden=false}
	{* navItems should be an array: page name/responseId => 'optional description' *}
	<nav class="nav site {if $mobileHidden}mobile-hidden{/if}">
		<ul>
		{foreach $navItems navItemId navItemDesc}
			{if $navItemId == 'template'}{$navItemId = ''}{/if}
			{$navItemDesc = default($navItemDesc, ucfirst($navItemId))}
			
			<li><a href="/{$navItemId}" {if $.responseId == $navItemId} class="current"{/if}>{$navItemDesc}</a></li>
		{/foreach}
		</ul>
	</nav>
{/template}

{*template field name label='' error='' type=text placeholder='' hint='' required=false autofocus=false default=null cls=null}
	<label class="field {$type}-field {if $error}has-error{/if} {if $required}is-required{/if}">
		{if $label}<span class="field-label">{$label}</span>{/if}

		{if $type==textarea}
			<textarea
		{else}
			<input type="{$type}"
		{/if}
				class="field-control {$cls}"
				name="{$name}"
				{if $placeholder}placeholder="{$placeholder}"{/if}
				{if $autofocus}autofocus{/if}
				{if $required}required{/if}
		{if $type==textarea}
			>{refill field=$name default=$default}</textarea>
		{else}
			value="{refill field=$name default=$default}">
		{/if}
		
		{if $error}<span class="error-text">{$error}</span>{/if}
		{if $hint}<p class="hint">{$hint}</p>{/if}
	</label>
{/template*}

{template labeledField html type=text label='' error='' hint='' required=false}
	<label class="field {$type}-field {if $error}has-error{/if} {if $required}is-required{/if}">
		{if $label}<span class="field-label">{$label}</span>{/if}

		{$html}
		
		{if $error}<span class="error-text">{$error}</span>{/if}
		{if $hint}<p class="hint">{$hint}</p>{/if}
	</label>
{/template}

{template field name label='' error='' type=text placeholder='' hint='' required=false attribs='' default=null class=null}
	{capture assign=html}
		<input type="{$type}"
			class="field-control {$class}"
			name="{$name|escape}"
			{if $placeholder}placeholder="{$placeholder|escape}"{/if}
			{if $required}required{/if}
			{$attribs}
			value="{refill field=$name default=$default}">
	{/capture}
	
	{labeledField html=$html type=$type label=$label error=$error hint=$hint required=$required}
{/template}

{template checkbox name value label='' error='' hint='' attribs='' default=null class=null unsetValue=null}
	{capture assign=html}
		<input type="checkbox"
			class="field-control {$class}"
			name="{$name|escape}"
			value="{$value|escape}"
			{$attribs}
			{refill field=$name default=$default checked=$value}>
	{/capture}

	{if $unsetValue !== null}
		<input type="hidden" name="{$name|escape}" value="{$unsetValue|escape}">
	{/if}
	
	{labeledField html=$html type=checkbox label=$label error=$error hint=$hint required=$required}
{/template}

{template textarea name label='' error='' placeholder='' hint='' required=false attribs='' default=null}
	{capture assign=html}
		<textarea
			class="field-control"
			name="{$name|escape}"
			{if $placeholder}placeholder="{$placeholder|escape}"{/if}
			{if $required}required{/if}
			{$attribs}
		>{refill field=$name default=$default}</textarea>
	{/capture}
	
	{labeledField html=$html type=textarea label=$label error=$error hint=$hint required=$required}
{/template}

{template ratefields baseName countDefault='' periodDefault='' label='' error='' hint='' required=false}
	{$countField = cat($baseName, "Count")}
	{$periodField = cat($baseName, "Period")}
	{$periodPresets = array(Minute=60,Hour=3600,Day=86400,Week=604800,Month=2592000)}

	{capture assign=html}{strip}
		<input type="number" class="tiny" size=2 name="{$countField}" value="{refill field=$countField default=$countDefault}">
		&nbsp;requests&nbsp;per&nbsp;
		{if $periodDefault && !in_array($periodDefault, $periodPresets)}
			<input type="number" name="{$periodField}" value="{refill field=$periodField default=$periodDefault}"> seconds
		{else}
			<select name="{$periodField}">
				<option value="">Select</option>
				{foreach key=periodLabel item=periodValue from=$periodPresets}
					<option value="{$periodValue}" {refill field=$periodField default=$periodDefault selected=$periodValue}>{$periodLabel}</option>
				{/foreach}
			</select>
		{/if}
	{/strip}{/capture}
	{labeledField html=$html type=compound label=$label error=$error hint=$hint required=$required}
{/template}

{template key Key}
	<a href="/keys/{$Key->Key}">{$Key->OwnerName|escape}</a> <small class="muted key-string">{$Key->Key}</small>
{/template}

{template endpoint Endpoint}
	<a href="/endpoints/{$Endpoint->Handle}/v{$Endpoint->Version}">{$Endpoint->Title|escape} <small class="muted">v{$Endpoint->Version}</small></a>
{/template}