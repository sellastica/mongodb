<?php
namespace Sellastica\MongoDB\Mapping;

abstract class MongoMapper implements \Sellastica\Entity\Mapping\IMapper
{
	/** @var string */
	protected $collectionName;
	/** @var \Sellastica\AdminUI\User\Model\AdminUserAccessor */
	private $adminUserAccessor;
	/** @var IDatabaseFactory */
	private $databaseFactory;


	/**
	 * @param IDatabaseFactory $databaseFactory
	 * @param \Sellastica\AdminUI\User\Model\AdminUserAccessor $adminUserAccessor
	 */
	public function __construct(
		IDatabaseFactory $databaseFactory,
		\Sellastica\AdminUI\User\Model\AdminUserAccessor $adminUserAccessor
	)
	{
		$this->databaseFactory = $databaseFactory;
		$this->adminUserAccessor = $adminUserAccessor;
		$this->collectionName = $this->getCollectionName();
	}

	/**
	 * @param \Sellastica\Entity\Configuration|null $configuration
	 * @return \MongoDB\Database
	 * @throws \InvalidArgumentException If database is not defined in the configuration
	 */
	protected function getDatabase(\Sellastica\Entity\Configuration $configuration = null): \MongoDB\Database
	{
		return $this->databaseFactory->create();
	}

	/**
	 * @return \MongoDB\Collection
	 */
	protected function getCollectionName(): string
	{
		$collectionName = \Sellastica\Utils\Strings::fromCamelCase((new \ReflectionClass($this))->getShortName());
		$collectionName = \Nette\Utils\Strings::before($collectionName, '_mapper');
		return $collectionName;
	}

	/**
	 * @param \Sellastica\Entity\Configuration|null $configuration
	 * @return \MongoDB\Collection
	 */
	protected function getCollection(\Sellastica\Entity\Configuration $configuration = null): \MongoDB\Collection
	{
		return $this->getDatabase($configuration)->selectCollection($this->getCollectionName());
	}

	/**
	 * @return \MongoDB\BSON\ObjectId
	 */
	public function nextIdentity(): \MongoDB\BSON\ObjectId
	{
		return new \MongoDB\BSON\ObjectId();
	}

	/**
	 * @param $id
	 * @param \Sellastica\Entity\Configuration|null $configuration
	 * @return \MongoDB\Model\BSONDocument|null
	 */
	public function find(
		$id,
		\Sellastica\Entity\Configuration $configuration = null
	)
	{
		if (!$id instanceof \MongoDB\BSON\ObjectId) {
			try {
				$id = new \MongoDB\BSON\ObjectId($id);
			} catch (\MongoDB\Driver\Exception\InvalidArgumentException $e) {
				return null;
			}
		}

		return isset($id)
			? $this->getCollection($configuration)->findOne(['_id' => $id])
			: null;
	}

	/**
	 * @param $id
	 * @param string $field
	 * @return mixed|false
	 */
	public function findField($id, string $field)
	{
		throw new \Nette\NotImplementedException();
	}

	/**
	 * @param string $field
	 * @param array $filter
	 * @param \Sellastica\Entity\Configuration|null $configuration
	 * @return array
	 */
	public function findFieldBy(
		string $field,
		array $filter,
		\Sellastica\Entity\Configuration $configuration = null
	): array
	{
		throw new \Nette\NotImplementedException();
	}

	/**
	 * @param $id
	 * @param array $fields
	 * @throws \Nette\NotImplementedException
	 */
	public function findFields($id, array $fields)
	{
		throw new \Nette\NotImplementedException();
	}

	/**
	 * @param array $idsArray
	 * @param \Sellastica\Entity\Configuration|null $configuration
	 * @return array
	 */
	public function findByIds(
		array $idsArray,
		\Sellastica\Entity\Configuration $configuration = null
	): array
	{
		throw new \Nette\NotImplementedException();
	}

	/**
	 * @param \Sellastica\Entity\Configuration $configuration
	 * @return \MongoDB\Driver\Cursor
	 */
	public function findAllIds(
		\Sellastica\Entity\Configuration $configuration = null
	): iterable
	{
		return $this->getCollection($configuration)->find([], $this->getOptions([], $configuration));
	}

