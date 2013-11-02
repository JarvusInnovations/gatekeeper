<?php

class EndpointsRequestHandler extends RecordsRequestHandler
{
	static public $recordClass = 'Endpoint';
	
	static protected function applyRecordDelta(ActiveRecord $Endpoint, $data)
	{
		if (is_numeric($data['AlertNearMaxRequests'])) {
			$data['AlertNearMaxRequests'] = $data['AlertNearMaxRequests'] / 100;
		}
		
		return parent::applyRecordDelta($Endpoint, $data);
	}
}