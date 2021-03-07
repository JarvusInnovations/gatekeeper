<?php

namespace Gatekeeper\Endpoints;

use Cache;
use RecordValidator;

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

    public static $dynamicFields = [
        'Endpoint'
    ];

    public static $validators = [
        'Endpoint' => 'require-relationship',
        'Pattern' => [
            'validator' => 'regexp',
            'validator' => [__CLASS__, 'validatePattern']
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

    public static function validatePattern(RecordValidator $validator, EndpointRewrite $EndpointRewrite)
    {
        if (!preg_match('/^(.).+\1[a-zA-Z]*$/', $EndpointRewrite->Pattern)) {
            $validator->addError('Pattern', 'Pattern must include matching delimiters');
            return;
        }

        if (@preg_match($EndpointRewrite->Pattern, null) === false) {
            $validator->addError('Pattern', 'Pattern must valid PCRE regex');
            return;
        }
    }
}
