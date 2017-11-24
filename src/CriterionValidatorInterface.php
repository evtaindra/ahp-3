<?php

namespace Gandung\AHP;

/**
 * @author Paulus Gandung Prakosa <rvn.plvhx@gmail.com>
 */
interface CriterionValidatorInterface
{
	/**
	 * Validate given pairwise comparison matrix to produce
	 * it's priority
	 *
	 * @return array
	 */
	public function validate();
}