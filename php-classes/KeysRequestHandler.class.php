<?php

class KeysRequestHandler extends RecordsRequestHandler
{
	static public $recordClass = 'Key';

    static public $accountLevelRead = 'Staff';
    static public $accountLevelComment = 'Staff';
	static public $accountLevelBrowse = 'Staff';
	static public $accountLevelWrite = 'Staff';
	static public $accountLevelAPI = 'Staff';
}