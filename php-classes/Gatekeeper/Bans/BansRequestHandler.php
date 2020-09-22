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

    public static function handleCreateRequest(\ActiveRecord $Record = null)
    {
        if (static::shiftPath() === 'bulk') {
            return static::handleBulkCreationRequest();
        }

        return parent::handleCreateRequest($Record);
    }

    protected static function handleBulkCreationRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // process request
            $bans = [];
            foreach (explode("\r\n", $_REQUEST['IPPatterns']) as $ipPattern) {
                $trimmedPattern = trim($ipPattern);
                if (empty($trimmedPattern)) {
                    continue;
                }

                $bans[] = Ban::create([
                    'IPPattern' => $trimmedPattern
                ], true);
            }

            return static::respond('bulk/bansSaved', [
                'data' => $bans
            ]);
        }

        return static::respond('bulk/banCreate');
    }

    protected static function applyRecordDelta(\ActiveRecord $Ban, $data)
    {
        if (isset($data['KeyID']) && !is_numeric($data['KeyID'])) {
            $Key = Key::getByHandle($data['KeyID']);
            $data['KeyID'] = $Key ? $Key->ID : null;
        }

        return parent::applyRecordDelta($Ban, $data);
    }
}