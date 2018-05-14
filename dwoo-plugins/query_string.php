<?php

function Dwoo_Plugin_query_string(Dwoo_Core $dwoo, $input)
{
    return preg_replace(
        '/([?&])([^=]+)(=([^&]*))?/'
        ,'<span class="query-pair">$1<span class="query-key">$2</span>=<span class="query-value">$4</span></span>'
        ,$input
    );
}
