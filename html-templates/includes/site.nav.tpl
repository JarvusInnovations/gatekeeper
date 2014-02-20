{* page name/responseId => 'optional description' *}
{$navItems = array(
  'endpoints' => ''
       'keys' => ''
       'bans' => ''
)}

{load_templates subtemplates/nav.tpl}

{nav $navItems mobileHidden=$mobileHidden}