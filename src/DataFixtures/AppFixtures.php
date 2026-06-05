<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        #[Autowire('%env(ADMIN_EMAIL)%')]
        private readonly string $adminEmail,
        #[Autowire('%env(ADMIN_PASSWORD)%')]
        private readonly string $adminPassword,
        #[Autowire('%env(ADMIN_NOM)%')]
        private readonly string $adminNom,
        #[Autowire('%env(ADMIN_PRENOM)%')]
        private readonly string $adminPrenom,
        #[Autowire('%env(ADMIN_TELEPHONE)%')]
        private readonly string $adminTelephone,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $existingAdmin = $manager->getRepository(User::class)->findOneBy(['email' => $this->adminEmail]);

        if ($existingAdmin instanceof User) {
            return;
        }

        $admin = (new User())
            ->setEmail($this->adminEmail)
            ->setNom($this->adminNom)
            ->setPrenom($this->adminPrenom)
            ->setTelephone($this->adminTelephone ?: null)
            ->setRoles(['ROLE_ADMIN']);

        $admin->setPassword($this->passwordHasher->hashPassword($admin, $this->adminPassword));

        $manager->persist($admin);
        $manager->flush();
    }
}
