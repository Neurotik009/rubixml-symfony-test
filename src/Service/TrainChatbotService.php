<?php

namespace App\Service;

use App\Helper\CsvReaderHelper;
use Rubix\ML\Classifiers\KNearestNeighbors;
use Rubix\ML\CrossValidation\KFold;
use Rubix\ML\CrossValidation\Metrics\Accuracy;
use Rubix\ML\CrossValidation\Metrics\FBeta;
use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\Kernels\Distance\Manhattan;
use Rubix\ML\Transformers\MultibyteTextNormalizer;
use Rubix\ML\Transformers\WordCountVectorizer;
use stdClass;
use Symfony\Component\Console\Output\OutputInterface;
use Rubix\ML\Datasets\Labeled;
use Symfony\Component\Finder\Finder;
use Rubix\ML\Tokenizers\Word;

/**
 * Needs tons of training data, accuracy is almost zero
 *
 * TODO: Add Training Questions and Testing Data
 */
class TrainChatbotService
{
    // Where the model file is saved
    private string $modelPath = __DIR__ . '/../../var/model/chatbot_model.rbx';

    // Where the training data is found
    private string $datasetFilePath = __DIR__ . '/../../var/train_data/';

    // The initializing the dataset of type Labeled
    private Labeled $dataset;

    public function __construct()
    {
        $this->dataset = new Labeled(array(), array());
    }

    private function buildDataset(OutputInterface $output, $filename = false): stdClass
    {
        $filenames = $this->getFilesFromDirectory($filename);

        // Define Tokenizer for transforming words into float values for Classifier
        $vectorizer = new WordCountVectorizer(
            tokenizer: new Word()
        );

        $transformer = new MultibyteTextNormalizer(false);


        // Initialize Classifier
        $classifier = new KNearestNeighbors(3, false, new Manhattan());


        foreach($filenames as $filename) {
            $output->writeln('Adding dataset from file: ' . $filename);

            [$samples, $labels] = CsvReaderHelper::readCsvFile($filename);

            // Create Dataset
            $this->dataset = new Labeled($samples, $labels);

            // Transform Questions with the vectorizer
            $this->dataset->apply($vectorizer);
            $this->dataset->apply($transformer);


            // Train the model
            $classifier->train($this->dataset);
        }

        $trainingData = new stdClass();
        $trainingData->dataset = $this->dataset;
        $trainingData->classifier = $classifier;
        $trainingData->vectorizer = $vectorizer;
        return $trainingData;
    }

    public function rebuildDataset(OutputInterface $output): bool
    {
        $classifier = $this->buildDataset($output);
        // Save the new model
        $status = file_put_contents($this->modelPath, serialize($classifier));

        if($status) {
            $output->writeln('Chatbot was successfully trained!');
            return true;
        } else {
            $output->writeln('Chatbot was not trained!');
            return false;
        }
    }

    public function addToDataset(OutputInterface $output, $filename): bool
    {
        $classifier = $this->buildDataset($output, $filename);

        $trainedDataset = unserialize(file_get_contents($this->modelPath));

        $trainedDataset->merge($this->dataset);

        // Train the model
        $classifier->train($trainedDataset);

        // Save the new model
        $status = file_put_contents($this->modelPath, serialize($classifier));

        if($status) {
            $output->writeln('Chatbot was successfully trained!');
            return true;
        } else {
            $output->writeln('Chatbot was not trained!');
            return false;
        }
    }

    private function getFilesFromDirectory($filename): array
    {
        $filenames = [];
        $finder = new Finder();

        $finder->files()
            ->in($this->datasetFilePath)
            ->name('*.csv');

        foreach ($finder as $file) {
            if($filename === false) {
                $filenames[] = $this->datasetFilePath . '/' . $file->getFilename();
            } elseif(stripos($file->getFilename(), $filename) !== false) {
                $filenames[] = $this->datasetFilePath . '/' . $file->getFilename();
                break;
            }
        }

        return $filenames;
    }

    public function predict($question): array
    {
        $trainedDataset = unserialize(file_get_contents($this->modelPath));

        // Generate dataset from question and transform from string to numersical
        $dataset = new Unlabeled([$question]); // temporäres Label wird benötigt


        // Initialize Transformer
        $transformer = new MultibyteTextNormalizer(false);

        $dataset->apply($trainedDataset->vectorizer);
        $dataset->apply($transformer);;

        // Generate prediction for question
        $predictions = $trainedDataset->classifier->predict($dataset);

        $metric = new Accuracy();

        $score = $metric->score($predictions, [$question]);
        return ['predictions' => $predictions, 'score' => $score];
    }

    public function validate(): array
    {
        /**
         * Missing tons of training data and testing data but is already working some
         */

        // Get Trainng Data
        $trainedDataset = unserialize(file_get_contents($this->modelPath));

        $questions = ['Wie erkenne ich, dass meine Katze angst vor Fremden hat?', 'Angst vor Fremden?', 'Wieviel schlafen Katzen am Tag?', 'Wie lange schlafen Katzen am Tag?', 'Was essen Katzen am Tag?', 'Wieviel essen Katzen am Tag?'];
        $labels = ['cat_fear_strangers', 'cat_fear_strangers', 'cat_amount_sleeping', 'cat_amount_sleeping', 'cat_food_amount', 'cat_food_amount'];


        $dataset = new Labeled($questions, $labels);

        // Create Estimator, Vectorizer and Transformer
        $estimator = $trainedDataset->classifier;
        $vectorizer = $trainedDataset->vectorizer;
        $transformer = new MultibyteTextNormalizer(false);

        // Transform Data
        $dataset->apply($vectorizer);
        $dataset->apply($transformer);

        // Get prediction
        $predictions = $estimator->predict($dataset);

        // Create Metrics
        $f1Metric = new FBeta(1.0);
        $accuracyMetric = new Accuracy();

        // Calculate Metrics
        return [
            'accuracy' => round($accuracyMetric->score($predictions, $labels) * 100, 2),
            'f1' => round($f1Metric->score($predictions, $labels) * 100, 2),
        ];
    }
}
