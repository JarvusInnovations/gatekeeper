<?php

class Cache
{
	static public function localizeKey($key)
	{
		return Site::$config['handle'].':'.$key;
	}

	static public function fetch($key)
	{
		return apc_fetch(static::localizeKey($key));
	}
	
	static public  function store($key, $value, $ttl = 0)
	{
		return apc_store(static::localizeKey($key), $value, $ttl);
	}
	
	static public function delete($key)
	{
		return apc_delete(static::localizeKey($key));
	}

	static public function exists($key)
	{
		return apc_exists(static::localizeKey($key));
	}

	static public function increase($key, $step = 1)
	{
		return apc_inc(static::localizeKey($key), $step);
	}

	static public function decrease($key, $step = 1)
	{
		return apc_dec(static::localizeKey($key), $step);
	}
}