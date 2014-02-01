<?php

class HitBuckets
{
	/**
	 * Count a hit against a bucket and determine if the bucket has been filled
	 *
	 * @param string $bucketKey A string key used to identify a particular bucket
	 * @param callable $getBucketLimits A function that will be called if a current
	 *    bucket does not exist. It should return an array containing the keys 
	 *    'seconds' and 'hits' defining the parameters of the next bucket
	 *
	 * @return boolean|integer False if bucket has been filled or number of seconds until new bucket will be available
	 */
	static public function drip($bucketKey, callable $getBucketLimits)
	{
		$now = time();
		$cacheKey = "buckets/$bucketKey";
		$bucketStamp = Cache::fetch($cacheKey);
		
		// create bucket if not found or erased
		if ($bucketStamp === false || $bucketStamp <= $now) {
			$bucketLimits = call_user_func($getBucketLimits);
			$bucketStamp = $now + $bucketLimits['seconds'];

			Cache::store("$cacheKey/$bucketStamp", $bucketLimits['count'], $bucketLimits['seconds'] + 10);
			Cache::store($cacheKey, $bucketStamp);
		}
		
		if (0 <= Cache::decrease("$cacheKey/$bucketStamp")) {
			return false;
		} else {
			return $bucketStamp - $now; 
		}
	}
}