<?php

namespace Gandung\AHP;

/**
 * @author Paulus Gandung Prakosa <rvn.plvhx@gmail.com>
 */
class CriterionValidator implements CriterionValidatorInterface
{
	/**
	 * @var PairwiseComparisonBuilder
	 */
	private $comparisonBuilder;

	/**
	 * @var array
	 */
	const INDEX_RATIO_LOOKUP = [
		1 => 0,
		2 => 0,
		3 => 0.58,
		4 => 0.9,
		5 => 1.12,
		6 => 1.24,
		7 => 1.32,
		8 => 1.41,
		9 => 1.45,
		10 => 1.49,
		11 => 1.51,
		12 => 1.48,
		13 => 1.56,
		14 => 1.57,
		15 => 1.59
	];

	public function __construct(PairwiseComparisonBuilder $comparisonBuilder)
	{
		$this->comparisonBuilder = $comparisonBuilder;
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate($isSub = false)
	{
		$key = array_map(function($q) {
			return $q->getName();
		}, $this->comparisonBuilder->getInitialWeight());
		$sumPerCriterion = $this->synthesizeMatrix();
		$normalizedCriterion = $this->normalizeMatrix($sumPerCriterion);
		$priority = $this->getPriorityFromNormalizedMatrix($normalizedCriterion, $isSub);
		$summedCriterion = $this->sumPerRowMatrix($priority);
		$totalFromSummedCriterion = $this->getTotalFromSumPerRowMatrix($summedCriterion);
		$consistencyRatioPerRow = $this->getConsistencyRatioPerRow(
			$totalFromSummedCriterion,
			$priority
		);

		$finalSum = array_sum($consistencyRatioPerRow);
		$lambdaMaximum = $this->getLambdaMaximum($finalSum);
		$consistencyIndex = $this->getConsistencyIndex($lambdaMaximum);
		$consistencyRatio = $this->getConsistencyRatio($consistencyIndex);

		return $consistencyRatio < 0.1
			? array_combine($key, $priority)
			: null;
	}

	/**
	 * Aggregate each criterion column using sum function.
	 *
	 * @return array
	 */
	private function synthesizeMatrix()
	{
		$matrix = $this->comparisonBuilder->build();
		$sumPerMatrix = [];
		$count = sizeof($matrix);

		for ($i = 0; $i < $count; $i++) {
			for ($j = 0; $j < $count; $j++) {
				$tmp = $j == 0
					? $matrix[$j][$i]->getValue()->getValue()
					: $tmp + $matrix[$j][$i]->getValue()->getValue();
			}

			$sumPerMatrix[] = $tmp;
		}

		return $sumPerMatrix;
	}

	/**
	 * Normalize each elements in the matrix by dividing it against
	 * sum of each own column.
	 *
	 * @param array $sumPerMatrix
	 * @return array
	 */
	private function normalizeMatrix($sumPerMatrix)
	{
		$orig = $this->comparisonBuilder->build();
		$normalized = [];

		for ($i = 0; $i < sizeof($orig); $i++) {
			$normalized[$i] = [];
			for ($j = 0; $j < sizeof($orig); $j++) {
				$normalized[$i][] = new CriterionNode(
					new ValueNode(
						$orig[$i][$j]->getValue()->getValue() / $sumPerMatrix[$j]
					),
					$orig[$i][$j]->getName()
				);
			}
		}

		return $normalized;
	}

	/**
	 * Get priority from each row by summing each row value and
	 * dividing it by it's row count.
	 *
	 * @param array $matrix
	 * @return array
	 */
	private function getPriorityFromNormalizedMatrix($matrix, $isSub = false)
	{
		$priority = [];

		for ($i = 0; $i < sizeof($matrix); $i++) {
			$normalizedSubArray = array_map(function($q) {
				return $q->getValue()->getValue();
			}, $matrix[$i]);
			$priority[] = array_reduce($normalizedSubArray, function($x, $y) {
				$x += $y;

				return $x;
			}) / sizeof($normalizedSubArray);
		}

		return $isSub === false
			? $priority
			: array_map(function($q) use ($priority) {
				return $q / $priority[0];
			}, $priority);
	}

	/**
	 * Normalizing matrix of base criterion by multiplying
	 * each elements per row against it's own priority.
	 *
	 * @param array $priority
	 * @return array
	 */
	private function sumPerRowMatrix($priority)
	{
		$orig = $this->comparisonBuilder->build();
		$normalized = [];

		for ($i = 0; $i < sizeof($orig); $i++) {
			$normalized[$i] = [];
			for ($j = 0; $j < sizeof($orig); $j++) {
				$normalized[$i][] = new CriterionNode(
					new ValueNode(
						$orig[$i][$j]->getValue()->getValue() * $priority[$j]
					),
					$orig[$i][$j]->getName()
				);
			}
		}

		return $normalized;
	}

	/**
	 * Aggregate each second-step normalized matrix using sum
	 * function.
	 *
	 * @param array $matrix
	 * @return array
	 */
	private function getTotalFromSumPerRowMatrix($matrix)
	{
		$sumPerRowMatrix = [];

		for ($i = 0; $i < sizeof($matrix); $i++) {
			$normalizedSubArray = array_map(function($q) {
				return $q->getValue()->getValue();
			}, $matrix[$i]);
			$sumPerRowMatrix[] = array_sum($normalizedSubArray);
		}

		return $sumPerRowMatrix;
	}

	/**
	 * Get consistency ratio per row from second-step
	 * normalized matrix.
	 *
	 * @param array $sum
	 * @param array $priority
	 */
	private function getConsistencyRatioPerRow($sum, $priority)
	{
		for ($i = 0; $i < sizeof($sum); $i++) {
			$sum[$i] += $priority[$i];
		}

		return $sum;
	}

	private function dumpMatrix($criterion)
	{
		return array_map(function($sub) {
			return array_map(function($q) {
				return $q->getValue()->getValue();
			}, $sub);
		}, $criterion);
	}

	/**
	 * Get maximum eigenvalue from sum of current processed
	 * matrix.
	 *
	 * @param array $sum
	 * @return double
	 */
	private function getLambdaMaximum($sum)
	{
		$matrixCount = $this->comparisonBuilder->getInitialWeightLength();

		return $sum / $matrixCount;
	}

	/**
	 * Get consistency index from given maximum eigenvalue.
	 *
	 * @param double $lambdaMax
	 * @return double
	 */
	private function getConsistencyIndex($lambdaMax)
	{
		$matrixCount = $this->comparisonBuilder->getInitialWeightLength();

		return ($lambdaMax - $matrixCount) / $matrixCount;
	}

	/**
	 * Get consistency ratio from given consistency index.
	 *
	 * @param double $consistencyIndex
	 * @return double
	 */
	private function getConsistencyRatio($consistencyIndex)
	{
		$matrixCount = $this->comparisonBuilder->getInitialWeightLength();

		return $consistencyIndex / self::INDEX_RATIO_LOOKUP[$matrixCount];
	}
}