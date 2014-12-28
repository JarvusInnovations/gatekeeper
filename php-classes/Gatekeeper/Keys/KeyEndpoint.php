<?php

namespace Gatekeeper\Keys;

use Cache;
use Gatekeeper\Endpoints\Endpoint;

class KeyEndpoint extends \ActiveRecord
{
    // ActiveRecord configuration
    public static $tableName = 'key_endpoints';
    public static $singularNoun = 'key endpoint';
    public static $pluralNoun = 'key endpoints';

    public static $fields = [
        'KeyID' => 'uint',
        'EndpointID' => 'uint'
    ];

    public static $relationships = [
        'Key' => [
            'type' => 'one-one',
            'class' => Key::class
        ],
        'Endpoint' => [
            'type' => 'one-one',
            'class' => Endpoint::class
        ]
    ];

    public function validate($deep = true)
    {
        parent::validate($deep);

        if (!$this->KeyID || !$this->EndpointID) {
            $this->_validator->addError('KeyEndpoint', 'Both a KeyID and an EndpointID must be specified');
        }

        return $this->finishValidation();
    }

    public function save($deep = true)
    {
        parent::save($deep);

        if (($this->isUpdated || $this->isNew) && $this->KeyID) {
            Cache::delete("keys/$this->KeyID/endpoints");
        }
    }

    public function destroy()
    {
        $success = parent::destroy();
        Cache::delete("keys/$this->KeyID/endpoints");
        return $success;
    }
}
