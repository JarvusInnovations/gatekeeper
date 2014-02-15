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