	/**
	 * @param array $filter
	 * @param \Sellastica\Entity\Configuration|null $configuration
	 * @return array
	 */
	protected function getOptions(
		array $filter,
		\Sellastica\Entity\Configuration $configuration = null
	): array
	{
		$options = [];
		if (isset($configuration)) {
			//sorter
			if ($configuration->getSorter()) {
				$sort = [];
				foreach ($configuration->getSorter()->getRules() as $rule) {
					$sort[$rule->getColumn()] = $rule->isAscending() ? 1 : -1;
				}

				$options['sort'] = $sort;
			}

			//paginator
			if ($paginator = $configuration->getPaginator()) {
				$paginator->setItemCount($this->findCountBy($filter));
				$options['limit'] = $paginator->getItemsPerPage();
				$options['skip'] = $paginator->getOffset();
			}
		}

		return $options;
	}

	/**
	 * @param array $filter
	 * @param \Sellastica\Entity\Configuration|null $configuration
	 * @return array
	 */
	protected function getOptionsForAggregate(
		array $filter,
		\Sellastica\Entity\Configuration $configuration = null
	): array
	{
		$options = [];
		if (isset($configuration)) {

			//sorter - must be first
			if ($configuration->getSorter()) {
				$sort = [];
				foreach ($configuration->getSorter()->getRules() as $rule) {
					$sort[$rule->getColumn()] = $rule->isAscending() ? 1 : -1;
				}

				$options = array_merge($options, [
					['$sort' => $sort],
				]);
			}

			//paginator
			if ($paginator = $configuration->getPaginator()) {
				$paginator->setItemCount($this->findCountBy($filter));
				$options = array_merge($options, [
					['$limit' => $paginator->getItemsPerPage() * $paginator->getPage()],
					['$skip' => $paginator->getOffset()],
				]);
			}
		}

		return $options;
	}

	/**
	 * @param array $filter
	 * @param \Sellastica\Entity\Configuration $configuration
	 * @return \MongoDB\Driver\Cursor
	 */
	public function findBy(
		array $filter,
		\Sellastica\Entity\Configuration $configuration = null
	): iterable
	{
		return $this->getCollection($configuration)->find($filter, $this->getOptions($filter, $configuration));
	}

	/**
	 * @param \Sellastica\Entity\Entity\ConditionCollection $conditions
	 * @param \Sellastica\Entity\Configuration $configuration
	 * @return array
	 */
	public function findByConditions(
		\Sellastica\Entity\Entity\ConditionCollection $conditions,
		\Sellastica\Entity\Configuration $configuration = null
	): array
	{
		throw new \Nette\NotImplementedException();
	}

	/**
	 * @param string $column
	 * @param array $values
	 * @param string $modifier
	 * @param \Sellastica\Entity\Configuration $configuration
	 * @return array
	 */
	public function findIn(
		string $column,
		array $values,
		string $modifier = 's',
		\Sellastica\Entity\Configuration $configuration = null
	): array
	{
		throw new \Nette\NotImplementedException();
	}

	/**
	 * @param array $filter
	 * @param \Sellastica\Entity\Configuration|null $configuration
	 * @return \MongoDB\Model\BSONDocument|null
	 */
	public function findOneBy(
		array $filter,
		\Sellastica\Entity\Configuration $configuration = null
	)
	{
		return $this->getCollection($configuration)->findOne($filter, $this->getOptions($filter, $configuration));
	}

	/**
	 * @param \Sellastica\Entity\Configuration|null $configuration
	 * @return int
	 */
	public function findCount(\Sellastica\Entity\Configuration $configuration = null): int
	{
		return $this->getCollection($configuration)->countDocuments();
	}

	/**
	 * @param array $filter
	 * @param \Sellastica\Entity\Configuration|null $configuration
	 * @return int
	 */
	public function findCountBy(array $filter, \Sellastica\Entity\Configuration $configuration = null): int
	{
		return $this->getCollection($configuration)->countDocuments($filter);
	}

