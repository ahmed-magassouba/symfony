<?php

namespace App\DataFixtures;

use App\Entity\Categorie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;

class CategoriesFixtures extends Fixture
{
public $counter = 1;
    public function __construct(private SluggerInterface $slugger)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $parent = $this->createCategory('Electronique', null, $manager);
        $this->createCategory('Ordinateur', $parent, $manager);
        $this->createCategory('Imprimante', $parent, $manager);
        $this->createCategory('Tablette', $parent, $manager);
        $this->createCategory('Téléphone', $parent, $manager);
        $this->createCategory('Télévision', $parent, $manager);

        $parent = $this->createCategory('Informatique', null, $manager);
        $this->createCategory('Langage de programation', $parent, $manager);
        $this->createCategory('Système d\'exploitation', $parent, $manager);
        $manager->flush();
    }

    public function createCategory(string $name, Categorie $parent = null, ObjectManager $manager): Categorie
    {
        $category = new Categorie();
        $category->setName($name);
        $category->setSlug($this->slugger->slug($category->getName())->lower());
        $category->setParent($parent);
        $manager->persist($category);

        $this->addReference('category-'.$this->counter, $category);
        $this->counter++;
    
        return $category;
    }
}
