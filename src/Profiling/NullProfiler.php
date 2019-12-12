<?php
namespace Sellastica\MongoDB\Profiling;

class NullProfiler implements IProfiler
{
	/**
	 * @return int
	 */
	public function getSelectsCount(): int
	{
		return 0;
	}

	public function addSelect(): void
	{
	}

	/**
	 * @return int
	 */
	public function getUpdatesCount(): int
	{
		return 0;
	}

	public function addUpdate(): void
	{
	}

	/**
	 * @return int
	 */
	public function getInsertsCount(): int
	{
		return 0;
	}

	public function addInsert(): void
	{
	}
}
