<?php

namespace Gandung\AHP;

/**
 * @author Paulus Gandung Prakosa <rvn.plvhx@gmail.com>
 */
class PairwiseComparisonBuilder implements PairwiseComparisonBuilderInterface
{
	/**
	 * @var array
	 */
	private $weight;

	public function __construct($weight)
	{
		$this->weight = $weight;
	}

	/**
	 * {@inheritdoc}
	 */
	public function build()
	{
		$matrix = [];
		$matrix[] = $this->weight[0];
		$t = $matrix[0];

		for ($i = 1; $i < sizeof($t); $i++) {
			$tmp = [];
			$tval = $i;
			for ($j = 0; $j < sizeof($t); $j++) {
				if ($j == $i) {
					$tmp[] = new CriterionNode(new ValueNode(1), $t[$j]->getName());
				} else if ($j < $i) {
					$tmp[] = new CriterionNode(
						new ValueNode(
							$t[0]->getValue()->getValue() /
							$t[$tval--]->getValue()->getValue()
						),
						$t[$j]->getName()
					);
				} else {
					$tmp[] = new CriterionNode(
						new ValueNode($t[sizeof($t) - $j]->getValue()->getValue()),
						$t[$j]->getName()
					);
				}
			}
			$matrix[] = $tmp;
		}

		return $matrix;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getInitialWeight()
	{
		return $this->weight[0];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getInitialWeightLength()
	{
		return sizeof($this->weight[0]);
	}
}