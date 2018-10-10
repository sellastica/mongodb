<?php
namespace Sellastica\MongoDB\Entity;

trait TMongoObject
{
	/** @var \MongoDB\BSON\ObjectId|null */
	protected $id;


	/**
	 * @return string|null
	 */
	public function getId(): ?string
	{
		return $this->id ? $this->id->__toString() : null;
	}

	/**
	 * @return \MongoDB\BSON\ObjectId|string|null
	 */
	public function getObjectId(): ?\MongoDB\BSON\ObjectId
	{
		return $this->id;
	}

	/**
	 * @param \MongoDB\BSON\ObjectId|null $id
	 */
	public function setObjectId(?\MongoDB\BSON\ObjectId $id): void
	{
		$this->id = $id;
	}

	/**
	 * @return array
	 */
	public function mongoTraitToArray(): array
	{
		return [
			'_id' => $this->id,
		];
	}
}