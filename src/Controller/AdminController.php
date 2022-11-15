<?php

namespace App\Controller;

use App\Repository\FriendRepository;
use App\Repository\InvitationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{

    private $userrespository;
    private $invitationrepository;
    private $friendrespository;
    private $em;

    public function __construct(UserRepository $userrespository, EntityManagerInterface $em, InvitationRepository $invitationrepository, FriendRepository $friendrespository)
    {
        $this->userrespository = $userrespository;
        $this->invitationrepository = $invitationrepository;
        $this->friendrespository = $friendrespository;
        $this->em = $em;
    }

    public function getQuery($dql)
    {
        $query = $this->em
                      ->createQuery($dql);

        $result = implode("", $query->getResult()[0]);
        if($result == null){
            return 0;
        }else{
            return $result;
        }
    }

    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        $users = $this->getQuery('SELECT COUNT(u) FROM App\Entity\User u');
        $messages = $this->getQuery('SELECT COUNT(m) FROM App\Entity\Message m');
        $inv = $this->getQuery('SELECT COUNT(i) FROM App\Entity\Invitation i');
        $notif = $this->getQuery('SELECT COUNT(n) FROM App\Entity\Notification n');
        $mods = $this->getQuery("SELECT COUNT(mo) FROM App\Entity\User mo WHERE mo.roles LIKE '%ROLE_MOD%'");
        $blocked = $this->getQuery("SELECT COUNT(mo) FROM App\Entity\User mo WHERE mo.roles LIKE '%ROLE_BLOCKED%'");
        $array = ['users' => $users, 'messages' => $messages, 'inv' => $inv, 'notif' => $notif, 'mods' => $mods, 'blocked' => $blocked];
        
        return $this->render('admin/index.html.twig', [
            'info' => $array
        ]);
    }

    #[Route('/admin/users', name: 'app_admin_users')]
    public function users(): Response
    {
        return $this->render('admin/users.html.twig');
    }

    #[Route('/admin/invitations', name: 'app_admin_invitations')]
    public function invitations(): Response
    {
        $users = $this->userrespository->findAll();

        return $this->render('admin/invitations.html.twig', [
            'users' => $users
        ]);
    }

    #[Route('/admin/notifications', name: 'app_admin_notifications')]
    public function notifications(): Response
    {
        $users = $this->userrespository->findAll();

        return $this->render('admin/notifications.html.twig', [
            'users' => $users
        ]);
    }

    #[Route('/admin/messages', name: 'app_admin_messages')]
    public function messages(): Response
    {
        $users = $this->userrespository->findAll();

        return $this->render('admin/messages.html.twig', [
            'users' => $users
        ]);
    }

    #[Route('/admin/friends', name: 'app_admin_friends')]
    public function friends(): Response
    {
        $users = $this->userrespository->findAll();

        return $this->render('admin/friends.html.twig', [
            'users' => $users
        ]);
    }
}
