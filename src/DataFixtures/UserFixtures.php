<?php declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private const DEFAULT_EMAIL = 'admin@toborek.info';
    private const DEFAULT_PASSWORD = 'admin';
    private const DEFAULT_ROLE = 'ROLE_ADMIN';

    public function __construct(private readonly UserPasswordEncoderInterface $passwordEncoder)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();

        $user->setEmail(self::DEFAULT_EMAIL);
        $user->setPassword($this->passwordEncoder->encodePassword(
            $user,
            self::DEFAULT_PASSWORD
        ));
        $user->setRoles([
            self::DEFAULT_ROLE,
        ]);

        $manager->persist($user);
        $manager->flush();
    }
}
