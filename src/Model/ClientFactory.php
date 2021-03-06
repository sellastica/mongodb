<?php
namespace Sellastica\MongoDB\Model;

/**
 * @method \MongoDB\Database get
 */
class ClientFactory extends \Sellastica\Core\Model\FactoryAccessor
{
	/** @var iterable */
	private $config;


	/**
	 * @param iterable $config
	 */
	public function __construct(iterable $config)
	{
		$this->config = $config;
	}

	/**
	 * @param bool $includeTypeMap
	 * @return \MongoDB\Client
	 */
	public function create(bool $includeTypeMap = true): \MongoDB\Client
	{
		$driverOptions = $includeTypeMap
			? [
				'typeMap' => [
					'array' => 'Sellastica\MongoDB\Model\BSONArray',
					'document' => 'Sellastica\MongoDB\Model\BSONDocument',
					'root' => 'Sellastica\MongoDB\Model\BSONDocument',
				],
			]
			: [];
		return new \MongoDB\Client(
			'mongodb://' . $this->config['host'] . '/',
			array_filter([
				'username' => (string)$this->config['username'],
				'password' => (string)$this->config['password'],
			]),
			$driverOptions
		);
	}
}