	/**
	 * @param string|null $key
	 * @param string $value
	 * @param array $filter
	 * @param \Sellastica\Entity\Configuration $configuration
	 * @return array
	 */
	public function findPairs(
		string $key = null,
		string $value,
		array $filter = [],
		\Sellastica\Entity\Configuration $configuration = null
	): array
	{
		if ($key === 'id') {
			$key = '_id';
		}

		if ($value === 'id') {
			$value = '_id';
		}

		$projection = [
			$key => 1,
			$value => 1,
		];
		if ($key !== '_id'
			&& $value !== '_id') {
			$projection['_id'] = 0;
		}

		$cursor = $this->getCollection($configuration)->find(
			$filter,
			array_merge(['projection' => $projection], $this->getOptions($filter, $configuration))
		);

		$result = [];
		foreach ($cursor as $row) {
			$key = $row->$key instanceof \MongoDB\BSON\ObjectId ? (string)$row->$key : $row->$key;
			$value = $row->$value instanceof \MongoDB\BSON\ObjectId ? (string)$row->$value : $row->$value;
			$result[$key] = $value;
		}

		return $result;
	}

	/**
	 * @param \Sellastica\Entity\Entity\IEntity|\Sellastica\MongoDB\Entity\IMongoObject $entity
	 * @param \Sellastica\Entity\Configuration|null $configuration
	 */
	public function update(
		\Sellastica\Entity\Entity\IEntity $entity,
		\Sellastica\Entity\Configuration $configuration = null
	)
	{
		if ($entity->getChangedData()) {
			$this->getCollection($configuration)->replaceOne(
				['_id' => $entity->getObjectId()],
				$this->appendModifiedTimestamp($entity->toArray())
			);
		}
	}

	/**
	 * @param \Sellastica\Entity\Entity\IEntity|\Sellastica\MongoDB\Entity\IMongoObject $entity
	 * @param \Sellastica\Entity\Configuration|null $configuration
	 */
	public function insert(
		\Sellastica\Entity\Entity\IEntity $entity,
		\Sellastica\Entity\Configuration $configuration = null
	): void
	{
		$this->getCollection($configuration)->insertOne(
			$this->appendModifiedTimestamp(
				$entity->toArray(true)
			)
		);
	}

	/**
	 * @param array $array
	 * @return array
	 */
	private function appendModifiedTimestamp(array $array): array
	{
		return array_merge($array, ['modified' => new \MongoDB\BSON\UTCDateTime()]);
	}

	/**
	 * @param \Sellastica\Entity\Entity\IEntity[]|\Sellastica\MongoDB\Entity\IMongoObject[] $entities
	 * @param \Sellastica\Entity\Configuration|null $configuration
	 */
	public function batchInsert(
		array $entities,
		\Sellastica\Entity\Configuration $configuration = null
	): void
	{
		if (!$entities) {
			return;
		} elseif (sizeof($entities) === 1) {
			$this->insert(current($entities));
		} else {
			$arrays = [];
			foreach ($entities as $entity) {
				$arrays[] = $this->appendModifiedTimestamp($entity->toArray(true));
			}

			$this->getCollection($configuration)->insertMany($arrays);
		}
	}

	/**
	 * @param $id
	 * @param array $columns
	 */
	public function saveUncachedColumns($id, array $columns)
	{
		throw new \Nette\NotImplementedException();
	}

	/**
	 * @param array $ids
	 * @return array The returned array is associative due to sorting
	 *      in the repository/getEntitiesFromCacheOrStorage method
	 */
	public function getEntitiesByIds(array $ids): array
	{
		throw new \Nette\NotImplementedException();
	}

	/**
	 * Truncates the table
	 * @param \Sellastica\Entity\Configuration|null $configuration
	 */
	public function deleteAll(\Sellastica\Entity\Configuration $configuration = null)
	{
		$this->getCollection($configuration)->deleteMany([]);
	}

	/**
	 * @param $id
	 * @param \Sellastica\Entity\Configuration|null $configuration
	 */
	public function deleteById($id, \Sellastica\Entity\Configuration $configuration = null)
	{
		if (!$id instanceof \MongoDB\BSON\ObjectId) {
			try {
				$id = new \MongoDB\BSON\ObjectId($id);
			} catch (\MongoDB\Driver\Exception\InvalidArgumentException $e) {
				return;
			}
		}

		$this->getCollection($configuration)->deleteOne(['_id' => $id]);
	}

	/**
	 * Finds one column from relation table and returns as simple array
	 * @param \Sellastica\Entity\Relation\RelationGetManager $relationGetManager
	 * @return array
	 */
	public function getRelationIds(\Sellastica\Entity\Relation\RelationGetManager $relationGetManager): array
	{
		throw new \Nette\NotImplementedException();
	}

