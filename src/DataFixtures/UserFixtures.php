<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $passwordHasherFactory = new PasswordHasherFactory([
            PasswordAuthenticatedUserInterface::class => ['algorithm' => 'auto'],
        ]);
        $passwordHasher = new UserPasswordHasher($passwordHasherFactory);

        $admin = new User();
        $moderator = new User();
        $plaintextPassword = 'password123';

        $adminPassword = $passwordHasher->hashPassword($admin, $plaintextPassword);
        $moderatorPassword = $passwordHasher->hashPassword($moderator, $plaintextPassword);

        $admin->setEmail('admin@test.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($adminPassword);
        $manager->persist($admin);

        $moderator->setEmail('moderator@test.com');
        $moderator->setPassword($moderatorPassword);
        $manager->persist($moderator);

        $manager->flush();
    }
}
