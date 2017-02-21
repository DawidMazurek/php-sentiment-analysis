<?php

declare(strict_types = 1);

use Phpml\Dataset\CsvDataset;
use Phpml\Dataset\ArrayDataset;
use Phpml\FeatureExtraction\TokenCountVectorizer;
use Phpml\Tokenization\WordTokenizer;
use Phpml\CrossValidation\StratifiedRandomSplit;
use Phpml\FeatureExtraction\TfIdfTransformer;
use Phpml\Classification\SVC;
use Phpml\SupportVectorMachine\Kernel;

require __DIR__ . '/vendor/autoload.php';

define('CACHE_DIR', __DIR__ . '/cache/');
define('DATA_DIR', __DIR__ . '/data/');

$serializedVectorizerPath = CACHE_DIR . 'serialized_vectorizer';
$serializedTransformerPath = CACHE_DIR . 'serialized_transformer';
$serializedClassifierPath = CACHE_DIR . 'serialized_classifier';

ini_set('memory_limit', '1024M');
$dataset = new CsvDataset(DATA_DIR . 'nawl-analysis-2-cols.csv', 1);

$samples = [];
foreach ($dataset->getSamples() as $sample) {
    $samples[] = $sample[0];
}

if (file_exists($serializedVectorizerPath)) {
    $vectorizer = unserialize(file_get_contents($serializedVectorizerPath));
}
else {
    $vectorizer = new TokenCountVectorizer(new WordTokenizer());
    $vectorizer->fit($samples);
    $vectorizer->transform($samples);
    file_put_contents($serializedVectorizerPath, serialize($vectorizer));
}

if (file_exists($serializedTransformerPath)) {
    $tfIdfTransformer = unserialize(file_get_contents($serializedTransformerPath));
}
else {
    $tfIdfTransformer = new TfIdfTransformer();
    $tfIdfTransformer->fit($samples);
    $tfIdfTransformer->transform($samples);
    file_put_contents($serializedTransformerPath, serialize($tfIdfTransformer));
}

if (file_exists($serializedClassifierPath)) {
    $classifier = unserialize(file_get_contents($serializedClassifierPath));
}
else {
    $dataset = new ArrayDataset($samples, $dataset->getTargets());
    $randomSplit = new StratifiedRandomSplit($dataset, 0.1);
    $classifier = new SVC(Kernel::RBF, 10000);
    $classifier->train($randomSplit->getTrainSamples(), $randomSplit->getTrainLabels());
    file_put_contents(
        $serializedClassifierPath,
        serialize($classifier)
    );
}


$message = 'Aha w ten sposób ? 
Myślę że nie będzie z tym problemu.
Tylko nie wiem czy będę mógł w tej wiadomości wysłać panu linka do strony z aukcją. Bo nie wiem czy mi nie zablokuje jakiś filtr spamu czy coś.';

$newSamples= [$message];
$vectorizer->transform($newSamples);
$tfIdfTransformer->transform($newSamples);
$result = $classifier->predict($newSamples);
var_dump($result);
