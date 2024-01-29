<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;

class CategoriesFixtures extends Fixture
{
    public function __construct(private SluggerInterface $slugger) {} // pas obliger de mettre le $this->slugger = $slugger car c'est intuitif

    public function load(ObjectManager $manager): void
    {
        $parent = new Category();
        $parent->setName('Informatique');
        $parent->setSlug($this->slugger->slug($parent->getName())->lower());
        $manager->persist($parent);

        $category = new Category();
        $category->setName('Ordinateurs portable');
        $category->setSlug($this->slugger->slug($category->getName())->lower());
        $category->setParent($parent);
        $manager->persist($category);

        $manager->flush();
    }
}
