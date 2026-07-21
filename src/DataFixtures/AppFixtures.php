<?php
namespace App\DataFixtures;

use App\Entity\Admin;
use App\Entity\Student;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Création d'un admin par défaut
        $admin = new Admin();
        $admin->setUsername('admin');
        $admin->setPassword(
            $this->passwordHasher->hashPassword($admin, 'admin123')
        );
        $manager->persist($admin);

        // Création d'étudiants de test
        $students = [
            ['Dupont', 'Jean', 'L1'],
            ['Martin', 'Marie', 'L2'],
            ['Durand', 'Pierre', 'L3'],
            ['Lefebvre', 'Sophie', 'M1'],
            ['Moreau', 'Lucas', 'M2'],
            ['Simon', 'Emma', 'L1'],
            ['Laurent', 'Hugo', 'L2'],
            ['Michel', 'Léa', 'L3'],
        ];

        foreach ($students as [$lastName, $firstName, $classroom]) {
            $student = new Student();
            $student->setLastName($lastName);
            $student->setFirstName($firstName);
            $student->setClassroom($classroom);
            $manager->persist($student);
        }

        $manager->flush();
    }
}