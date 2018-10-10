<?php
namespace Sellastica\MongoDB\Model;

trait THydrator
{
	/**
	 * @param iterable $data
	 */
	private function hydrate(iterable $data): void
	{
		foreach ($data as $property => $value) {
			if ($property === 'id') {
				$this->id = $value;
				continue;
			}

			$method = "set$property";
			if (method_exists($this, $method)) {
				$this->$method($value);
			}
		}
	}
}