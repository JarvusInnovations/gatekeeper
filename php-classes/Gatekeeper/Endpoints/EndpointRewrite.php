<?php

namespace Gatekeeper\Endpoints;

use Cache;

class EndpointRewrite extends \ActiveRecord
{
    // ActiveRecord configuration
    public static $tableName = 'endpoint_rewrites';
    public static $singularNoun = 'endpoint rewrite';
    public static $pluralNoun = 'endpoint rewrites';
    public static $useCache = true;

    public static $fields = [
        'EndpointID' => 'uint',
        'Pattern',
        'Replace',
        'Last' => [
            'type' => 'boolean',
            'default' => false
        ],
        'Priority' => [
            'type' => 'uint',
            'default' => 100
        ]
    ];

    public static $relationships = [
        'Endpoint' => [
            'type' => 'one-one',
            'class' => Endpoint::class
        ]
    ];

    public static $validators = [
        'Endpoint' => 'require-relationship',
        'Pattern' => [
            'validator' => 'regexp',
            'regexp' => '/^(.).+\1[a-zA-Z]*$/',
            'errorMessage' => 'Pattern must include matching delimiters'
        ],
        'Priority' => [
            'required' => false,
            'validator' => 'number',
            'min' => 0,
            'errorMessage' => 'Priority must be integer > 0'
        ]
    ];

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
