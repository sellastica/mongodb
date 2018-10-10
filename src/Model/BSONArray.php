<?php
namespace Sellastica\MongoDB\Model;

class BSONArray extends \ArrayObject implements \MongoDB\BSON\Unserializable
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
}