	/**
	 * Finds one single result from relation table and returns as string or integer
	 * @param \Sellastica\Entity\Relation\RelationGetManager $relationGetManager
	 * @return mixed
	 * @throws \InvalidArgumentException
	 */
	public function getRelationId(\Sellastica\Entity\Relation\RelationGetManager $relationGetManager)
	{
		throw new \Nette\NotImplementedException();
	}

	/**
	 * Finds rows (all columns) from relation table and returns as simple array or indexed array
	 * @param \Sellastica\Entity\Relation\RelationGetManager $relationGetManager
	 * @return array
	 */
	public function getRelations(\Sellastica\Entity\Relation\RelationGetManager $relationGetManager): array
	{
		throw new \Nette\NotImplementedException();
	}

	/**
	 * Finds one row (all columns) from relation table and returns as Dibi row object
	 * @param \Sellastica\Entity\Relation\RelationGetManager $relationGetManager
	 */
	public function getRelation(\Sellastica\Entity\Relation\RelationGetManager $relationGetManager)
	{
		throw new \Nette\NotImplementedException();
	}

	/**
	 * @param \Sellastica\Entity\Relation\ManyToManyRelation $relation
	 */
	public function addRelation(\Sellastica\Entity\Relation\ManyToManyRelation $relation)
	{
		throw new \Nette\NotImplementedException();
	}

	/**
	 * @param \Sellastica\Entity\Relation\ManyToManyRelation $relation
	 */
	public function removeRelation(\Sellastica\Entity\Relation\ManyToManyRelation $relation)
	{
		throw new \Nette\NotImplementedException();
	}

	/**
	 * @param array $filter
	 * @return bool
	 */
	public function existsBy(array $filter): bool
	{
		return $this->findCountBy($filter) > 0;
	}

	/**
	 * @param string $slugWithoutNumbers
	 * @param string $column
	 * @param $id
	 * @param array $groupConditions
	 * @param string $slugNumberDivider
	 * @return array
	 */
	public function findSlugs(
		string $slugWithoutNumbers,
		string $column = 'slug',
		$id = null,
		array $groupConditions = [],
		string $slugNumberDivider = '-'
	): array
	{
		throw new \Nette\NotImplementedException();
	}

	/**
	 ****************************************************************
	 ********************** FRONTEND METHODS ************************
	 ****************************************************************
	 */

	/**
	 * This method is often overridden in entity mapper
	 * @param \Sellastica\Entity\Configuration|null $configuration
	 */
	protected function getPublishableResource(\Sellastica\Entity\Configuration $configuration = null)
	{
		throw new \Nette\NotImplementedException();
	}

	/**
	 * @param \Sellastica\Entity\Configuration $configuration
	 */
	protected function getPublishableResourceWithIds(\Sellastica\Entity\Configuration $configuration = null)
	{
		throw new \Nette\NotImplementedException();
	}

	/**
	 * @param $id
	 */
	public function findPublishable($id)
	{
		throw new \Nette\NotImplementedException();
	}

	/**
	 * @param \Sellastica\Entity\Configuration|null $configuration
	 * @return array
	 */
	public function findAllPublishableIds(\Sellastica\Entity\Configuration $configuration = null): array
	{
		throw new \Nette\NotImplementedException();
	}

	/**
	 * @param array $filter
	 */
	public function findOnePublishableBy(array $filter)
	{
		throw new \Nette\NotImplementedException();
	}

	/**
	 * @param array $filter
	 * @param \Sellastica\Entity\Configuration $configuration
	 * @return array
	 */
	public function findPublishableBy(
		array $filter,
		\Sellastica\Entity\Configuration $configuration = null
	): array
	{
		throw new \Nette\NotImplementedException();
	}

	/**
	 * @param array $filter
	 * @return int
	 */
	public function findCountOfPublishableBy(array $filter): int
	{
		throw new \Nette\NotImplementedException();
	}

	/**
	 ****************************************************************
	 ********************** BACKEND METHODS *************************
	 ****************************************************************
	 */

	/**
	 * @param \Sellastica\Entity\Configuration|null $configuration
	 */
	protected function getAdminResource(\Sellastica\Entity\Configuration $configuration = null)
	{
		throw new \Nette\NotImplementedException();
	}

	/**
	 * @param \Sellastica\Entity\Configuration|null $configuration
	 */
	protected function getAdminResourceWithIds(\Sellastica\Entity\Configuration $configuration = null)
	{
		throw new \Nette\NotImplementedException();
	}
}
