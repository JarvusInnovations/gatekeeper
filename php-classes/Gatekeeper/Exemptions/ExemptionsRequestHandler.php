<?php

namespace Gatekeeper\Exemptions;

use Gatekeeper\Keys\Key;

class ExemptionsRequestHandler extends \RecordsRequestHandler
{
    public static $recordClass = Exemption::class;

    public static $accountLevelRead = 'Staff';
    public static $accountLevelComment = 'Staff';
    public static $accountLevelBrowse = 'Staff';
    public static $accountLevelWrite = 'Staff';
    public static $accountLevelAPI = 'Staff';

    protected static function applyRecordDelta(\ActiveRecord $Record, $data)
    {
        if (isset($data['KeyID']) && !is_numeric($data['KeyID'])) {
            $Key = Key::getByHandle($data['KeyID']);
            $data['KeyID'] = $Key ? $Key->ID : null;
        }

        return parent::applyRecordDelta($Record, $data);
    }
}
