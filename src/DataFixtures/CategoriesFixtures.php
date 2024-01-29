<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;

class CategoriesFixtures extends Fixture
{
    private $counter = 1;

    public function __construct(private SluggerInterface $slugger) {} // pas obliger de mettre le $this->slugger = $slugger car c'est intuitif

    public function load(ObjectManager $manager): void
    {
        $parent = $this->createCategory(name: 'Informatique', manager: $manager);

        $this->createCategory('Ordinateur parent', $manager, $parent);

        $this->createCategory('Ecrans', $manager, $parent);

        $this->createCategory('Souris', $manager, $parent);


        $parent = $this->createCategory(name: 'Mode', manager: $manager);
        $this->createCategory('Homme', $manager, $parent);
        $this->createCategory('Femme', $manager, $parent);
        $this->createCategory('Enfant', $manager, $parent);
        $this->createCategory('Accessoire', $manager, $parent);


        $manager->flush();
    }

    public function createCategory (string $name, ObjectManager $manager,  Category $parent = null)
    {
        $category = new Category();
        $category->setName($name);
        $category->setSlug($this->slugger->slug($category->getName())->lower());
        $category->setParent($parent);
        $manager->persist($category);

        $this->addReference('cat-'.$this->counter, $category);
        
        $this->counter++;

        return $category;
    }
}
