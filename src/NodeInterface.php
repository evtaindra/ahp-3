<?php

namespace Gandung\AHP;

/**
 * @author Paulus Gandung Prakosa <rvn.plvhx@gmail.com>
 */
interface NodeInterface
{
	/**
	 * Set node value.
	 *
	 * @param mixed $value
	 * @return void
	 */
	public function setValue($value);

	/**
	 * Get node value
	 *
	 * @return mixed
	 */
	public function getValue();

	/**
	 * Set node name
	 *
	 * @param string $name
	 * @return void
	 */
	public function setName($name);
	
	/**
	 * Get node name
	 *
	 * @return string
	 */
	public function getName();
}