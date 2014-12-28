<?php

namespace Gatekeeper\Bans;

use Gatekeeper\Keys\Key;

class BansRequestHandler extends \RecordsRequestHandler
{
    public static $recordClass = Ban::class;

    public static $accountLevelRead = 'Staff';
    public static $accountLevelComment = 'Staff';
    public static $accountLevelBrowse = 'Staff';
    public static $accountLevelWrite = 'Staff';
    public static $accountLevelAPI = 'Staff';

    protected static function applyRecordDelta(\ActiveRecord $Ban, $data)
    {
        if (isset($data['IP']) && !is_numeric($data['IP'])) {
            $data['IP'] = ip2long($data['IP']);
        }

        if (isset($data['KeyID']) && !is_numeric($data['KeyID'])) {
            $Key = Key::getByHandle($data['KeyID']);
            $data['KeyID'] = $Key ? $Key->ID : null;
        }

        return parent::applyRecordDelta($Ban, $data);
    }
}