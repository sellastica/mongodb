<?php
namespace Sellastica\MongoDB\Utils;

class DateTime
{
	/**
	 * @param \MongoDB\BSON\UTCDateTime $UTCDateTime
	 * @return \DateTime
	 */
	public static function fromUTCDateTime(\MongoDB\BSON\UTCDateTime $UTCDateTime): \DateTime
	{
		$dateTime = $UTCDateTime->toDateTime();
		$dateTime->setTimezone(new \DateTimeZone(date_default_timezone_get()));
		return $dateTime;
	}

	/**
	 * @param \DateTime $dateTime
	 * @return \MongoDB\BSON\UTCDateTime
	 */
	public static function toUTCDateTime(\DateTime $dateTime): \MongoDB\BSON\UTCDateTime
	{
		return new \MongoDB\BSON\UTCDateTime($dateTime->getTimestamp() * 1000);
	}
}