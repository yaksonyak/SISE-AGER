<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private const ADMIN_EMAIL = 'admin@sise-ager.local';
    private const ADMIN_PASSWORD = 'admin123';
    private const ADMIN_NOM = 'Administrateur';
    private const ADMIN_PRENOM = 'SISE-AGER';

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $admin = $manager->getRepository(User::class)->findOneBy(['email' => self::ADMIN_EMAIL]);

        if (!$admin instanceof User) {
            $admin = (new User())
                ->setEmail(self::ADMIN_EMAIL)
                ->setNom(self::ADMIN_NOM)
                ->setPrenom(self::ADMIN_PRENOM)
                ->setTelephone(null);

            $manager->persist($admin);
        }

        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, self::ADMIN_PASSWORD));

        $manager->flush();
    }
}
