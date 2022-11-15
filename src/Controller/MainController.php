<?php

namespace App\Controller;

use App\Form\AccountUpdateFormType;
use App\Repository\FriendRepository;
use App\Repository\InvitationRepository;
use App\Repository\NotificationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\String\Slugger\SluggerInterface;

class MainController extends AbstractController
{
    private $invitationrepository;
    private $userrespository;
    private $friendrespository;
    private $notificationrespository;
    private $em;
    private $user;

    public function getquery($dql, $param = null, $param_value = null, $str = false)
    {
        if($param){
            $query = $this->em
                      ->createQuery($dql)
                      ->setParameter($param, $param_value);
        }else{
            $query = $this->em
                      ->createQuery($dql);
        }

        if($str == true){
            $result = implode("", $query->getResult()[0]);
            if($result == null){
                return 0;
            }else{
                return $result;
            }
        }else{
            return $query->getResult();
        }
    }

    public function __construct(InvitationRepository $invitationrepository, UserRepository $userrespository, FriendRepository $friendrespository, EntityManagerInterface $em, NotificationRepository $notificationrespository)
    {
        $this->invitationrepository = $invitationrepository;
        $this->userrespository = $userrespository;
        $this->friendrespository = $friendrespository;
        $this->notificationrespository = $notificationrespository;
        $this->em = $em;
    }

    #[Route('/chat', name: 'app_chat')]
    public function index(Request $request): Response
    {
        
        $current_chat = $request->get('uuid');
        if($current_chat == null){
            $current_chat = '';
        }
        $user = $this->getUser();

        if($current_chat != $user->getLastChat()){
            $user->setLastChat($current_chat);
            $this->em->flush();
        }

        return $this->render('chat/index.html.twig');

    }

    #[Route('/friends', name: 'app_friends')]
    public function friends(): Response
    {
        return $this->render('chat/friends.html.twig');
    }

    #[Route('/inv', name: 'app_inv')]
    public function inv(): Response
    {
        return $this->render('chat/invitations.html.twig');
    }

    #[Route('/notifications', name: 'app_notifications')]
    public function notifications(): Response
    {
        $notifications = $this->notificationrespository->findBy(['user' => $this->getUser()], ['id' => 'DESC']);

        if($notifications){
            foreach($notifications as $notif){
                if($notif->isOpened() == 0){
                    $notif->setOpened(1);
                    $this->em->flush();
                }
            }
        }

        return $this->render('chat/notifications.html.twig');
    }

    #[Route('/account', name: 'app_account')]
    public function account(Request $request, SluggerInterface $slugger, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(AccountUpdateFormType::class, $user);

        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()){
            $user->setfirstname($form->get('firstname')->getData());
            $user->setlastname($form->get('lastname')->getData());
            $user->setLastChat($form->get('last_chat')->getData());

            $password = $form->get('plainPassword')->getData();

            if($password != '-1'){
                if (!$userPasswordHasher->isPasswordValid($user, $password)) {
                    $user->setPassword(
                        $userPasswordHasher->hashPassword(
                            $user,
                            $password
                        )
                    );
                }
            }

            $file = $form->get('profile_image')->getData();

            if($file && $file != '-1'){
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('pictures_directory'),
                        $newFilename
                    );

                    
                } catch (FileException $e) {
                        
                }
                $user->setImg($newFilename);
            }

            $this->em->flush();

            return $this->redirect($request->getUri());
        }
        
        return $this->render('chat/account.html.twig', [
            'accountUpdate' => $form->createView(),
        ]);
    }

    public function getAllFriendsWithLastMessage()
    {
        $dql = "SELECT f user, IDENTITY(m.outgoing_msg) u_id, m.msg, m.status FROM App\Entity\Friend f LEFT JOIN App\Entity\Message m WITH ((m.incoming_msg = f.sec_user AND m.outgoing_msg = :user_id) OR (m.outgoing_msg = f.sec_user AND m.incoming_msg = :user_id)) WHERE NOT f.sec_user = :user_id GROUP BY f.sec_user ORDER BY m.id DESC";
        $query = $this->em
                      ->createQuery($dql)
                      ->setParameter('user_id', $this->getUser()->getId());

        $friends = $query->getResult();
        
        return $friends;
        
    }

}
