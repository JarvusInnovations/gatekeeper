<?php

namespace Gatekeeper;

use Cache;

class EndpointRewrite extends \ActiveRecord
{
    // ActiveRecord configuration
    public static $tableName = 'endpoint_rewrites';
    public static $singularNoun = 'endpoint rewrite';
    public static $pluralNoun = 'endpoint rewrites';
    public static $useCache = true;

    public static $fields = array(
        'EndpointID' => 'uint'
        ,'Pattern'
        ,'Replace'
        ,'Last' => array(
            'type' => 'boolean'
            ,'default' => false
        )
        ,'Priority' => array(
            'type' => 'uint'
            ,'default' => 100
        )
    );

    public static $relationships = array(
        'Endpoint' => array(
            'type' => 'one-one'
            ,'class' => Endpoint::class
        )
    );

    public function validate($deep = true)
    {
        parent::validate($deep);

        if (!$this->EndpointID) {
            $this->_validator->addError('Endpoint', 'EndpointID must be specified');
        }

        $this->_validator->validate(array(
            'field' => 'Pattern'
            ,'validator' => 'regexp'
            ,'regexp' => '/^(.).+\1[a-zA-Z]*$/'
            ,'errorMessage' => 'Pattern must include matching delimiters'
        ));

        $this->_validator->validate(array(
            'field' => 'Priority'
            ,'required' => false
            ,'validator' => 'number'
            ,'min' => 0
            ,'errorMessage' => 'Priority must be integer > 0'
        ));

        return $this->finishValidation();
    }

    public function save($deep = true)
    {
        parent::save($deep);

        if (($this->isUpdated || $this->isNew) && $this->EndpointID) {
            Cache::delete("endpoints/$this->EndpointID/rewrites");
        }
    }

    public function destroy()
    {
        $success = parent::destroy();
        Cache::delete("endpoints/$this->EndpointID/rewrites");
        return $success;
    }
}
