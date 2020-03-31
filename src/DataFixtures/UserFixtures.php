<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

//php bin/console make:fixtures //créer des fixtures
//php bin/console doctrine:fixtures:load --append //charger les firxtures dans la base de donnée sans supprimer ce qu'il y a déja dans la bdd 
class UserFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    

    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);

        $user = new User();


        $user->setPassword($this->passwordEncoder->encodePassword(
            $user,
            'mon_mot_de_passe'
        ))
            ->setEmail('user@gmail.com')
            ->setRoles(['ROLE_USER'])
        ;

        $manager->persist($user);
        $manager->flush();
    }
}
