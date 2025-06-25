<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Item;
use App\Entity\Interaction;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Create 5 users
        for ($i = 1; $i <= 25; $i++) {
            $user = new User();
            $user->setUsername('user' . $i);
            $user->setEmail('user' . $i . '@example.com');
            $manager->persist($user);
            // Store a reference to use in interactions
            $this->addReference('user_' . $i, $user);
        }

        // Create 10 items
        for ($i = 1; $i <= 1000; $i++) {
            $item = new Item();
            $item->setName('Item ' . $i);
            $item->setDescription('Description for item ' . $i);
            $manager->persist($item);
            // Store a reference to use in interactions
            $this->addReference('item_' . $i, $item);
        }

        // Flush to ensure users and items are saved before creating interactions
        $manager->flush();

        // Create interactions (each user rates 500 items)
        for ($u = 1; $u <= 25; $u++) {
            $user = $this->getReference('user_' . $u, User::class);

            for ($i = $u; $i < $u + 500; $i++) {
                // Wrap around item indices to stay within 1-10
                $itemIndex = ($i - 1) % 1000 + 1;
                $item = $this->getReference('item_' . $itemIndex, Item::class);

                $interaction = new Interaction();
                $interaction->setUserId($user->getId());
                $interaction->setItemId($item->getId());
                // Generate a rating between 1 and 5
                $rating = ($u + $itemIndex) % 5 + 1;
                $interaction->setRating($rating);
                $manager->persist($interaction);
            }
        }

        // Save all persisted interactions to the database
        $manager->flush();
    }
}
