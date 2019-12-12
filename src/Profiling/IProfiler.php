<?php
namespace Sellastica\MongoDB\Profiling;

interface IProfiler
{
	/**
	 * @return int
	 */
	function getSelectsCount(): int;

	function addSelect(): void;

	/**
	 * @return int
	 */
	function getUpdatesCount(): int;

	function addUpdate(): void;

	/**
	 * @return int
	 */
	function getInsertsCount(): int;

	function addInsert(): void;
}
