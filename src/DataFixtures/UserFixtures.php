<?php

namespace App\DataFixtures;

use App\Entity\Role;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface

{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function getDependencies(): array
    {
        return [
            RoleFixtures::class, // Ihre Role Fixtures-Klasse
        ];
    }


    public function load(ObjectManager $manager): void
    {
        // Creating Admin User
        $admin = new User();
        $admin->setEmail('admin@admin.com');

        $adminRole = $manager->getRepository(Role::class)->findOneBy(['name' => 'ROLE_ADMIN']);
        $userRole = $manager->getRepository(Role::class)->findOneBy(['name' => 'ROLE_USER']);

        $admin->addRole($adminRole);
        $admin->addRole($userRole);

        $hashedPassword = $this->passwordHasher->hashPassword(
            $admin,
            'admin'
        );

        $admin->setPassword($hashedPassword);

        $manager->persist($admin);

        $manager->flush();
    }
}

