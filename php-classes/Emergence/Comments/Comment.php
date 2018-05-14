<?php

namespace Emergence\Comments;

use HandleBehavior;

class Comment extends \VersionedRecord
{
    // support subclassing
    public static $rootClass = __CLASS__;
    public static $defaultClass = __CLASS__;
    public static $subClasses = array(__CLASS__);

    // ActiveRecord configuration
    public static $tableName = 'comments';
    public static $singularNoun = 'comment';
    public static $pluralNoun = 'comments';
    public static $collectionRoute = '/comments';

    public static $fields = array(
        'ContextClass'
        ,'ContextID' => 'uint'
        ,'Handle' => array(
            'unique' => true
        )
        ,'ReplyToID' => array(
            'type' => 'uint'
            ,'notnull' => false
        )
        ,'Message' => array(
            'type' => 'clob'
            ,'fulltext' => true
        )
    );

    public static $relationships = array(
        'Context' => array(
            'type' => 'context-parent'
        )
        ,'ReplyTo' => array(
            'type' => 'one-one'
            ,'class' => __CLASS__
        )
    );

    public static $validations = array(
        'Message' => array(
            'validator' => 'string_multiline'
            ,'errorMessage' => 'You must provide a message.'
        )
    );

    public static $searchConditions = array(
        'Message' => array(
            'qualifiers' => array('any','message')
        )
    );

    public function validate($deep = true)
    {
        // call parent
        parent::validate($deep);

        // implement handles
        HandleBehavior::onValidate($this, $this->_validator);

        // save results
        return $this->finishValidation();
    }

    public function save($deep = true)
    {
        // set handle
        if (!$this->Handle) {
            $this->Handle = HandleBehavior::generateRandomHandle(__CLASS__, 12);
        }

        parent::save();
    }
}