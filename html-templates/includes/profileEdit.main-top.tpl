{if
    (
        $.User->ID == $User->ID ||
        (
            ProfileRequestHandler::$accountLevelEditOthers &&
            $.User->hasAccountLevel(ProfileRequestHandler::$accountLevelEditOthers)
        )
    ) &&
    count($User->Subscriptions)
}
    <fieldset class="stretch">
        <h2 class="legend title-2">Subscriptions</h2>

        <ul>
            {foreach item=Subscription from=$User->Subscriptions}
                <li>
                    <a href="/api-docs/{$Subscription->Endpoint->Path}" title="{$Subscription->Endpoint->Title|escape}">
                        /{$Subscription->Endpoint->Path|escape}
                    </a>
                </li>
            {/foreach}
        </ul>
    </fieldset>
{/if}