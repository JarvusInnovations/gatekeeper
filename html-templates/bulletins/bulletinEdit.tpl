{extends "designs/site.tpl"}

{block "title"}{tif $data->isPhantom ? "Draft Bulletin" : escape("Edit Bulletin")} &mdash; {$dwoo.parent}{/block}

{block "content"}
    {$Bulletin = $data}
    {$errors = $Bulletin->validationErrors}

    <header class="page-header">
        <h2 class="header-title">{if $Bulletin->isPhantom}New Draft Bulletin{else}Edit Draft Bulletin{/if}</h2>

        {if !$Bulletin->isPhantom}
            <div class="header-buttons">
                <a class="button destructive" href="{$Bulletin->getUrl('/delete')}">Cancel Bulletin</a>
                <a class="button primary" href="{$Bulletin->getUrl('/publish')}">Publish Bulletin</a>
            </div>
        {/if}
    </header>

    <form method="POST">
        {if $errors}
            <div class="notify error">
                <strong>Please double-check the fields highlighted below.</strong>
            </div>
        {/if}

        <fieldset class="shrink">

            {capture assign=endpointSelectHtml}
                <select name="EndpointID" class="field-control">
                    <option value="">None (System-wide Bulletin)</option>
                    {foreach item=AvailableEndpoint from=Gatekeeper\Endpoints\Endpoint::getAll()}
                        <option value="{$AvailableEndpoint->ID}" {refill field=endpoint selected=$AvailableEndpoint->Handle default=$Endpoint->Handle}>{$AvailableEndpoint->getTitle()|escape}</option>
                    {/foreach}
                </select>
            {/capture}

            {labeledField html=$endpointSelectHtml type=select label='Endpoint' class="auto-width"}

            {field inputName=Headline label='Headline' placeholder='New attributes added to crime API' default=$Bulletin->Headline required=yes error=$errors.Headline}

            {capture assign=handleInputHtml}{strip}
                http://{tif Gatekeeper\Gatekeeper::$portalHostname ? Gatekeeper\Gatekeeper::$portalHostname : "$.server.HTTP_HOST"}/bulletins/&thinsp;
                <input type="text" size=20 name="Handle" placeholder="new-crime-api-attributes" value="{refill field=Handle default=$Endpoint->Handle}" placeholder="transitview/v1">
            {/strip}{/capture}

            {labeledField html=$handleInputHtml type=compound label='URL' error=$errors.Handle hint='Leave blank to auto-generate from headline'}

            {textarea inputName=Body label='Body' default=$Bulletin->Body fieldClass='xlarge field-markdown' attribs='rows="15"' hint='Use <a href="http://daringfireball.net/projects/markdown">Markdown</a> to give your text some style' error=$errors.Body}

            <div class="submit-area">
                <input type="submit" class="button submit" value="{tif $Bulletin->isPhantom ? Create : Update} Draft Bulletin">
            </div>
        </fieldset>
    </form>
{/block}