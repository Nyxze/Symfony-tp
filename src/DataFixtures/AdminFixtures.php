<?php

namespace App\DataFixtures;

use App\Entity\Admin;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminFixtures extends Fixture
{


    public function load(ObjectManager $manager): void
    {
        $admin = new Admin();
        $admin->setAdminName('Rene')->setPlainPassword('123');
        $manager->persist($admin);

        $admin = new Admin();
        $admin->setAdminName('yolo')->setPlainPassword('123');
        $manager->persist($admin);

        $manager->flush();
    }
}
