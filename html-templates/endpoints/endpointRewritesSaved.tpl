{extends "designs/site.tpl"}

{block "title"}Rewrites saved &mdash; {$dwoo.parent}{/block}

{block "content"}
	<p class="lead"><strong>{$saved|count}</strong> rewrite{tif count($saved) != 1 ? 's'} saved, <strong>{$deleted|count}</strong> rewrite{tif count($deleted) != 1 ? 's'} deleted, <strong>{$invalid|count}</strong> rewrite{tif count($invalid) != 1 ? 's'} invalid.</p>

    {if count($invalid)}
        <form id="endpoint-rewrites" method="POST">
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
            			<tr>
        					<td class="col-priority">{field name="rewrites[$Rewrite->ID][Priority]" default=$Rewrite->Priority}</td>
        					<td class="col-pattern">{field name="rewrites[$Rewrite->ID][Pattern]" default=$Rewrite->Pattern}</td>
        					<td class="col-replace">{field name="rewrites[$Rewrite->ID][Replace]" default=$Rewrite->Replace}</td>
        					<td class="col-last">{checkbox name="rewrites[$Rewrite->ID][Last]" value=1 unsetValue=0 default=$Rewrite->Last}</td>
        				</tr>
                        <tr>
                            <td colspan="4" class="errors">
                                <ul>
                                {foreach item=error from=$Rewrite->validationErrors}
                                    <li>{$error|escape}</li>
                                {/foreach}
                                </ul>
                            </td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
            <input type="submit" value="Save rewrites">
        </form>
    {/if}

	<p><a href="/endpoints/{$Endpoint->Handle}/v{$Endpoint->Version}#endpoint-rewrites">Return to endpoint</a></p>
{/block}