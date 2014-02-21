<?php

class KeysRequestHandler extends RecordsRequestHandler
{
	static public $recordClass = 'Key';

    static public $accountLevelRead = 'Staff';
    static public $accountLevelComment = 'Staff';
	static public $accountLevelBrowse = 'Staff';
	static public $accountLevelWrite = 'Staff';
	static public $accountLevelAPI = 'Staff';

    static public function handleRecordRequest(ActiveRecord $Key, $action = false)
    {
		switch ($action ? $action : $action = static::shiftPath()) {			
			case 'endpoints':
				return static::handleEndpointsRequest($Key);
			default:
				return parent::handleRecordRequest($Key, $action);
		}
	}
    
    static public function handleEndpointsRequest(Key $Key)
    {
        if ($endpointId = static::shiftPath()) {
            $Endpoint = Endpoint::getByID($endpointId);
            if (!$Endpoint || !in_array($Endpoint, $Key->Endpoints)) {
                return static::throwNotFoundError('Requested endpoint not added to this key');
            }
            
            return static::handleEndpointRequest($Key, $Endpoint);
        }
        
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                return static::respond('keyEndpoints', array(
                    'data' => KeyEndpoint::getAllByWhere(array('KeyID' => $Key->ID))
                ));
            case 'POST':
                if (empty($_POST['EndpointID']) || !($Endpoint = Endpoint::getByID($_POST['EndpointID']))) {
                    return static::throwInvalidRequestError('Valid EndpointID must be provided');
                }
                
                if (KeyEndpoint::getByWhere(array('KeyID' => $Key->ID, 'EndpointID' => $Endpoint->ID))) {
                    return static::throwInvalidRequestError('Provided endpoint already added to this key');
                }
                
                $KeyEndpoint = KeyEndpoint::create(array('KeyID' => $Key->ID, 'EndpointID' => $Endpoint->ID), true);
                
                return static::respond('keyEndpointAdded', array(
                    'success' => true
                    ,'data' => $KeyEndpoint
                ));
                
                break;
            default:
                return static::throwInvalidRequestError('Method not supported');
        }
    }
    
    static public function handleEndpointRequest(Key $Key, Endpoint $Endpoint)
    {
        if (static::peekPath() == 'remove') {
            return static::handleEndpointRemoveRequest($Key, $Endpoint);
        }
        
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                return static::respond('keyEndpoint', array(
                    'data' => KeyEndpoint::getByWhere(array('KeyID' => $Key->ID, 'EndpointID' => $Endpoint->ID))
                ));
            default:
                return static::throwInvalidRequestError('Method not supported');
        }
    }
    
    static public function handleEndpointRemoveRequest(Key $Key, Endpoint $Endpoint)
    {
        $KeyEndpoint = KeyEndpoint::getByWhere(array('KeyID' => $Key->ID, 'EndpointID' => $Endpoint->ID));
        
        if (!$KeyEndpoint) {
            return static::throwNotFoundError('Requested endpoint not added to this key');
        }
        
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        	return static::respond('confirm', array(
    			'question' => 'Are you sure you want to remove endpoint <strong>'.htmlspecialchars($Endpoint->Title).'</strong> from key '.$Key->Key.'?'
    			,'data' => KeyEndpoint::getByWhere(array('KeyID' => $Key->ID, 'EndpointID' => $Endpoint->ID))
    		));
        }
        
        $KeyEndpoint->destroy();
        
        return static::respond('keyEndpointRemoved', array(
            'success' => true
            ,'data' => $KeyEndpoint
        ));
    }
}