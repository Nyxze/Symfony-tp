<?php

namespace App\DataFixtures;

use App\Entity\Author;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthorFixtures extends Fixture implements OrderedFixtureInterface
{

    public static int $numberOfRecords = 15;

    private Generator $faker;
    private UserPasswordHasherInterface $pswEncoder;
    public static array $nationalities = [
        "Française", "Anglaise", "Allemande", "Espagnole", "Italienne",
        "Irlandaise"
    ];

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->pswEncoder = $userPasswordHasher;
        $this->faker = Factory::create("fr_FR");
    }

    public function load(ObjectManager $manager): void
    {
        for($i=1; $i <= self::$numberOfRecords; $i++) {
            $author = new Author();
            $author->setFirstName($this->faker->firstName())
                ->setLastName($this->faker->lastName())
                ->setEmail($this->faker->email())
                ->setNationality(
                    $this->faker->randomElement(self::$nationalities)
                );
            $author->setPlainPassword('123');
            $author->setHashPswd($this->pswEncoder->hashPassword($author,'123'));
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
