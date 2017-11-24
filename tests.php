<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

use Gandung\AHP\CriterionNode;
use Gandung\AHP\ValueNode;
use Gandung\AHP\PairwiseComparisonBuilder;
use Gandung\AHP\CriterionValidator;

$base = [
	[
		new CriterionNode(new ValueNode(1), 'kedisiplinan'),
		new CriterionNode(new ValueNode(2), 'prestasi_kerja'),
		new CriterionNode(new ValueNode(2), 'pengalaman_kerja'),
		new CriterionNode(new ValueNode(3), 'perilaku')
	]
];

$disiplin = [
	[
		new CriterionNode(new ValueNode(1), 'baik'),
		new CriterionNode(new ValueNode(3), 'cukup'),
		new CriterionNode(new ValueNode(5), 'kurang')
	]
];

$prestasi = [
	[
		new CriterionNode(new ValueNode(1), 'baik'),
		new CriterionNode(new ValueNode(2), 'cukup'),
		new CriterionNode(new ValueNode(6), 'kurang')
	]
];

$pengalaman = [
	[
		new CriterionNode(new ValueNode(1), 'baik'),
		new CriterionNode(new ValueNode(3), 'cukup'),
		new CriterionNode(new ValueNode(4), 'kurang')
	]
];

$perilaku = [
	[
		new CriterionNode(new ValueNode(1), 'baik'),
		new CriterionNode(new ValueNode(2), 'cukup'),
		new CriterionNode(new ValueNode(5), 'kurang')
	]
];

$alternative = [
	['cukup', 'cukup', 'baik', 'baik'],
	['baik', 'kurang', 'cukup', 'cukup'],
	['cukup', 'baik', 'baik', 'baik']
];

$basePriority = new CriterionValidator(new PairwiseComparisonBuilder($base));
$disiplinPriority = new CriterionValidator(new PairwiseComparisonBuilder($disiplin));
$prestasiPriority = new CriterionValidator(new PairwiseComparisonBuilder($prestasi));
$pengalamanPriority = new CriterionValidator(new PairwiseComparisonBuilder($pengalaman));
$perilakuPriority = new CriterionValidator(new PairwiseComparisonBuilder($perilaku));

$a = array_values($basePriority->validate());
$b = [
	$disiplinPriority->validate(true),
	$prestasiPriority->validate(true),
	$pengalamanPriority->validate(true),
	$perilakuPriority->validate(true)
];
$normalizedAlternative = [];

for ($i = 0; $i < sizeof($alternative); $i++) {
	$normalizedAlternative[$i] = [];
	for ($j = 0; $j < sizeof($b); $j++) {
		$normalizedAlternative[$i][] = $b[$j][$alternative[$i][$j]] * $a[$j];
	}
}

$preference = array_map(function($q) {
	return array_sum($q);
}, $normalizedAlternative);

dump($preference);
