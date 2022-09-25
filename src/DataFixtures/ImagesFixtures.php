<?php

namespace App\DataFixtures;

use App\Entity\Image;
use Faker;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ImagesFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');
        for ($i = 1; $i < 40; $i++) {
            $image = new Image();
            $image->setName($faker->imageUrl(640, 480, true));
            // $image->setCaption($faker->sentence);
            $article = $this->getReference('article-'.rand(1, 30));
            $image->setArticle($article);

            $manager->persist($image);
        }
      

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            ArticlesFixtures::class,
        ];
    }
    
}
