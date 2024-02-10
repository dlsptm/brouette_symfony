<?php

namespace App\DataFixtures;

use App\Entity\Categories;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;

class CategoriesFixtures extends Fixture
{
    private $counter = 1;

    public function __construct(private SluggerInterface $slugger){}


    public function load(ObjectManager $manager): void
    {
        $parent = $this->createCategory("Informatique", $manager, null);

        $this->createCategory("Ordinateurs portables", $manager, $parent);
        $this->createCategory("Ecrans", $manager, $parent);
        $this->createCategory("Souris", $manager, $parent);

        $parent = $this->createCategory("Mode", $manager, null);
        $this->createCategory("Homme", $manager, $parent);
        $this->createCategory("Femmes", $manager, $parent);
        $this->createCategory("Enfants", $manager, $parent);

        $manager->flush();
    }

    public function createCategory(string $name, ObjectManager $manager, Categories $parent = null)
    {
        $category = new Categories();
        $category->setName($name);
        $category->setSlug($this->slugger->slug($category->getName())->lower());
        $category->setCategoryOrder(0);
        $category->setParent($parent);
        $manager->persist($category);

        $this->addReference('cat-'.$this->counter, $category);
        $this->counter++;

        return $category;
    }
}
