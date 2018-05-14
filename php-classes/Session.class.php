<?php

class Session extends ActiveRecord
{
    // Session configurables
    public static $cookieName = null;
    public static $cookieDomain = null;
    public static $cookiePath = '/';
    public static $cookieSecure = false;
    public static $cookieExpires = false;
    public static $timeout = 3600;

    // support subclassing
    public static $rootClass = __CLASS__;
    public static $defaultClass = __CLASS__;
    public static $subClasses = array(__CLASS__);

    // ActiveRecord configuration
    public static $tableName = 'sessions';
    public static $singularNoun = 'session';
    public static $pluralNoun = 'sessions';

    public static $fields = array(
        'ContextClass' => null
        ,'ContextID' => null
        ,'Handle' => array(
            'unique' => true
        )
        ,'LastRequest' => array(
            'type' => 'timestamp'
            ,'default' => null
        )
        ,'LastIP' => array(
            'type' => 'integer'
            ,'unsigned' => true
            ,'notnull' => false
        )
        ,'CreatorID' => null
    );


    // Session
    public static function __classLoaded()
    {
        parent::__classLoaded();

        // generate cookie name
        if (!static::$cookieName) {
            static::$cookieName = 's_'.Site::getConfig('handle');
        }

        // auto-detect cookie domain by trimming leading www. from current hostname
        if (!static::$cookieDomain && !empty($_SERVER['HTTP_HOST'])) {
            static::$cookieDomain = preg_replace('/^www\.([^.]+\.[^.]+)$/i', '$1', $_SERVER['HTTP_HOST']);
            static::$cookieDomain = preg_replace('/^([^:]+):\d+$/i', '$1', static::$cookieDomain);
        }
    }


    public static function getFromRequest($create = true)
    {
        $sessionData = array(
            'LastIP' => !empty($_SERVER['REMOTE_ADDR']) ? ip2long($_SERVER['REMOTE_ADDR']) : null
            ,'LastRequest' => time()
        );


        // try to load from authorization header
        if (!empty($_SERVER['HTTP_AUTHORIZATION']) && 0 === strpos($_SERVER['HTTP_AUTHORIZATION'], 'Token ')) {
            if ($Session = static::getByHandle(substr($_SERVER['HTTP_AUTHORIZATION'], 6))) {
                $Session = static::updateSession($Session, $sessionData);
            }
        }

        // try to load from POST data
        if (empty($Session) && !empty($_POST[static::$cookieName])) {
            if ($Session = static::getByHandle($_POST[static::$cookieName])) {
                $Session = static::updateSession($Session, $sessionData);
            }
        }

        // try to load from GET data
        if (empty($Session) && !empty($_GET[static::$cookieName])) {
            if ($Session = static::getByHandle($_GET[static::$cookieName])) {
                $Session = static::updateSession($Session, $sessionData);
            }
        }

        // try to load from cookie data
        if (empty($Session) && !empty($_COOKIE[static::$cookieName])) {
            if ($Session = static::getByHandle($_COOKIE[static::$cookieName])) {
                $Session = static::updateSession($Session, $sessionData);
            }
        }


        // return found or create new session
        if (!empty($Session)) {
            // session found
            return $Session;
        } elseif ($create) {
            // create session
            return static::create($sessionData, true);
        } else {
            // no session available
            return false;
        }
    }

    public static function updateSession(Session $Session, $sessionData)
    {

        // check timestamp
        if (static::$timeout && $Session->LastRequest < (time() - static::$timeout)) {
            $Session->terminate();

            return false;
        } else {
            // update session
            $Session->setFields($sessionData);
            $Session->save();

            return $Session;
        }
    }

    public function save($deep = true)
    {
        // set handle
        if (!$this->Handle) {
            $this->Handle = HandleBehavior::generateRandomHandle($this);
        }

        // call parent
        parent::save($deep);

        // set cookie
        setcookie(
            static::$cookieName
            , $this->Handle
            , static::$cookieExpires ? (time() + static::$cookieExpires) : 0
            , static::$cookiePath
            , static::$cookieDomain
            , static::$cookieSecure
        );
    }

    public function terminate()
    {
        setcookie(static::$cookieName, '', time() - 3600);
        unset($_COOKIE[static::$cookieName]);

        $this->destroy();
    }
}