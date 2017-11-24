<?php

namespace Gandung\AHP;

/**
 * @author Paulus Gandung Prakosa <rvn.plvhx@gmail.com>
 */
interface PairwiseComparisonBuilderInterface
{
	public function build();

	public function getInitialWeightLength();
}