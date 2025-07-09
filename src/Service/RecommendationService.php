<?php

namespace App\Service;

use App\Repository\InteractionRepository;
use App\Repository\ItemRepository;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\Regressors\KNNRegressor;

/**
 * Just a small exemple to test different Regressors
 */
class RecommendationService
{
    /**
     * @var InteractionRepository
     */
    private InteractionRepository $interactionRepository;
    /**
     * @var ItemRepository
     */
    private ItemRepository $itemRepository;

    /**
     * @param InteractionRepository $interactionRepository
     * @param ItemRepository $itemRepository
     */
    public function __construct(InteractionRepository $interactionRepository, ItemRepository $itemRepository)
    {
        $this->interactionRepository = $interactionRepository;
        $this->itemRepository = $itemRepository;
    }

    /**
     * Returns the current dataset
     *
     * @return Labeled
     */
    public function getDataset(): Labeled
    {
        $interactions = $this->interactionRepository->findAll();
        $samples = [];
        $labels = [];

        foreach ($interactions as $interaction) {
            $samples[] = [$interaction->getUserId(), $interaction->getItemId()];
            $labels[] = $interaction->getRating();
        }

        return new Labeled($samples, $labels);
    }

    /**
     * Trains the Model
     *
     * @return KNNRegressor
     */
    public function trainModel(): KNNRegressor
    {
        $dataset = $this->getDataset();
        $estimator = new KNNRegressor(3); // Use 5 nearest neighbors
        $estimator->train($dataset);
        return $estimator;
    }

    /**
     * Get the Recommendations from a user
     * and put in an Unlabeled dataset
     *
     * @param int $userId
     * @param int $numRecommendations
     * @return array
     */
    public function getRecommendations(int $userId, int $numRecommendations = 500): array
    {
        $model = $this->trainModel();
        $allItems = $this->itemRepository->findAll();
        $userInteractions = $this->interactionRepository->findBy(['user_id' => $userId]);

        $ratedItemIds = array_map(fn($interaction) => $interaction->getItemId(), $userInteractions);
        $unratedItems = array_filter($allItems, fn($item) => !in_array($item->getId(), $ratedItemIds));

        $predictions = [];
        foreach ($unratedItems as $item) {
            $sample = new Unlabeled([[$userId, $item->getId()]]);
            $prediction = $model->predict($sample);
            $predictions[$item->getId()] = $prediction[0]; // Extract the scalar value
        }

        arsort($predictions); // Sort by predicted rating (descending)
        return array_slice($predictions, 0, $numRecommendations, true);
    }
}
