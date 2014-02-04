<?php

class EndpointsRequestHandler extends RecordsRequestHandler
{
	static public $recordClass = 'Endpoint';
    
    static public function getRecordByHandle($endpointHandle)
    {
        // get version tag from next URL component
        if (!($endpointVersion = static::shiftPath()) || !preg_match('/^v.+$/', $endpointVersion)) {
			return static::throwInvalidRequestError('Endpoint version required');
		}
        
        $endpointVersion = substr($endpointVersion, 1);
        
        return Endpoint::getByWhere(array(
            'Handle' => $endpointHandle
            ,'Version' => $endpointVersion
        ));
    }
	
	static protected function applyRecordDelta(ActiveRecord $Endpoint, $data)
	{
		if (is_numeric($data['AlertNearMaxRequests'])) {
			$data['AlertNearMaxRequests'] = $data['AlertNearMaxRequests'] / 100;
		}
		
		return parent::applyRecordDelta($Endpoint, $data);
	}
}