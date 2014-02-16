<?php

class LogsRequestHandler extends RecordsRequestHandler
{
    static public $recordClass = 'LoggedRequest';

    static public $accountLevelRead = 'Staff';
    static public $accountLevelComment = 'Staff';
	static public $accountLevelBrowse = 'Staff';
	static public $accountLevelWrite = 'Staff';
	static public $accountLevelAPI = 'Staff';

    static public $browseLimitDefault = 20;
    static public $browseOrder = array('ID' => 'DESC');


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