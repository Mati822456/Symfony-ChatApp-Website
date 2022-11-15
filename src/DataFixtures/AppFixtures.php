<?php

namespace App\DataFixtures;

use App\Entity\Friend;
use App\Entity\Invitation;
use App\Entity\Notification;
use App\Entity\User;
use DateTime;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    public function __construct(private UserPasswordHasherInterface $hasher)
    {
    }

    function generateRandomString($length) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function load(ObjectManager $manager): void
    {
        
        $admin = new User();
        $admin->setfirstname('Administrator')
            ->setlastname('Admin')
            ->setEmail('admin@db.com')
            ->setPassword($this->hasher->hashPassword($admin, 'Admin1234'))
            ->setRoles(['ROLE_ADMIN'])
            ->setImg('profile.png')
            ->setUuid($this->generateRandomString(10))
            ->setStatus(0)
            ->setFriends(1)
            ->setDarkTheme(0);

        $mod = new User();
        $mod->setfirstname('Moderator')
            ->setlastname('Mod')
            ->setEmail('mod@db.com')
            ->setPassword($this->hasher->hashPassword($mod, 'Mod1234'))
            ->setRoles(['ROLE_MOD'])
            ->setImg('user.png')
            ->setUuid($this->generateRandomString(10))
            ->setStatus(0)
            ->setFriends(1)
            ->setDarkTheme(0);

        $user = new User();
        $user->setfirstname('Użytkownik')
            ->setlastname('User')
            ->setEmail('user@db.com')
            ->setPassword($this->hasher->hashPassword($user, 'User1234'))
            ->setRoles(['ROLE_USER'])
            ->setImg('user.png')
            ->setUuid($this->generateRandomString(10))
            ->setStatus(0)
            ->setFriends(2)
            ->setDarkTheme(0);

        $inv = new Invitation();
        $inv->setFrmUser($admin)
            ->setToUser($mod)
            ->setCreated(new \DateTime());

        $friend = new Friend();
        $friend->setUser($user)
               ->setSecUser($mod)
               ->setCreated(new \DateTime());

        $friend2 = new Friend();
        $friend2->setUser($mod)
                ->setSecUser($user)
                ->setCreated(new \DateTime());

        $friend3 = new Friend();
        $friend3->setUser($admin)
                ->setSecUser($user)
                ->setCreated(new \DateTime());
        
        $friend4 = new Friend();
        $friend4->setUser($user)
                ->setSecUser($admin)
                ->setCreated(new \DateTime());

        $notification = new Notification();
        $notification->setUser($mod)
                     ->setStatus(1)
                     ->setSubject('Otrzymano zaproszenie')
                     ->setText('Od użytkownika Administrator Admin')
                     ->setOpened(0)
                     ->setCreated(new \DateTime());

        $manager->persist($admin);
        $manager->persist($mod);
        $manager->persist($user);
        $manager->persist($inv);
        $manager->persist($friend);
        $manager->persist($friend2);
        $manager->persist($friend3);
        $manager->persist($friend4);
        $manager->persist($notification);
        $manager->flush();
    }
}
