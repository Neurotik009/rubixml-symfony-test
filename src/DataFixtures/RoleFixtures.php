<?php

namespace App\DataFixtures;

use App\Entity\Role;
use App\Entity\Permission;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class RoleFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Create Permissions for Recommendations
        $viewRecommendations = new Permission();
        $viewRecommendations->setName('view_recommendations');;
        $manager->persist($viewRecommendations);

        $predictRecommendations = new Permission();
        $predictRecommendations->setName('predict_recommendations');
        $manager->persist($predictRecommendations);

        $trainRecommendations = new Permission();
        $trainRecommendations->setName('train_recommendations');
        $manager->persist($trainRecommendations);

        $validateRecommendations = new Permission();
        $validateRecommendations->setName('validate_recommendations');
        $manager->persist($validateRecommendations);

        // Create Permissions for Chatbot
        $viewChatbot = new Permission();
        $viewChatbot->setName('view_chatbot');
        $manager->persist($viewChatbot);

        $predictChatbot = new Permission();
        $predictChatbot->setName('predict_chatbot');
        $manager->persist($predictChatbot);

        $trainChatbot = new Permission();
        $trainChatbot->setName('train_chatbot');
        $manager->persist($trainChatbot);

        $validateChatbot = new Permission();
        $validateChatbot->setName('validate_chatbot');
        $manager->persist($validateChatbot);

        // Create Permissions for Users
        $viewUsers = new Permission();
        $viewUsers->setName('view_users');
        $manager->persist($viewUsers);

        $createUsers = new Permission();
        $createUsers->setName('create_users');
        $manager->persist($createUsers);

        $editUsers = new Permission();
        $editUsers->setName('edit_users');
        $manager->persist($editUsers);

        $deleteUsers = new Permission();
        $deleteUsers->setName('delete_users');
        $manager->persist($deleteUsers);

        // Create Permissions for Roles
        $viewRoleToUsers = new Permission();
        $viewRoleToUsers->setName('view_role');
        $manager->persist($viewRoleToUsers);

        $createRoles = new Permission();
        $createRoles->setName('create_role');
        $manager->persist($createRoles);

        $editRoles = new Permission();
        $editRoles->setName('edit_role');
        $manager->persist($editRoles);

        $deleteRoles = new Permission();
        $deleteRoles->setName('delete_role');
        $manager->persist($deleteRoles);

        // Create Role to User Permissions
        $addRoleToUsers = new Permission();
        $addRoleToUsers->setName('add_role_user');
        $manager->persist($addRoleToUsers);

        $removeRoleFromUsers = new Permission();
        $removeRoleFromUsers->setName('remove_role_user');
        $manager->persist($removeRoleFromUsers);

        // Permisson to Role
        $addPermissionToRole = new Permission();
        $addPermissionToRole->setName('add_permission_role');
        $manager->persist($addPermissionToRole);

        $removePermissionToRole = new Permission();
        $removePermissionToRole->setName('remove_permission_role');
        $manager->persist($removePermissionToRole);

        // Create all Roles

        // Create Admin Role
        $adminRole = new Role();
        $adminRole->setName('ROLE_ADMIN');
        $adminRole->getPermissions()->add($viewUsers);
        $adminRole->getPermissions()->add($createUsers);
        $adminRole->getPermissions()->add($editUsers);
        $adminRole->getPermissions()->add($deleteUsers);
        $adminRole->getPermissions()->add($viewRoleToUsers);
        $adminRole->getPermissions()->add($createRoles);
        $adminRole->getPermissions()->add($editRoles);
        $adminRole->getPermissions()->add($deleteRoles);
        $adminRole->getPermissions()->add($addRoleToUsers);
        $adminRole->getPermissions()->add($removeRoleFromUsers);
        $adminRole->getPermissions()->add($addPermissionToRole);
        $adminRole->getPermissions()->add($removePermissionToRole);

        $manager->persist($adminRole);

        // Create User Role
        $userRole = new Role();
        $userRole->setName('ROLE_USER');
        $userRole->getPermissions()->add($viewChatbot);
        $userRole->getPermissions()->add($viewRecommendations);
        $userRole->getPermissions()->add($predictRecommendations);
        $userRole->getPermissions()->add($predictChatbot);
        $manager->persist($userRole);

        // Create Chatbot Editor Role
        $chatbotEditorRole = new Role();
        $chatbotEditorRole->setName('ROLE_CHATBOT_EDITOR');
        $chatbotEditorRole->getPermissions()->add($trainChatbot);
        $chatbotEditorRole->getPermissions()->add($validateChatbot);
        $manager->persist($chatbotEditorRole);

        // Create Recommendation Editor Role
        $recommendationEditorRole = new Role();
        $recommendationEditorRole->setName('ROLE_RECOMMENDATION_EDITOR');
        $recommendationEditorRole->getPermissions()->add($trainRecommendations);
        $recommendationEditorRole->getPermissions()->add($validateRecommendations);
        $manager->persist($recommendationEditorRole);

        $manager->flush();

        // Set References for other fixtures
        $this->addReference('role-admin', $adminRole);
        $this->addReference('role-editor', $userRole);
        $this->addReference('role-recommendation', $recommendationEditorRole);
        $this->addReference('role-chatbot', $chatbotEditorRole);
    }
}
