<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Faker;

class UsersFixtures extends Fixture
{


    public function __construct(private UserPasswordHasherInterface $passwordHasher, private SluggerInterface $slugger)
    {
    }



    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setFirstname('admin');
        $admin->setLastname('admin');
        $admin->setPseudo('admin');
        $admin->setEmail('admin@localhost');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin'));
        $manager->persist($admin);

        $faker = Faker\Factory::create('fr_FR');
        for ($i = 1; $i <= 10; $i++) {
            $user = new User();
            $user->setFirstname($faker->firstName);
            $user->setLastname($faker->lastName);
            $user->setPseudo($faker->userName);
            $user->setEmail($faker->email);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
            $manager->persist($user);

             $this->addReference('user-'. $i, $user);
        
        }

        $manager->flush();
    }

   
}
