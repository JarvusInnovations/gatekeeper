{extends "designs/site.tpl"}

{block "title"}Rewrites saved &mdash; {$dwoo.parent}{/block}

{block "content"}
    <p class="lead"><strong>{$saved|count}</strong> rewrite{tif count($saved) != 1 ? 's'} saved, <strong>{$deleted|count}</strong> rewrite{tif count($deleted) != 1 ? 's'} deleted, <strong>{$invalid|count}</strong> rewrite{tif count($invalid) != 1 ? 's'} invalid.</p>

    {if count($invalid)}
        <form class="page-section" id="endpoint-rewrites" method="POST">
            <table>
                <caption>
                    <h3>Unsaved rewrites</h3>
                </caption>
                <thead>
                    <tr>
                        <th class="col-priority">Priority</th>
                        <th class="col-pattern">Pattern</th>
                        <th class="col-replace">Replace</th>
                        <th class="col-last">Last?</th>
                    </tr>
                </thead>

                <tbody>
                    {foreach item=Rewrite from=$invalid}
                        {$rewriteKey = tif($Rewrite->isPhantom ? 'new' : $Rewrite->ID)}
                        <tr>
                            <td class="col-priority">{field inputName="rewrites[$rewriteKey][Priority]" default=$Rewrite->Priority}</td>
                            <td class="col-pattern">{field inputName="rewrites[$rewriteKey][Pattern]" default=$Rewrite->Pattern}</td>
                            <td class="col-replace">{field inputName="rewrites[$rewriteKey][Replace]" default=$Rewrite->Replace}</td>
                            <td class="col-last">{checkbox inputName="rewrites[$rewriteKey][Last]" value=1 unsetValue=0 default=$Rewrite->Last}</td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td colspan="3">
                                <ul class="errors">
                                {foreach item=error from=$Rewrite->validationErrors}
                                    <li>{$error|escape}</li>
                                {/foreach}
                                </ul>
                            </td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
            <input type="submit" value="Save Rewrites">
        </form>
    {/if}

    <a href="{$Endpoint->getURL()|escape}#endpoint-rewrites">&larr;&nbsp;Back to {$Endpoint->getTitle()|escape}</a>
{/block}