<?php
namespace Sellastica\MongoDB\Entity;

class SubdocumentCollection implements \Countable, \ArrayAccess, \IteratorAggregate
{
	/** @var \Sellastica\MongoDB\Entity\IMongoObject[] */
	protected $items = [];


	/**
	 * @param array $items
	 */
	public function __construct(array $items = [])
	{
		$this->items = $items;
	}

	/**
	 * Immutable
	 *
	 * @param callable $function
	 * @return SubdocumentCollection|\Sellastica\MongoDB\Entity\IMongoObject[]
	 */
	public function filter(callable $function): SubdocumentCollection
	{
		$return = new static();
		foreach ($this->items as $key => $item) {
			$result = $function($item);
			if (true === $result) {
				$return[$key] = $item;
			} elseif (!is_bool($result)) {
				throw new \Exception('Filter callback must return boolean');
			}
		}

		return $return;
	}

	/**
	 * @param \Sellastica\MongoDB\Entity\IMongoObject $item
	 */
	public function add(\Sellastica\MongoDB\Entity\IMongoObject $item): void
	{
		$this->items[] = $item;
	}

	/**
	 * @param $key
	 */
	public function unset($key): void
	{
		unset($this->items[$key]);
	}

	/**
	 * @param \MongoDB\BSON\ObjectId|string $id
	 */
	public function remove($id): void
	{
		foreach ($this->items as $key => $item) {
			if ($item->getId() === (string)$id) {
				$this->unset($key);
			}
		}
	}

	/**
	 * @param callable $callback
	 * @return null|IMongoObject
	 */
	public function findOneByCallback(callable $callback): ?IMongoObject
	{
		return $this->filter($callback)->first();
	}

	/**
	 * @param \MongoDB\BSON\ObjectId|string $id
	 * @return \Sellastica\MongoDB\Entity\IMongoObject|null
	 */
	public function findOneById($id): ?\Sellastica\MongoDB\Entity\IMongoObject
	{
		return $this->findOneByCallback(function($v) use ($id) {
			return (string)$v->getObjectId() === (string)$id;
		});
	}

	/**
	 * @param \MongoDB\BSON\ObjectId|string $id
	 * @return bool
	 */
	public function has($id): bool
	{
		return (bool)$this->findOneById($id);
	}

	/**
	 * Returns first item or null if no items exist
	 * @return \Sellastica\MongoDB\Entity\IMongoObject|null
	 */
	public function first(): ?\Sellastica\MongoDB\Entity\IMongoObject
	{
		if (!sizeof($this->items)) {
			return null;
		}

		reset($this->items);
		return current($this->items);
	}

	/**
	 * @param callable $function
	 */
	public function walk(callable $function): void
	{
		foreach ($this->items as $key => $item) {
			$this[$key] = $function($item);
		}
	}

	public function clear(): void
	{
		$this->items = [];
	}

	/**
	 * @param SubdocumentCollection $collection
	 * @return SubdocumentCollection
	 */
	public function diff(SubdocumentCollection $collection): SubdocumentCollection
	{
		$result = $this->clone();
		foreach ($collection as $item) {
			$result->remove($item->getId());
		}

		return $result;
	}

	/**
	 * @return array
	 */
	public function toArray(): array
	{
		return $this->items;
	}

	/**
	 * @param string $separator
	 * @return string
	 */
	public function toString($separator = ','): string
	{
		$array = [];
		foreach ($this->items as $item) {
			$array[] = (string)$item;
		}

		return implode($separator, $array);
	}

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->toString();
	}

	/**
	 * @return static
	 */
	public function clone(): SubdocumentCollection
	{
		return clone $this;
	}

	/****************************************************************
	 ******************* Interface implementations ******************
	 ****************************************************************/

	/**
	 * @return int
	 */
	public function count(): int
	{
		return count($this->items);
	}

	/**
	 * @param int|null $key
	 * @param mixed $value
	 */
	public function offsetSet($key, $value): void
	{
		if (!isset($key)) {
			$this->items[] = $value;
		} else {
			$this->items[$key] = $value;
		}
	}

	/**
	 * @param mixed $key
	 */
	public function offsetUnset($key): void
	{
		if (isset($this->items[$key])) {
			unset($this->items[$key]);
		}
	}

	/**
	 * @param int $key
	 * @return \Sellastica\MongoDB\Entity\IMongoObject|null
	 */
	public function offsetGet($key): ?\Sellastica\MongoDB\Entity\IMongoObject
	{
		if (isset($this->items[$key])) {
			return $this->items[$key];
		}

		return null;
	}

	/**
	 * @param int $key
	 * @return bool
	 */
	public function offsetExists($key): bool
	{
		return isset($this->items[$key]);
	}

	/**
	 * @return \ArrayIterator
	 */
	public function getIterator(): \ArrayIterator
	{
		return new \ArrayIterator($this->items);
	}
}