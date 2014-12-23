{capture assign=subject}
    {if $Alert->Status == 'open'}
        New alert opened
    {else}
        Alert {$Alert->Status}
    {/if}

    {if $Alert->Endpoint}
        for {$Alert->Endpoint->getTitle()}
    {/if}
    at {$.now|date_format:'%Y-%m-%d %H:%M:%S'}
{/capture}
{load_templates "subtemplates/endpoints.tpl"}
<html>
    <body>
        <dl>
            <dt>ID</dt><dd>{$Alert->ID}</dd>
            <dt>Class</dt><dd>{$Alert->Class|regex_replace:'/^(.+\\\\)([^\\\\]+)\$/':'<small>\$1</small><strong>\$2</strong>'}</dd>
            <dt>Details URL</dt><dd><a href="http://{Site::getConfig(primary_hostname)}{$Alert->getUrl()}">{$Alert->getUrl()}</a></dd>
            <dt>Status</dt><dd>{$Alert->Status}</dd>
            <dt>Opened</dt><dd>{$Alert->Opened|date_format:'%Y-%m-%d %H:%M:%S'}</dd>
            <dt>Closed</dt><dd>{$Alert->Closed|date_format:'%Y-%m-%d %H:%M:%S'|default:'&mdash;'}</dd>

            {if $Alert->Repetitions}
                <dt>Repetitions</dt><dd>{$Alert->Repetitions|number_format}</dd>
            {/if}

            <dt>Acknowledger</dt><dd>{tif $Alert->Acknowledger ? $Alert->Acknowledger->FullName : '&mdash;'}</dd>

            {if $Alert->Endpoint}
                <dt>Endpoint</dt><dd>{endpoint $Alert->Endpoint useHostname=true}</dd>
            {/if}

            {if $Alert->Metadata}
                <dt>Alert Metadata</dt>
                <dd><pre>{$Alert->Metadata|print_r:true|escape}</pre></dd>
            {/if}
        </dl>

    </body>
</html>