<?php

if ($GLOBALS['Session']->hasAccountLevel('Staff')) {
    SearchRequestHandler::$searchClasses[User::class] = array(
        'fields' => array(
            array(
                'field' => 'FirstName'
                ,'method' => 'like'
            )
            ,array(
                'field' => 'LastName'
                ,'method' => 'like'
            )
            ,array(
                'field' => 'Username'
                ,'method' => 'like'
            )
            ,array(
                'field' => 'FullName'
                ,'method' => 'sql'
                ,'sql' => 'CONCAT(FirstName," ",LastName) = "%s"'
            )
        )
        ,'conditions' => array('AccountLevel != "Deleted"')
    );

    SearchRequestHandler::$searchClasses[Gatekeeper\Endpoint::class] = array(
        'fields' => array(
            array(
                'field' => 'Title'
                ,'method' => 'like'
            )
            ,array(
                'field' => 'Handle'
                ,'method' => 'like'
            )
            ,array(
                'field' => 'AdminName'
                ,'method' => 'like'
            )
            ,array(
                'field' => 'AdminEmail'
                ,'method' => 'like'
            )
        )
    );

    SearchRequestHandler::$searchClasses[Gatekeeper\Key::class] = array(
        'fields' => array(
            array(
                'field' => 'OwnerName'
                ,'method' => 'like'
            )
            ,array(
                'field' => 'Key'
                ,'method' => 'ContactName'
            )
            ,array(
                'field' => 'ContactEmail'
                ,'method' => 'like'
            )
            ,array(
                'field' => 'Key'
                ,'method' => 'like'
            )
        )
    );

    SearchRequestHandler::$searchClasses[Gatekeeper\Ban::class] = array(
        'fields' => array(
            'Notes'
            ,array(
                'field' => 'Notes'
                ,'method' => 'like'
            )
            ,array(
                'field' => 'IP'
                ,'method' => 'sql'
                ,'sql' => 'INET_NTOA(IP) LIKE "%%%s%%"'
            )
        )
    );
}