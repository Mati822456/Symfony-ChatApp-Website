<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Friend;
use App\Entity\Invitation;
use App\Entity\Notification;
use App\Repository\UserRepository;
use App\Repository\FriendRepository;
use App\Repository\MessageRepository;
use App\Repository\InvitationRepository;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

class UsersApiController extends AbstractController
{
    private $userrespository;
    private $invitationrepository;
    private $friendrespository;
    private $messagerespository;
    private $notificationrespository;
    private $em;

    public function __construct(UserRepository $userrespository, EntityManagerInterface $em, InvitationRepository $invitationrepository, FriendRepository $friendrespository, MessageRepository $messagerespository, NotificationRepository $notificationrespository)
    {
        $this->userrespository = $userrespository;
        $this->invitationrepository = $invitationrepository;
        $this->friendrespository = $friendrespository;
        $this->messagerespository = $messagerespository;
        $this->notificationrespository = $notificationrespository;
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

    // SHOW NON-SENSITIVE DATA
    
    public function apiResponseData($object)
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $normalizer = array(new DateTimeNormalizer(), new ObjectNormalizer($classMetadataFactory));
        $serializer = new Serializer($normalizer);
        $data = $serializer->normalize($object, null, ['groups' => 'api_public']);

        return $data;
    }

    /** @param User $user
     *  @param Int $status
     *  @param String $subject
     *  @param String $text
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
    
    // USERS ENDPOINT

    #[Route('/api/v1/users/{id}', name: 'api_users')]
    public function users(Request $request, $id = null): Response
    {

        if(!$this->checkXRequest($request)){
            return $this->render('errorpage.html.twig', [
                'status' => 403, 
                'message' => 'Forbidden'
            ]);
        }

        if($id == null){
            return $this->json('Bad request', Response::HTTP_BAD_REQUEST);
        }

        $method = $request->getMethod();

        if($method == 'GET'){
            return $this->get_user($id);
        }else{
            return $this->json('Method Not Allowed', Response::HTTP_METHOD_NOT_ALLOWED);
        }

    }

    // GET USER

    public function get_user($uuid): Response
    {

        $dql = "SELECT u FROM App\Entity\User u WHERE u.uuid LIKE BINARY('".$uuid."')";

        $query = $this->em
                      ->createQuery($dql);

        $user = $query->getResult()[0];

        $friend = $this->friendrespository->findBy(['user' => $this->getUser(), 'sec_user'=> $user]);
        $isFriend = 0;

        if(!empty($friend)){
            $isFriend = 1;
        }

        $data = $this->apiResponseData($user);

        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $serializer = new Serializer($normalizers, $encoders);

        $jsonContent = $serializer->serialize($data, 'json');
        
        $merged = 
            array_merge(
                json_decode($jsonContent, true),
                json_decode('{"isFriend": '.$isFriend.'}', true)
            );

        return $this->json($merged, Response::HTTP_OK);

    }

    // INVITATIONS ENDPOINTS

    #[Route('/api/v1/invitations/{id}', name: 'api_invitations')]
    public function invitations(Request $request, $id = null): Response
    {

        if(!$this->checkXRequest($request)){
            return $this->render('errorpage.html.twig', [
                'status' => 403, 
                'message' => 'Forbidden'
            ]);
        }

        $method = $request->getMethod();

        if($id == null && $method != 'GET'){
            return $this->json('Bad request', Response::HTTP_BAD_REQUEST);
        }

        if($method == 'GET'){
            if($id){
                return $this->get_invitation($id);
            }
            return $this->get_invitations();
        }else if($method == 'POST'){
            return $this->send_invitation($id);
        }else if($method == 'DELETE'){
            return $this->cancel_invitation($id);
        }else{
            return $this->json('Method Not Allowed', Response::HTTP_METHOD_NOT_ALLOWED);
        }
    }

    // GET INVITATION

    public function get_invitations(): Response
    {
        
        $inv = $this->invitationrepository->findBy(['to_user' => $this->getUser()]);

        $data = $this->apiResponseData($inv);

        return $this->json($data, Response::HTTP_OK);

    }

    // GET INVITATIONS

    public function get_invitation($uuid): Response
    {
        
        $user = $this->userrespository->findBy(['uuid' => $uuid])[0];

        $qb = $this->em->createQueryBuilder();

        $qb->select('i')
           ->from('App\Entity\Invitation', 'i')
           ->where('i.frm_user = '.$user->getId())
           ->andWhere('i.to_user = '.$this->getUser()->getId());

        $qb = $qb->getQuery();

        $inv = $qb->execute()[0];
        
        $data = $this->apiResponseData($inv);
        
        return $this->json($data, Response::HTTP_OK);

    }

    // SEND INVITATION

    public function send_invitation($uuid): Response
    {
        
        $inv = new Invitation;
        $user = $this->userrespository->findBy(['uuid' => $uuid])[0];

        $inv->setFrmUser($this->getUser())
            ->setToUser($user)
            ->setCreated(new \DateTime());

        $this->sendNotification($user, 1, 'Otrzymano zaproszenie', 'Od użytkownika '.$this->getUser()->getFirstName().' '.$this->getUser()->getLastName());

        $this->em->persist($inv);
        $this->em->flush();

        return $this->json('Created', Response::HTTP_CREATED);

    }

    // CANCEL INVITATION

    public function cancel_invitation($uuid): Response
    {
        
        $user = $this->userrespository->findBy(['uuid' => $uuid])[0];

        $inv = $this->invitationrepository->findBy(['frm_user' => $user->getId()])[0];

        $this->em->remove($inv);
        $this->em->flush();

        return $this->json('OK', Response::HTTP_OK);

    }

    // FRIENDS ENDPOINTS

    #[Route('/api/v1/friends/{id}', name: 'api_friends')]
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
            return $this->get_friends($id);
        }else if($method == 'POST'){
            return $this->accept_friend($id);
        }else if($method == 'DELETE'){
            return $this->remove_friend($id);
        }else{
            return $this->json('Method Not Allowed', Response::HTTP_METHOD_NOT_ALLOWED);
        }
    }

    // GET FRIENDS

    public function get_friends($uuid): Response
    {

        if($uuid != null){

            $user = $this->userrespository->findBy(['uuid' => $uuid])[0];

            $qb = $this->em->createQueryBuilder();
    
            $qb->select('f')
               ->from('App\Entity\Friend', 'f')
               ->where('f.user = '.$this->getUser()->getId())
               ->andWhere('f.sec_user = '.$user->getId());
    
            $qb = $qb->getQuery();
            
            $friends = $qb->execute()[0];

        }else{

            $dql = "SELECT f user, IDENTITY(m.outgoing_msg) u_id, m.id, m.msg, m.status FROM App\Entity\Friend f LEFT JOIN App\Entity\Message m WITH ((m.incoming_msg = f.sec_user AND m.outgoing_msg = :user_id) OR (m.outgoing_msg = f.sec_user AND m.incoming_msg = :user_id)) WHERE NOT f.sec_user = :user_id AND f.user = :user_id GROUP BY f.sec_user ORDER BY m.id DESC";
            $query = $this->em
                        ->createQuery($dql)
                        ->setParameter('user_id', $this->getUser()->getId());

            $friends = $query->getResult();

            foreach($friends as $mess){
                if($mess['id'] != null){
                    if($mess['u_id'] != $this->getUser()->getId()){
                        if($mess['status'] < 2){
                            $message = $this->messagerespository->findBy(['id' => $mess['id'], 'incoming_msg' => $this->getUser()->getId()]);
                            $message[0]->setStatus(1);
                            $this->em->flush();
                        }
                    }
                    
                }
            }
        }
        
        $data = $this->apiResponseData($friends);
        
        return $this->json($data, Response::HTTP_OK);
    }

    // ACCEPT FRIEND

    public function accept_friend($uuid): Response
    {
        
        $user = $this->userrespository->findBy(['uuid' => $uuid])[0];

        $inv = $this->invitationrepository->findBy(['frm_user' => $user->getId(), 'to_user' => $this->getUser()])[0];

        $friend = new Friend;
        $friend2 = new Friend;
        $me = $this->getUser();

        $friend->setUser($this->getUser())
               ->setSecUser($user)
               ->setCreated(new \DateTime());

        $friend2->setUser($user)
                ->setSecUser($this->getUser())
                ->setCreated(new \DateTime());

        $user->setFriends($user->getFriends() + 1);
        $me->setFriends($me->getFriends() + 1);

        $this->sendNotification($user, 1, 'Jesteście znajomymi z', $this->getUser()->getFirstName().' '.$this->getUser()->getLastName());

        $this->em->remove($inv);
        $this->em->persist($friend);
        $this->em->persist($friend2);
        $this->em->flush();

        return $this->json('Created', Response::HTTP_CREATED);

    }

    // REMOVE FRIEND

    public function remove_friend($uuid): Response
    {
        
        $user = $this->userrespository->findBy(['uuid' => $uuid])[0];
        $me = $this->getUser();

        $qb = $this->em->createQueryBuilder();

        $qb->select('f')
           ->from('App\Entity\Friend', 'f')
           ->where($qb->expr()->andX($qb->expr()->eq('f.user', $this->getUser()->getId()), $qb->expr()->eq('f.sec_user', $user->getId())))
           ->orWhere($qb->expr()->andX($qb->expr()->eq('f.sec_user', $this->getUser()->getId()), $qb->expr()->eq('f.user', $user->getId())));

        $qb = $qb->getQuery();

        $user->setFriends($user->getFriends() - 1);
        $me->setFriends($me->getFriends() - 1);

        $this->em->remove($qb->execute()[0]);
        $this->em->remove($qb->execute()[1]);
        $this->em->flush();

        return $this->json('OK', Response::HTTP_OK);

    }

    // THEME ENDPOINTS

    #[Route('/api/v1/theme', name: 'api_theme_set')]
    public function theme(Request $request): Response
    {

        if(!$this->checkXRequest($request)){
            return $this->render('errorpage.html.twig', [
                'status' => 403, 
                'message' => 'Forbidden'
            ]);
        }

        $method = $request->getMethod();

        if($method == 'PATCH'){
            return $this->set_theme();
        }else{
            return $this->json('Method Not Allowed', Response::HTTP_METHOD_NOT_ALLOWED);
        }
    }

    // SET THEME

    public function set_theme(): Response
    {
        $user = $this->getUser();
        $mode = $user->isDarkTheme();
        
        if($mode == 0){
            $user->setDarkTheme(1);
        }else{
            $user->setDarkTheme(0);
        }
        $this->em->flush();

        return $this->json('OK', Response::HTTP_OK);
    }

    // NOTIFICATIONS ENDPOINTS
    #[Route('/api/v1/notifications/{id}', name: 'api_notification')]
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
            return $this->get_notifications();
        }else if($method == 'DELETE'){
            if($id == null){
                return $this->json('Bad request', Response::HTTP_BAD_REQUEST);
            }
            return $this->remove_notification($id);
        }else{
            return $this->json('Method Not Allowed', Response::HTTP_METHOD_NOT_ALLOWED);
        }

    }

    // GET NOTIFICATIONS

    public function get_notifications()
    {
        $notifications = $this->notificationrespository->findBy(['user' => $this->getUser()], ['id' => 'DESC']);

        $data = $this->apiResponseData($notifications);

        return $this->json($data, Response::HTTP_OK);
    }

    // REMOVE NOTIFICATION

    public function remove_notification($id): Response
    {

        $notification = $this->notificationrespository->findBy(['id' => $id]);

        if($notification){
            if($notification[0]->getUser() == $this->getUser()){
                $this->em->remove($notification[0]);
                $this->em->flush();
            }else{
                return $this->json('FORBIDDEN', Response::HTTP_FORBIDDEN);
            }
        }

        return $this->json('OK', Response::HTTP_OK);
    }
    
}
