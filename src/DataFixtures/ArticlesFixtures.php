<?php

namespace App\DataFixtures;

use App\Entity\Article;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;
use Symfony\Component\String\Slugger\SluggerInterface;

class ArticlesFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private SluggerInterface $slugger)
    {
    }
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');
        for ($i = 1; $i < 40; $i++) {
            $article = new Article();
            $article->setTitle($faker->sentence);
            $article->setSlug($this->slugger->slug($article->getTitle())->lower());
            $article->setContent($faker->paragraph);

            $category = $this->getReference('category-'.rand(1, 8));
            $article->setCategorie($category);

            $user = $this->getReference('user-' . rand(1, 9));
            $article->setUser($user);

            $this->addReference('article-' . $i, $article);

            $manager->persist($article);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            CategoriesFixtures::class,
            UsersFixtures::class,  
        ];
    }
}
