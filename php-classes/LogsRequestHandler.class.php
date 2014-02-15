<?php

class LogsRequestHandler extends RecordsRequestHandler
{
    static public $recordClass = 'LoggedRequest';
    static public $browseLimitDefault = 20;


    static public function handleBrowseRequest($options = array(), $conditions = array(), $responseID = null, $responseData = array())
    {
        // apply time-slot filter
        if (!empty($_REQUEST['endpoint'])) {
            if (!$Endpoint = Endpoint::getByHandle($_REQUEST['endpoint'])) {
                return static::throwNotFoundError('Endpoint not found');
            }
            
			$conditions['EndpointID'] = $Endpoint->ID;
		}
		
        return parent::handleBrowseRequest($options, $conditions, $responseID, $responseData);
    }
}