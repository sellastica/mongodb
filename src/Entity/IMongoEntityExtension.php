<?php
namespace Sellastica\MongoDB\Entity;

interface IMongoEntityExtension
{
	/**
	 * @param bool $filter
	 * @return mixed
	 */
	function toArray(bool $filter = true): array;
}