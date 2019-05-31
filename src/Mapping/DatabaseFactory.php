<?php
namespace Sellastica\MongoDB\Mapping;

class DatabaseFactory implements IDatabaseFactory
{
	/** @var \Project\Model\ProjectFactory */
	private $projectFactory;
	/** @var \Sellastica\MongoDB\Model\ClientFactory */
	private $clientFactory;
	/** @var \Sellastica\Core\Model\Environment */
	private $environment;


	/**
	 * @param \Sellastica\MongoDB\Model\ClientFactory $clientFactory
	 * @param \Project\Model\ProjectFactory $projectFactory
	 * @param \Sellastica\Core\Model\Environment $environment
	 */
	public function __construct(
		\Sellastica\MongoDB\Model\ClientFactory $clientFactory,
		\Project\Model\ProjectFactory $projectFactory,
		\Sellastica\Core\Model\Environment $environment
	)
	{
		$this->projectFactory = $projectFactory;
		$this->clientFactory = $clientFactory;
		$this->environment = $environment;
	}

	/**
	 * @return \MongoDB\Database
	 */
	public function create(): \MongoDB\Database
	{
		$project = $this->projectFactory->create();
		if ($this->environment->isDebugMode()) {
			$database = $project->getDebugModeDatabase() ?? $project->getDatabase();
		} else {
			$database = $project->getDatabase();
		}

		if (!$database) {
			throw new \InvalidArgumentException('Unknown database name, it should be defined in the project');
		}
		
		return $this->clientFactory->createClient()->selectDatabase($database);
	}
}