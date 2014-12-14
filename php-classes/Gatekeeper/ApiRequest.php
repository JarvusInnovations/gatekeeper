<?php

namespace Gatekeeper;

class ApiRequest
{
    protected $startTime;
    protected $pathStack = [];
    protected $endpoint;
    protected $key;
    protected $url = '';
    protected $logRecord;

    public function __construct(array $pathStack = null)
    {
        $this->startTime = time();

        if ($pathStack !== null) {
            $this->pathStack = $pathStack;
        }
    }

    public function getStartTime()
    {
        return $this->startTime;
    }

    public function getPathStack()
    {
        return $this->pathStack;
    }

    public function setPathStack(array $pathStack)
    {
        $this->pathStack = $pathStack;
    }

    public function shiftPathStack()
    {
        return array_shift($this->pathStack);
    }

    public function peekPathStack()
    {
        return count($this->pathStack) ? $this->pathStack[0] : null;
    }

    public function getEndpoint()
    {
        return $this->endpoint;
    }

    public function setEndpoint(Endpoint $Endpoint)
    {
        $this->endpoint = $Endpoint;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function setKey(Key $Key)
    {
        $this->key = $Key;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = is_array($url) ? implode('/', $url) : $url;
    }

    public function getLogRecord()
    {
        return $this->logRecord;
    }

    public function setLogRecord(LoggedRequest $LogRecord)
    {
        $this->logRecord = $LogRecord;
    }

    public function isReady()
    {
        return $this->endpoint && is_a($this->endpoint, Endpoint::class);
    }

    public function getUserIdentifier()
    {
        return $this->key ? 'key:' . $this->key->ID : 'ip:' . $_SERVER['REMOTE_ADDR'];
    }
}