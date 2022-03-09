<?php

namespace App\DataFixtures;

use App\Entity\Author;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Faker\Factory;

class AuthorFixtures extends Fixture implements OrderedFixtureInterface
{

    public static int $numberOfRecords = 15;

    private Generator $faker;

    private array $nationalities = [
        "Française", "Anglaise", "Allemande", "Espagnole", "Italienne",
        "Irlandaise"
    ];

    public function __construct()
    {
        $this->faker = Factory::create("fr_FR");
    }

    public function load(ObjectManager $manager): void
    {
        for($i=1; $i <= self::$numberOfRecords; $i++) {
            $author = new Author();
            $author->setFirstName($this->faker->firstName())
                ->setLastName($this->faker->lastName())
                ->setNationality(
                    $this->faker->randomElement($this->nationalities)
                );
            $manager->persist($author);
            // Ajout de l'auteur en référence pour une utilisation
            // ultérieure dans une autre classe de fixtures
            $this->addReference("author$i", $author);
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return 5;
    }
}
