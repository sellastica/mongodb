<?php
namespace Sellastica\MongoDB\Mapping;

class DatabaseFactory implements IDatabaseFactory
{
	/** @var \Project\Model\ProjectFactory */
	private $projectFactory;
	/** @var \Sellastica\MongoDB\Model\ClientFactory */
	private $clientFactory;


	/**
	 * @param \Sellastica\MongoDB\Model\ClientFactory $clientFactory
	 * @param \Project\Model\ProjectFactory $projectFactory
	 */
	public function __construct(
		\Sellastica\MongoDB\Model\ClientFactory $clientFactory,
		\Project\Model\ProjectFactory $projectFactory
	)
	{
		$this->projectFactory = $projectFactory;
		$this->clientFactory = $clientFactory;
	}

	/**
	 * @return \MongoDB\Database
	 */
	public function create(): \MongoDB\Database
	{
		$database = $this->projectFactory->create()->getDatabase();
		if (!$database) {
			throw new \InvalidArgumentException('Unknown database name, it should be defined in the project');
		}
		
		return $this->clientFactory->createClient()->selectDatabase($database);
	}
}