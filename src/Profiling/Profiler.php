<?php
namespace Sellastica\MongoDB\Profiling;

class Profiler implements IProfiler
{
	/** @var int */
	private $selectsCount = 0;
	/** @var int */
	private $updatesCount = 0;
	/** @var int */
	private $insertsCount = 0;


	/**
	 * @return int
	 */
	public function getSelectsCount(): int
	{
		return $this->selectsCount;
	}

	public function addSelect(): void
	{
		$this->selectsCount++;
	}

	/**
	 * @return int
	 */
	public function getUpdatesCount(): int
	{
		return $this->updatesCount;
	}

	public function addUpdate(): void
	{
		$this->updatesCount++;
	}

	/**
	 * @return int
	 */
	public function getInsertsCount(): int
	{
		return $this->insertsCount;
	}

	public function addInsert(): void
	{
		$this->insertsCount++;
	}
}
