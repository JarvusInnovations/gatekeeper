<?php

class BansRequestHandler extends RecordsRequestHandler
{
	static public $recordClass = 'Ban';
	
	static protected function applyRecordDelta(ActiveRecord $Ban, $data)
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