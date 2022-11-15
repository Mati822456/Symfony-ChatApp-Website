<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\FriendRepository;
use App\Repository\InvitationRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\NotificationRepository;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminApiController extends AbstractController
{
    private $userrespository;
    private $invitationrepository;
    private $friendrespository;
    private $notificationRepository;
    private $em;

    public function __construct(UserRepository $userrespository, EntityManagerInterface $em, InvitationRepository $invitationrepository, FriendRepository $friendrespository, NotificationRepository $notificationRepository)
    {
        $this->userrespository = $userrespository;
        $this->invitationrepository = $invitationrepository;
        $this->friendrespository = $friendrespository;
        $this->notificationRepository = $notificationRepository;
        $this->em = $em;
    }

    // CHECK WHERE THE REQUEST CAME FROM AND CHECK IF X-AUTH-TOKEN MATCHES ApiToken User
    
    public function checkXRequest(Request $request): bool
    {
        if($request->headers->has('X-Requested-With')){
            if($request->headers->has('X-AUTH-TOKEN') && $this->getUser()->getApiToken() == $request->headers->has('X-AUTH-TOKEN')){
                return true;
            }
            return false;
        }
        return false;
    }

    /** @param User $user
     *  @param Int $status
     *  @param String $subject
     *  @param String $text
     *  @return null
     */
    public function sendNotification($user, $status, $subject, $text)
    {
        $notif = new Notification;

        $notif->setUser($user)
              ->setStatus($status)
              ->setSubject($subject)
              ->setText($text)
              ->setOpened(0)
              ->setCreated(new \DateTime());

        $this->em->persist($notif);
        $this->em->flush();
    }

    // USERS ENDPOINTS

    #[Route('/api/v1/admin/users/{id}', name: 'api_admin_users')]
    public function users(Request $request, $id = null): Response
    {

        if(!$this->checkXRequest($request)){
            return $this->render('errorpage.html.twig', [
                'status' => 403, 
                'message' => 'Forbidden'
            ]);
        }

        $method = $request->getMethod();
        
        if($method == 'GET'){
            return $this->get_users($request, $id);
        }else if($method == 'PUT'){
            return $this->edit_user($request, $id);
        }else if($method == 'DELETE'){
            return $this->delete_user($id);
        }else{
            return $this->json('Method Not Allowed', Response::HTTP_METHOD_NOT_ALLOWED);
        }
        
    }

    // GET USERS
    public function get_users(Request $request, $id): Response
    {
        $role = $request->get('role');
        $query = $request->get('query');
        $limit = $request->get('limit');

        if($limit == null){
            $limit = 20;
        }
        
        $qb = $this->em->createQueryBuilder();
        if($limit != 'all'){
            $qb->setMaxResults($limit);
        }

        if($id != null){
            $users = $this->userrespository->find($id);
        }else{
            if($role == "default"){
                $qb->select('u')
                ->from('App\Entity\User', 'u')
                ->add('where', $qb->expr()->orX(
                    $qb->expr()->like('u.firstname', $qb->expr()->literal('%'.$query.'%')),
                    $qb->expr()->like('u.lastname', $qb->expr()->literal('%'.$query.'%')),
                    $qb->expr()->like('u.email', $qb->expr()->literal('%'.$query.'%')),
                    $qb->expr()->like('u.uuid', $qb->expr()->literal('%'.$query.'%'))
                ));
            }else{
                if($role == 'user'){
                    $role = 'ROLE_USER';
                }else if($role == 'mod'){
                    $role = 'ROLE_MOD';
                }else if($role == 'admin'){
                    $role = 'ROLE_ADMIN';
                }else if($role == 'blocked'){
                    $role = 'ROLE_BLOCKED';
                }
                
                $qb->select('u')
                ->from('App\Entity\User', 'u')
                ->add('where', $qb->expr()->orX(
                    $qb->expr()->like('u.firstname', $qb->expr()->literal('%'.$query.'%')),
                    $qb->expr()->like('u.lastname', $qb->expr()->literal('%'.$query.'%')),
                    $qb->expr()->like('u.email', $qb->expr()->literal('%'.$query.'%')),
                    $qb->expr()->like('u.uuid', $qb->expr()->literal('%'.$query.'%'))
                ))
                ->andWhere($qb->expr()->like('u.roles', $qb->expr()->literal('%'.$role.'%')));
            }
            
            $users = $qb->getQuery()->execute();
        }
        
        return $this->json($users, Response::HTTP_OK);
    }

    // EDIT USER

    public function edit_user(Request $request, $id): Response
    {
        $user = $this->userrespository->find($id);

        if($request->get('firstname')){
            $fname = $request->get('firstname');
            $lname = $request->get('lastname');
            $uuid = $request->get('uuid');
            $email = $request->get('email');
            $role = $request->get('n_role');
            $friends = $request->get('friends');

            $user->setfirstname($fname);
            $user->setlastname($lname);
            $user->setUuid($uuid);
            $user->setEmail($email);
            if($role == "user"){
                $user->setRoles(['ROLE_USER']);
            }else if($role == "mod"){
                $user->setRoles(['ROLE_MOD']);
            }else if($role == "admin"){
                $user->setRoles(['ROLE_ADMIN']);
            }else{
                $user->setRoles(['ROLE_BLOCKED']);
            }
            $user->setFriends($friends);
        }else{
            if($user->getRoles() == ['ROLE_BLOCKED']){
                $user->setRoles(['ROLE_USER']);
            }else{
                $user->setRoles(['ROLE_BLOCKED']);

                $this->sendNotification($user, 4, 'Zablokowane konto', 'Brak powodu');
            }
        }
        
        $this->em->flush();

        return $this->json("OK", Response::HTTP_OK);
    }

    // DELETE USER

    public function delete_user($id): Response
    {
        $user = $this->userrespository->find($id);

        if($user){
            $this->em->remove($user);
            $this->em->flush();
        }else{
            return $this->json("Not Found", Response::HTTP_NOT_FOUND);
        }

        return $this->json("Deleted", Response::HTTP_OK);
    }

    // INVITATIONS ENDPOINTS
    
    #[Route('/api/v1/admin/invitations/{id}', name: 'api_admin_invitations')]
    public function invitations(Request $request, $id = null): Response
    {
        if(!$this->checkXRequest($request)){
            return $this->render('errorpage.html.twig', [
                'status' => 403, 
                'message' => 'Forbidden'
            ]);
        }

        $method = $request->getMethod();
        
        if($method == 'GET'){
            return $this->get_invitations($request, $id);
        }else if($method == 'PUT'){
            return $this->edit_invitations($request, $id);
        }else if($method == 'DELETE'){
            return $this->delete_invitations($id);
        }else{
            return $this->json('Method Not Allowed', Response::HTTP_METHOD_NOT_ALLOWED);
        }
        
    }

    // GET INVITATIONS

    public function get_invitations(Request $request, $id): Response
    {

        $limit = $request->get('limit');

        if($limit == null){
            $limit = 20;
        }
        
        if($limit == 'all'){
            $limit = null;
        }
        
        if($id != null){
            $inv = $this->invitationrepository->find($id);
        }else{
            $from = $request->get('from');
            $to = $request->get('to');

            if($from == 'Wszyscy' && $to == 'Wszyscy'){
                $inv = $this->invitationrepository->findBy(array(), array(), $limit);
            }else{
                if($from == 'Wszyscy'){
                    $inv = $this->invitationrepository->findBy(['to_user' => $to], array(), $limit);
                }
                if($to == 'Wszyscy'){
                    $inv = $this->invitationrepository->findBy(['frm_user' => $from], array(), $limit);
                }
                if($from != 'Wszyscy' && $to != 'Wszyscy'){
                    $inv = $this->invitationrepository->findBy(['frm_user' => $from, 'to_user' => $to], array(), $limit);
                }
                
            }
        }

        return $this->json($inv, Response::HTTP_OK);
    }

    // EDIT INVITATIONS

    public function edit_invitations(Request $request, $id): Response
    {
        $from = $request->get('from_user');
        $to = $request->get('to_user');
        $send = $request->get('send');
        
        $user_from = $this->userrespository->find($from);
        $user_to = $this->userrespository->find($to);

        $inv = $this->invitationrepository->find($id);
        $inv->setFrmUser($user_from);
        $inv->setToUser($user_to);
        $inv->setCreated(new \DateTime($send));
        $this->em->flush();

        return $this->json("OK", Response::HTTP_OK);
    }

    // DELETE INVITATIONS 
    
    public function delete_invitations($id): Response
    {
        $inv = $this->invitationrepository->find($id);

        if($inv){
            $this->em->remove($inv);
            $this->em->flush();
        }else{
            return $this->json("Not Found", Response::HTTP_NOT_FOUND);
        }

        return $this->json("OK", Response::HTTP_OK);
    }

    // FRIENDS ENDPOINTS

    #[Route('/api/v1/admin/friends/{id}', name: 'api_admin_friends')]
    public function friends(Request $request, $id = null): Response
    {
        
        if(!$this->checkXRequest($request)){
            return $this->render('errorpage.html.twig', [
                'status' => 403, 
                'message' => 'Forbidden'
            ]);
        }

        $method = $request->getMethod();
        
        if($method == 'GET'){
            return $this->get_friends($request, $id);
        }else if($method == 'PUT'){
            return $this->edit_friends($request, $id);
        }else if($method == 'DELETE'){
            return $this->delete_friends($id);
        }else{
            return $this->json('Method Not Allowed', Response::HTTP_METHOD_NOT_ALLOWED);
        }
        
    }

    // GET FRIENDS

    public function get_friends($request, $id): Response
    {

        if($id != null){
            $friends = $this->friendrespository->find($id);
        }else{
            if($request->get('limit')){
                $limit = $request->get('limit');
            }else{
                $limit = 20;
            }
                
            $user = $request->get('user');
            $friend = $request->get('friend');

            if($user == 'Wszyscy' && $friend == 'Wszyscy'){
                $friends = $this->friendrespository->findBy(array(), array(), $limit);
            }else{
                if($user == 'Wszyscy'){
                    $friends = $this->friendrespository->findBy(['sec_user' => $friend], array(), $limit);
                }
                if($friend == 'Wszyscy'){
                    $friends = $this->friendrespository->findBy(['user' => $user], array(), $limit);
                }
                if($user != 'Wszyscy' && $friend != 'Wszyscy'){
                    $friends = $this->friendrespository->findBy(['user' => $user, 'sec_user' => $friend], array(), $limit);
                }
            }
        }
        
        return $this->json($friends, Response:: HTTP_OK);
    }

    // EDIT FRIENDS

    public function edit_friends($request, $id): Response
    {
        $user_id = $request->get('user');
        $friend_id = $request->get('friend');
        $from = $request->get('from');
        
        $user = $this->userrespository->find($user_id);
        $friend = $this->userrespository->find($friend_id);

        $en_friend = $this->friendrespository->find($id);
        $en_friend->setUser($user);
        $en_friend->setSecUser($friend);
        $en_friend->setCreated(new \DateTime($from));

        $this->em->flush();

        return $this->json("OK", Response::HTTP_OK);
    }

    // DELETE FRIENDS

    public function delete_friends($id): Response
    {
        $friend = $this->friendrespository->find($id);
        $friend2 = $this->friendrespository->findBy(['sec_user' => $friend->getUser(), 'user' => $friend->getSecUser()])[0];
        
        $this->em->remove($friend);
        $this->em->remove($friend2);

        $this->em->flush();

        return $this->json("Deleted", Response::HTTP_OK);
    }

    // NOTIFICATIONS ENDPOINTS

    #[Route('/api/v1/admin/notifications/{id}', name: 'api_admin_notifications')]
    public function notifications(Request $request, $id = null): Response
    {

        if(!$this->checkXRequest($request)){
            return $this->render('errorpage.html.twig', [
                'status' => 403, 
                'message' => 'Forbidden'
            ]);
        }

        $method = $request->getMethod();
        
        if($method == 'GET'){
            return $this->get_notifications($request, $id);
        }else if($method == 'PUT'){
            return $this->edit_notifications($request, $id);
        }else if($method == 'DELETE'){
            return $this->delete_notifications($id);
        }else{
            return $this->json('Method Not Allowed', Response::HTTP_METHOD_NOT_ALLOWED);
        }
        
    }

    // GET NOTIFICATIONS

    public function get_notifications(Request $request, $id): Response
    {
        if($id != null){
            $notif = $this->notificationRepository->find($id);
        }else{
            $user = $request->get('user');
        
            if($user == 'Wszyscy'){
                $notif = $this->notificationRepository->findAll();
            }else{
                $notif = $this->notificationRepository->findBy(['user' => $user]);
            }
        }

        return $this->json($notif, Response::HTTP_OK);
    }

    // EDIT NOTIFICATIONS

    public function edit_notifications(Request $request, $id): Response
    {

        $to = $request->get('to_user');
        $status = $request->get('status');
        $subject = $request->get('subject');
        $text = $request->get('text');
        $send = $request->get('send');
        $opened = $request->get('opened');
        if($opened == 'true'){
            $opened = true;
        }else{
            $opened = false;
        }

        $notif = $this->notificationRepository->find($id);
        $user = $this->userrespository->find($to);

        $notif->setUser($user);
        $notif->setStatus($status);
        $notif->setSubject($subject);
        $notif->setText($text);
        $notif->setCreated(new \DateTime($send));
        $notif->setOpened($opened);
        $this->em->flush();

        return $this->json("OK", Response::HTTP_OK);
    }

    // DELETE NOTIFICATIONS

    public function delete_notifications($id): Response
    {
        $notif = $this->notificationRepository->find($id);

        if($notif){
            $this->em->remove($notif);
            $this->em->flush();
        }else{
            return $this->json(['message' => 'Not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(['message' => 'Deleted'], Response::HTTP_OK);
    }

    // MESSAGES ENDPOINTS

    #[Route('/api/v1/admin/messages', name: 'api_admin_messages')]
    public function messages(Request $request): Response
    {

        if(!$this->checkXRequest($request)){
            return $this->render('errorpage.html.twig', [
                'status' => 403, 
                'message' => 'Forbidden'
            ]);
        }

        $method = $request->getMethod();
        
        if($method == 'GET'){
            return $this->get_messages($request);
        }else if($method == 'DELETE'){
            return $this->clear_messages($request);
        }else{
            return $this->json('Method Not Allowed', Response::HTTP_METHOD_NOT_ALLOWED);
        }
        
    }

    // GET MESSAGES

    public function get_messages(Request $request): Response
    {
        if($request->get('limit')){
            $limit = $request->get('limit');
        }else{
            $limit = 20;
        }
        
        $from = $request->get('from');
        $to = $request->get('to');

        if($from == 'Wszyscy' && $to == 'Wszyscy'){
            $dql = "SELECT m, COUNT(m.id) AS Count FROM App\Entity\Message m GROUP BY m.incoming_msg, m.outgoing_msg ORDER BY m.incoming_msg";
            $query = $this->em
                            ->createQuery($dql);
        }else{
            if($from == 'Wszyscy'){
                $dql = "SELECT m, COUNT(m.id) AS Count FROM App\Entity\Message m WHERE m.incoming_msg = :user GROUP BY m.incoming_msg, m.outgoing_msg ORDER BY m.incoming_msg";
                $query = $this->em
                            ->createQuery($dql)
                            ->setParameter('user', $to);
            }
            if($to == 'Wszyscy'){
                $dql = "SELECT m, COUNT(m.id) AS Count FROM App\Entity\Message m WHERE m.outgoing_msg = :user GROUP BY m.incoming_msg, m.outgoing_msg ORDER BY m.incoming_msg";
                $query = $this->em
                            ->createQuery($dql)
                            ->setParameter('user', $from);
            }
            if($from != 'Wszyscy' && $to != 'Wszyscy'){
                $dql = "SELECT m, COUNT(m.id) AS Count FROM App\Entity\Message m WHERE (m.incoming_msg = :user AND m.outgoing_msg = :to) OR (m.incoming_msg = :to AND m.outgoing_msg = :user) GROUP BY m.incoming_msg, m.outgoing_msg ORDER BY m.incoming_msg";
                $query = $this->em
                            ->createQuery($dql)
                            ->setParameter('user', $to)
                            ->setParameter('to', $from);       
            }
        }
        
        if($limit != 'all'){
            $query->setMaxResults($limit);
        }
        $messages = $query->getResult();

        return $this->json($messages, Response::HTTP_OK);
    }

    // CLEAR MESSAGES

    public function clear_messages(Request $request): Response
    {

        $from = $request->get('from');
        $to = $request->get('to');

        $dql = "SELECT m FROM App\Entity\Message m WHERE m.outgoing_msg = :user AND m.incoming_msg = :to";

        $query = $this->em
                      ->createQuery($dql)
                      ->setParameter('user', $from)
                      ->setParameter('to', $to);
        
        $messages = $query->getResult();

        foreach($messages as $mess){
            $this->em->remove($mess);
        }
    
        $this->em->flush();

        return $this->json("Cleared", Response::HTTP_OK);
    }
}
