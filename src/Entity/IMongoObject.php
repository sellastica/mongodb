<?php
namespace Sellastica\MongoDB\Entity;

interface IMongoObject
{
	/**
	 * @return string|null
	 */
	function getId();

	/**
	 * @return \MongoDB\BSON\ObjectId|string|null
	 */
	function getObjectId(): ?\MongoDB\BSON\ObjectId;

	/**
	 * @param \MongoDB\BSON\ObjectId|null $objectId
	 */
	function setObjectId(?\MongoDB\BSON\ObjectId $objectId): void;

	/**
	 * @param bool $filter
	 * @return mixed
	 */
	function toArray(bool $filter = true): array;
}