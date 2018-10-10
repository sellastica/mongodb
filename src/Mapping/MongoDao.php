<?php
namespace Sellastica\MongoDB\Mapping;

abstract class MongoDao implements \Sellastica\Entity\Mapping\IDao
{
	/** @var \Sellastica\MongoDB\Mapping\MongoMapper */
	protected $mapper;
	/** @var \Sellastica\Entity\Entity\EntityFactory */
	protected $entityFactory;


	/**
	 * @param \Sellastica\Entity\Mapping\IMapper $mapper
	 * @param \Sellastica\Entity\Entity\EntityFactory $entityFactory
	 */
	public function __construct(
		\Sellastica\Entity\Mapping\IMapper $mapper,
		\Sellastica\Entity\Entity\EntityFactory $entityFactory
	)
	{
		$this->mapper = $mapper;
		$this->entityFactory = $entityFactory;
	}

	/**
	 * @return \MongoDB\BSON\ObjectId
	 */
	public function nextIdentity(): \MongoDB\BSON\ObjectId
	{
		return $this->mapper->nextIdentity();
	}

	/**
	 * @param \Sellastica\MongoDB\Model\BSONDocument|null $document
	 * @param mixed $first
	 * @param mixed $second
	 * @return \Sellastica\Entity\Entity\IEntity|null
	 */
	protected function createEntity(
		?\Sellastica\MongoDB\Model\BSONDocument $document,
		$first = null,
		$second = null
	): ?\Sellastica\Entity\Entity\IEntity
	{
		if (!isset($document)) {
			return null;
		}

		$document->created = new \DateTime();
		$document->created->setTimestamp($document->id->getTimestamp());

		$builder = $this->getBuilder($document, $first, $second);
		$entity = $this->entityFactory->build($builder, false);

		$metadata = $entity->getEntityMetadata();
		$metadata->setCreated($document->created);
		$metadata->setModified($document->modified);

		$this->completeEntity($entity, $document);

		return $entity;
	}

	/**
	 * @param $document
	 * @param mixed $first
	 * @param mixed $second
	 * @return \Sellastica\Entity\IBuilder
	 */
	abstract protected function getBuilder($document, $first = null, $second = null): \Sellastica\Entity\IBuilder;

	/**
	 * @param \Sellastica\Entity\Entity\IEntity $entity
	 * @param \Sellastica\MongoDB\Model\BSONDocument $document
	 */
	abstract protected function completeEntity(
		\Sellastica\Entity\Entity\IEntity $entity,
		\Sellastica\MongoDB\Model\BSONDocument $document
	): void;

	/**
	 * @param \MongoDB\Driver\Cursor $cursor
	 * @return \Sellastica\Entity\Entity\EntityCollection
	 */
	protected function createCollection(\MongoDB\Driver\Cursor $cursor): \Sellastica\Entity\Entity\EntityCollection
	{
		$collection = $this->getEmptyCollection();
		foreach ($cursor as $row) {
			$collection[] = $this->createEntity($row);
		}

		return $collection;
	}

	/**
	 * @param int|null $id
	 * @param mixed $first
	 * @param mixed $second
	 * @return \Sellastica\Entity\Entity\IEntity|null
	 */
	public function find($id, $first = null, $second = null)
	{
		if (empty($id)) {
			return null;
		}

		$row = $this->mapper->find($id);
		return $this->createEntity($row, $first, $second);
	}

