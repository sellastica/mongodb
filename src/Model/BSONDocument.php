<?php
namespace Sellastica\MongoDB\Model;

class BSONDocument extends \ArrayObject implements \MongoDB\BSON\Unserializable
{
	/**
	 * @param $input
	 */
	public function __construct($input = [])
	{
		$data = [];
		foreach ($input as $k => $v) {
			if ($k === '_id') {
				$data['id'] = $v;
			} elseif ($v instanceof \MongoDB\BSON\UTCDateTime) {
				$data[$k] = \Sellastica\MongoDB\Utils\DateTime::fromUTCDateTime($v);
			} else {
				$data[$k] = $v;
			}
		}

		parent::__construct($data);
	}

	/**
	 * @return array
	 */
	public function toArray(): array
	{
		return (array)$this;
	}

	/**
	 * @param array $data
	 */
	public function bsonUnserialize(array $data): void
	{
		self::__construct($data);
	}

	/**
	 * @param $property
	 * @return mixed
	 */
	public function __get($property)
	{
		return $this[$property];
	}

	/**
	 * @param $property
	 * @return bool
	 */
	public function __isset($property): bool
	{
		return isset($this[$property]);
	}

	/**
	 * @param $property
	 * @param $value
	 */
	public function __set($property, $value): void
	{
		$this[$property] = $value;
	}
}
