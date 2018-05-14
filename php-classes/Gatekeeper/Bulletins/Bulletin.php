<?php

namespace Gatekeeper\Bulletins;

use Cache;
use HandleBehavior;
use Emergence\People\Person;
use Gatekeeper\Endpoints\Endpoint;

class Bulletin extends \VersionedRecord
{
    // ActiveRecord configuration
    public static $tableName = 'bulletins';
    public static $singularNoun = 'bulletin';
    public static $pluralNoun = 'bulletins';
    public static $collectionRoute = '/bulletins';
    public static $useCache = true;

    public static $fields = [
        'EndpointID' => [
            'type' => 'uint',
            'default' => null
        ],
        'Status' => [
            'type' => 'enum',
            'values' => ['draft', 'pending', 'published', 'canceled'],
            'default' => 'draft'
        ],
        'PublishedOn' => [
            'type' => 'timestamp',
            'default' => null
        ],
        'PublisherID' => [
            'type' => 'uint',
            'default' => null
        ],
        'Headline',
        'Handle' => [
            'unique' => true
        ],
        'Body' => [
            'type' => 'clob',
            'default' => null
        ]
    ];

    public static $relationships = [
        'Endpoint' => [
            'type' => 'one-one',
            'class' => Endpoint::class
        ],
        'Publisher' => [
            'type' => 'one-one',
            'class' => Person::class
        ]
    ];

    public static $dynamicFields = [
        'Endpoint'
    ];

    public static $validators = [
        'Headline' => [
            'validator' => 'string',
            'minlength' => 3,
            'errorMessage' => 'Headline must contain at least 3 characters'
        ]
    ];

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
        HandleBehavior::onSave($this, $this->Headline);

        parent::save($deep);
    }

    public function destroy()
    {
        if ($this->Status == 'canceled') {
            return false;
        }

        $this->Status = 'canceled';
        $this->save();

        return true;
    }

    public static function delete($id)
    {
        if (!$Bulletin = static::getById($id)) {
            return false;
        }

        return $Bulletin->destroy();
    }
}
