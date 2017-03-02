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

ini_set('memory_limit', '1024M');


$dataset = new CsvDataset(DATA_DIR . 'nawl-analysis-2-cols.csv', 1);

$samples = [];
foreach ($dataset->getSamples() as $sample) {
    $samples[] = $sample[0];
}

function getEmotionAlias(string $emotion): string
{
    switch ($emotion) {
        case 'H': return 'happiness';
        case 'S': return 'sadness';
        case 'A': return 'anger';
        case 'F': return 'fear';
        case 'D': return 'disgust';
        case 'N': return 'neutral';
    }
    return 'unclassified';
}

function getVectorizer(array $samples): \Phpml\Transformer
{
    $serializedVectorizerPath = CACHE_DIR . 'serialized_vectorizer';

    if (file_exists($serializedVectorizerPath)) {
        return unserialize(file_get_contents($serializedVectorizerPath));
    }

    $vectorizer = new TokenCountVectorizer(new WordTokenizer());
    $vectorizer->fit($samples);
    $vectorizer->transform($samples);
    file_put_contents($serializedVectorizerPath, serialize($vectorizer));

    return $vectorizer;
}

function getTransformer(array $samples): \Phpml\Transformer
{
    $serializedTransformerPath = CACHE_DIR . 'serialized_transformer';

    if (file_exists($serializedTransformerPath)) {
        return unserialize(file_get_contents($serializedTransformerPath));
    }

    $tfIdfTransformer = new TfIdfTransformer();
    $tfIdfTransformer->fit($samples);
    $tfIdfTransformer->transform($samples);
    file_put_contents($serializedTransformerPath, serialize($tfIdfTransformer));

    return $tfIdfTransformer;
}

function getClassifier(array $samples, CsvDataset $dataset): SVC
{
    $serializedClassifierPath = CACHE_DIR . 'serialized_classifier';

    if (file_exists($serializedClassifierPath)) {
        return unserialize(file_get_contents($serializedClassifierPath));
    }
    $dataset = new ArrayDataset($samples, $dataset->getTargets());
    $randomSplit = new StratifiedRandomSplit($dataset, 0.1);
    $classifier = new SVC(Kernel::RBF, 10000);
    $classifier->train($randomSplit->getTrainSamples(), $randomSplit->getTrainLabels());
    file_put_contents(
        $serializedClassifierPath,
        serialize($classifier)
    );

    return $classifier;
}

$tfIdfTransformer = getTransformer($samples);
$vectorizer = getVectorizer($samples);
$classifier = getClassifier($samples, $dataset);

$message = 'Witam. Dziękuję za szybką przesyłkę. Wszystko dobrze działa i synek jest zadowolony.';

$newSamples= [$message];
$vectorizer->transform($newSamples);
$tfIdfTransformer->transform($newSamples);
$result = $classifier->predict($newSamples)[0];

echo "Message: $message" . PHP_EOL .
'Detected emotion: ' . getEmotionAlias($result) . PHP_EOL;
