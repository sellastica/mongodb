<?php
namespace Sellastica\MongoDB\Mapping;

interface IDatabaseFactory
{
	/**
	 * @return \MongoDB\Database
	 */
	function create(): \MongoDB\Database;
}