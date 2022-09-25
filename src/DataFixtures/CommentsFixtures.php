<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use Faker;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CommentsFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');
        for ($i = 1; $i < 100; $i++) {
            $comment = new Comment();
            $comment->setContent($faker->sentence);
            // $image->setCaption($faker->sentence);
            $article = $this->getReference('article-'.rand(1, 30));
            $user = $this->getReference('user-'.rand(2, 10));

            $comment->setArticle($article);
            $comment->setUser($user);

            $manager->persist($comment);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            ArticlesFixtures::class,
            UsersFixtures::class
        ];
    }
}
