<?php

namespace Gatekeeper;

class LoggedRequest extends \ActiveRecord
{
    // ActiveRecord configuration
    public static $tableName = 'requests_log';
    public static $singularNoun = 'logged request';
    public static $pluralNoun = 'logged requests';

    public static $fields = array(
        'CreatorID' => null
        ,'EndpointID' => array(
            'type' => 'uint'
            ,'index' => true
        )
        ,'KeyID' => array(
            'type' => 'uint'
            ,'notnull' => false
            ,'index' => true
        )
        ,'ClientIP' => 'uint'
        ,'Method'
        ,'Path'
        ,'Query' => 'clob'
        ,'ResponseTime' => array(
            'type' => 'mediumint'
            ,'unsigned' => true
        )
        ,'ResponseCode' => array(
            'type' => 'smallint'
            ,'unsigned' => true
        )
        ,'ResponseBytes' => array(
            'type' => 'mediumint'
            ,'unsigned' => true
        )
    );

    public static $relationships = array(
        'Endpoint' => array(
            'type' => 'one-one'
            ,'class' => Endpoint::class
        )
        ,'Key' => array(
            'type' => 'one-one'
            ,'class' => Key::class
        )
    );
}
