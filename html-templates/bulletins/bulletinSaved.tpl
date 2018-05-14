{extends "designs/site.tpl"}

{block "title"}Bulletin saved &mdash; {$dwoo.parent}{/block}

{block "content"}
    {$Bulletin = $data}

    <p class="lead">
        Bulletin <a href="{$Bulletin->getUrl()}">{$Bulletin->Headline|escape}</a> 
        {tif $Ban->isNew ? created : saved}.
    </p>

    <p><a href="/bulletins">&larr;&nbsp;Browse all bulletins</a></p>
{/block}