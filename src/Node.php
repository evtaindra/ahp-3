<?php

namespace Gandung\AHP;

/**
 * @author Paulus Gandung Prakosa <rvn.plvhx@gmail.com>
 */
class Node implements NodeInterface
{
	/**
	 * @var array
	 */
	private $node;

	/**
	 * @var string
	 */
	private $name;

	public function __construct($value = null, $name = null)
	{
		$this->name = $name;
		$this->node = $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setValue($value)
	{
		$this->node = $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getValue()
	{
		return $this->node;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return is_null($this->name)
			? 'default'
			: $this->name;
	}
}