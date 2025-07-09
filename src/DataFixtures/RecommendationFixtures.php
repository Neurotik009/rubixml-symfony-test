<?php

namespace App\DataFixtures;

use App\Entity\Role;
use App\Entity\User;
use App\Entity\Item;
use App\Entity\Interaction;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RecommendationFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function getDependencies() : array
    {
        return [
            RoleFixtures::class, // Ihre Role Fixtures-Klasse
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $userRole = $manager->getRepository(Role::class)->findOneBy(['name' => 'ROLE_USER']);

        // Create 25 users
        for ($i = 1; $i <= 25; $i++) {
            $user = new User();
            $user->setEmail('user' . $i . '@example.com');
            $user->addRole($userRole);

            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                'test'
            );

            $user->setPassword($hashedPassword);

            $manager->persist($user);
            // Store a reference to use in interactions
            $this->addReference('user_' . $i, $user);
        }

        // Create 1000 items
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

        // Create interactions for users
        for ($u = 1; $u <= 25; $u++) {
            $user = $this->getReference('user_' . $u, User::class);

            for ($i = $u; $i < $u + 1000; $i++) {
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
