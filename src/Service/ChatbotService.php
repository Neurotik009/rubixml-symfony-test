<?php

namespace App\Service;

use App\Helper\CsvReaderHelper;
use Rubix\ML\Classifiers\KNearestNeighbors;
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
class ChatbotService
{
    // Where the model file is saved
    /**
     * @var string
     */
    private string $modelPath = __DIR__ . '/../../var/model/chatbot_model.rbx';

    // Where the training data is found
    /**
     * @var string
     */
    private string $datasetFilePath = __DIR__ . '/../../var/train_data/';

    // The initializing the dataset of type Labeled
    /**
     * @var Labeled
     */
    private Labeled $dataset;

    private float $accuracy = 0.0;

    private float $f1 = 0.0;


    /**
     * Gets the accuracy of the last validation
     *
     * @return float
     */
    public function getAccuracy(): float
    {
        return round($this->accuracy * 100, 2);
    }

    /**
     * Gets the F1 accuracy of the previous validation
     * @return float
     */
    public function getF1(): float
    {
        return round($this->f1 * 100, 2);
    }

    /**
     *
     */
    public function __construct()
    {
        $this->dataset = new Labeled(array(), array());
    }

    /**
     * Builds a dataset for the Chatbot from training files from the train_data folder
     *
     * @param OutputInterface $output
     * @param $filename
     * @return stdClass
     */
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

    /**
     * rebuilds and adds the train data of files
     *
     * @param OutputInterface $output
     * @return bool
     */
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

    /**
     * adds data from a single train data file
     *
     * @param OutputInterface $output
     * @param $filename
     * @return bool
     */
    public function addToDataset(OutputInterface $output, $filename): bool
    {
        $classifier = $this->buildDataset($output, $filename);

        $trainedDataset = unserialize(file_get_contents($this->modelPath));

        $trainedDataset->merge($this->dataset);

        // Train the model
        // Error Message: unknown class for IDE but works
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

    /**
     * reads all files in train_data directory and
     * puts them in an array to be read later
     *
     * @param $filename
     * @return array
     */
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

    /**
     * predicts from a single question
     *
     * @param $question
     * @return array
     */
    public function predict($question): array
    {
        $trainedDataset = unserialize(file_get_contents($this->modelPath));

        // Generate dataset from question and transform from string to numerical matrix
        $dataset = new Unlabeled([$question]); // Using Unlabed Question

        // Initialize Transformer
        $transformer = new MultibyteTextNormalizer(false);

        $dataset->apply($transformer);;
        $dataset->apply($trainedDataset->vectorizer);

        // Generate prediction for question
        $predictions = $trainedDataset->classifier->predict($dataset);

        // calculate Accuracy of Prediction
        $metric = new Accuracy();
        $this->accuracy = $metric->score($predictions, [$question]);

        // Calculate FBeta of Prediction
        $f1Metric = new FBeta(1.0);
        $this->f1 = $f1Metric->score($predictions, [$question]);

        return [
            'predictions' => $predictions,
            'accuracy' => round($this->accuracy * 100, 2),
            'f1' => round($this->f1 * 100, 2),
        ];
    }

    /**
     * validates a set of questions for measuring
     * the training data's quality
     *
     * @return array
     */
    public function validate(): array
    {
        // Get Trainng Data
        $trainedDataset = unserialize(file_get_contents($this->modelPath));

        $questions = ['Wie oft muss ich meine Katze füttern??', 'Wieviel und wie oft sollte ich meine Katze füttern?', 'Wie häuftig sollte ich meine Katze füttern??', 'Kann ich meiner Katze Milch geben?', 'Dürfen Katzen Milch trinken?', 'Wie sieht es mit Katzen und Milch aus?'];
        $labels = ['cat_feed_amount', 'cat_feed_amount', 'cat_feed_amount', 'cat_milk_allowed', 'cat_milk_allowed', 'cat_milk_allowed'];

        // Questions are a separate array
        // Error suggestion is wrong
        $dataset = new Labeled($questions, $labels);

        // Create Estimator, Vectorizer and Transformer
        $estimator = $trainedDataset->classifier;
        $vectorizer = $trainedDataset->vectorizer;

        // Transformer tests
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
