{extends app/ext.tpl}

{block css-app}
    {cssmin fonts/font-awesome.css}
    {$dwoo.parent}
{/block}