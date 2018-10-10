<?php
namespace Sellastica\MongoDB\Entity;

trait TMongoExtension
{
	/** @var IMongoEntityExtension[] */
	protected $extensions = [];


	/**
	 * @param IMongoEntityExtension $extension
	 */
	public function addExtension(IMongoEntityExtension $extension): void
	{
		$this->extensions[get_class($extension)] = $extension;
	}

	/**
	 * @param string $className
	 * @return null|IMongoEntityExtension
	 */
	public function getExtensionByClassName(string $className): ?IMongoEntityExtension
	{
		return $this->extensions[$className] ?? null;
	}

	/**
	 * @return IMongoEntityExtension[]
	 */
	public function getExtensions(): array
	{
		return $this->extensions;
	}

	/**
	 * @param bool $filter
	 * @return array
	 */
	public function extensionsToArray(bool $filter = true): array
	{
		$array = [];
		foreach ($this->extensions as $extension) {
			$array = array_merge($array, $extension->toArray($filter));
		}

		return $array;
	}
}