	/**
	 * @param $id
	 * @param array $fields
	 * @return void
	 * @throws \Nette\NotImplementedException
	 */
	public function findFields($id, array $fields)
	{
		throw new \Nette\NotImplementedException();
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
	 * @param array $filterValues
	 * @param \Sellastica\Entity\Configuration|null $configuration
	 * @return array
	 */
	public function findFieldBy(
		string $field,
		array $filterValues,
		\Sellastica\Entity\Configuration $configuration = null
	): array
	{
		throw new \Nette\NotImplementedException();
	}

	/**
	 * Method similar to find(), except that the first parameter is an array with entity IDs
	 *
	 * @param array $idsArray
	 * @param \Sellastica\Entity\Configuration $configuration
	 * @return \Sellastica\Entity\Entity\EntityCollection
	 */
	public function findByIds(
		array $idsArray,
		\Sellastica\Entity\Configuration $configuration = null
	): \Sellastica\Entity\Entity\EntityCollection
	{
		throw new \Nette\NotImplementedException();
	}

	/**
	 * @param \Sellastica\Entity\Configuration $configuration
	 * @param null $first
	 * @param null $second
	 * @return \Sellastica\Entity\Entity\EntityCollection
	 */
	public function findAll(
		\Sellastica\Entity\Configuration $configuration = null,
		$first = null,
		$second = null
	): \Sellastica\Entity\Entity\EntityCollection
	{
		$documents = $this->mapper->findAllIds($configuration);
		return $this->createCollection($documents);
	}

	/**
	 * @param array $filterValues
	 * @param \Sellastica\Entity\Configuration $configuration
	 * @param null $first
	 * @param null $second
	 * @return \Sellastica\Entity\Entity\EntityCollection
	 */
	public function findBy(
		array $filterValues,
		\Sellastica\Entity\Configuration $configuration = null,
		$first = null,
		$second = null
	): \Sellastica\Entity\Entity\EntityCollection
	{
		$documents = $this->mapper->findBy($filterValues, $configuration);
		return $this->createCollection($documents);
	}

	/**
	 * @param \Sellastica\Entity\Entity\ConditionCollection $conditions
	 * @param \Sellastica\Entity\Configuration $configuration
	 * @return \Sellastica\Entity\Entity\EntityCollection
	 */
	public function findByConditions(
		\Sellastica\Entity\Entity\ConditionCollection $conditions,
		\Sellastica\Entity\Configuration $configuration = null
	): \Sellastica\Entity\Entity\EntityCollection
	{
		throw new \Nette\NotImplementedException();
	}

	/**
	 * @param string $column
	 * @param array $values
	 * @param string $modifier
	 * @param \Sellastica\Entity\Configuration $configuration
	 * @return \Sellastica\Entity\Entity\EntityCollection
	 */
	public function findIn(
		string $column,
		array $values,
		string $modifier = 's',
		\Sellastica\Entity\Configuration $configuration = null
	): \Sellastica\Entity\Entity\EntityCollection
	{
		throw new \Nette\NotImplementedException();
	}

	/**
	 * @param array $filterValues
	 * @param \Sellastica\Entity\Configuration|null $configuration
	 * @return \Sellastica\Entity\Entity\IEntity|null
	 */
	public function findOneBy(
		array $filterValues,
		\Sellastica\Entity\Configuration $configuration = null
	): ?\Sellastica\Entity\Entity\IEntity
	{
		$row = $this->mapper->findOneBy($filterValues, $configuration);
		return $this->createEntity($row);
	}

	/**
	 * @return int
	 */
	public function findCount(): int
	{
		return $this->mapper->findCount();
	}

	/**
	 * @param array $filterValues
	 * @return int
	 */
	public function findCountBy(array $filterValues): int
	{
		return $this->mapper->findCountBy($filterValues);
	}

	/**
	 * @param string|null $key
	 * @param string $value
	 * @param array $filterValues
	 * @param \Sellastica\Entity\Configuration $configuration
	 * @return array
	 */
	public function findPairs(
		string $key = null,
		string $value,
		array $filterValues = [],
		\Sellastica\Entity\Configuration $configuration = null
	): array
	{
		return $this->mapper->findPairs($key, $value, $filterValues, $configuration);
	}

	/**
	 * @return \Sellastica\Entity\Entity\EntityCollection
	 */
	abstract public function getEmptyCollection(): \Sellastica\Entity\Entity\EntityCollection;

	/**
	 * Deletes all records
	 */
	public function deleteAll()
	{
		$this->mapper->deleteAll();
	}

	/**
	 * @param $id
	 */
	public function deleteById($id)
	{
		$this->mapper->deleteById($id);
	}

	/**
	 * @param \Sellastica\Entity\Relation\RelationGetManager $relationGetManager
	 * @return mixed
	 */
	public function getRelationIds(\Sellastica\Entity\Relation\RelationGetManager $relationGetManager): array
	{
		throw new \Nette\NotImplementedException();
	}

	/**
	 * @param \Sellastica\Entity\Relation\RelationGetManager $relationGetManager
	 * @return mixed
	 */
	public function getRelationId(\Sellastica\Entity\Relation\RelationGetManager $relationGetManager)
	{
		throw new \Nette\NotImplementedException();
	}

	/**
	 * @param \Sellastica\Entity\Relation\RelationGetManager $relationGetManager
	 * @return mixed
	 */
	public function getRelations(\Sellastica\Entity\Relation\RelationGetManager $relationGetManager): array
	{
		throw new \Nette\NotImplementedException();
	}

	/**
	 * @param \Sellastica\Entity\Relation\RelationGetManager $relationGetManager
	 * @return mixed
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
	 * @param $id
	 * @return bool
	 */
	public function exists($id): bool
	{
		$entity = $this->find($id);
		return isset($entity);
	}

	/**
	 * @param array $filterValues
	 * @return bool
	 */
	public function existsBy(array $filterValues): bool
	{
		return $this->mapper->existsBy($filterValues);
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
	 * @param int|null $id
	 * @return void
	 * @throws \Nette\NotImplementedException
	 */
	public function findPublishable($id = null)
	{
		throw new \Nette\NotImplementedException();
	}

	/**
	 * @param \Sellastica\Entity\Configuration $configuration
	 * @return \Sellastica\Entity\Entity\EntityCollection
	 */
	public function findAllPublishable(
		\Sellastica\Entity\Configuration $configuration = null
	): \Sellastica\Entity\Entity\EntityCollection
	{
		throw new \Nette\NotImplementedException();
	}

	/**
	 * @param array $filterValues
	 * @return void
	 * @throws \Nette\NotImplementedException
	 */
	public function findOnePublishableBy(array $filterValues)
	{
		throw new \Nette\NotImplementedException();
	}

	/**
	 * @param array $filterValues
	 * @param \Sellastica\Entity\Configuration $configuration
	 * @return void
	 * @throws \Nette\NotImplementedException
	 */
	public function findPublishableBy(
		array $filterValues,
		\Sellastica\Entity\Configuration $configuration = null
	)
	{
		throw new \Nette\NotImplementedException();
	}

	/**
	 * @param array $filterValues
	 * @return int
	 */
	public function findCountOfPublishableBy(array $filterValues): int
	{
		throw new \Nette\NotImplementedException();
	}

	/**
	 * @param int $entityId
	 * @param array $columns
	 */
	public function saveUncachedColumns(int $entityId, array $columns)
	{
		throw new \Nette\NotImplementedException();
	}

	/**
	 * @param \Sellastica\Entity\Entity\IEntity $entity
	 */
	public function save(\Sellastica\Entity\Entity\IEntity $entity)
	{
		$state = $entity->getEntityMetadata()->getState();
		if ($state->isNew()) {
			$this->mapper->insert($entity);
			$entity->getEntityMetadata()->setState(\Sellastica\Entity\Entity\EntityState::persisted());
		} elseif ($state->isPersisted() && $entity->isChanged()) {
			$this->mapper->update($entity);
		}

		$entity->updateOriginalData();
	}

	/**
	 * @param \Sellastica\Entity\Entity\IEntity $entity
	 */
	public function update(\Sellastica\Entity\Entity\IEntity $entity): void
	{
		$this->mapper->update($entity);
		$entity->updateOriginalData();
	}

	/**
	 * @param \Sellastica\Entity\Entity\IEntity[] $entities
	 */
	public function batchInsert(array $entities): void
	{
		$this->mapper->batchInsert($entities);
		foreach ($entities as $entity) {
			$entity->getEntityMetadata()->setState(\Sellastica\Entity\Entity\EntityState::persisted());
			$entity->updateOriginalData();
		}
	}
}