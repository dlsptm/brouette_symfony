<?php

namespace App\DataFixtures;

use App\Entity\Products;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;
use Faker;

class ProductFixtures extends Fixture
{
    public function __construct(private SluggerInterface $slugger) {}

    public function load(ObjectManager $manager): void
    {

        $faker= Faker\Factory::create('fr_FR');

        for($prod=1; $prod<=10; $prod++)
        {
        $product = new Products();
        $product->setName($faker->text(20));
        $product->setDescription($faker->text());
        $product->setSlug($this->slugger->slug($product->getName())->lower());
        $product->setPrice($faker->numberBetween(900, 150000));
        $product->setStock($faker->numberBetween(0, 10));
        $this->addReference('prod'.$prod , $product );
        
        //on va chercher une référence de catégorie
        $category = $this->getReference('cat-'.rand(1,8));
        $product->setCategory($category); 
        
        $this->setReference('prod-'.$prod, $product);

        $manager->persist($product);
        }

        $manager->flush();
    }
}
