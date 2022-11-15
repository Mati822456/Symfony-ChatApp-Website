<?php

namespace App\Controller;

use App\Entity\Message;
use App\Repository\UserRepository;
use App\Repository\FriendRepository;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\NotificationRepository;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

class ChatApiController extends AbstractController
{

    private $messagerespository;
    private $userrespository;
    private $friendrespository;
    private $notificationrespository;
    private $em;

    public function __construct(MessageRepository $messagerespository, EntityManagerInterface $em, UserRepository $userrespository, FriendRepository $friendrespository, NotificationRepository $notificationrespository) 
    {
        $this->messagerespository = $messagerespository;
        $this->userrespository = $userrespository;
        $this->friendrespository = $friendrespository;
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
    
    // MESSAGES ENDPOINTS

    #[Route('/api/v1/messages/{id}', name: 'api_messages')]
    public function messages(Request $request, $id = null): Response
    {

        if(!$this->checkXRequest($request)){
            return $this->render('errorpage.html.twig', [
                'status' => 403, 
                'message' => 'Forbidden'
            ]);
        }

        $method = $request->getMethod();

        if($id == null){
            return $this->json('Bad request', Response::HTTP_BAD_REQUEST);
        }

        if($method == 'GET'){
            return $this->get_messages($id);
        }else if($method == 'POST'){
            return $this->send_message($request, $id);
        }else if($method == 'DELETE'){
            return $this->delete_message($id);
        }else{
            return $this->json('Method Not Allowed', Response::HTTP_METHOD_NOT_ALLOWED);
        }

    }

    // GET MESSAGES

    public function get_messages($uuid): Response
    {

        $user = $this->userrespository->findBy(['uuid' => $uuid])[0];
        
        $qb = $this->em->createQueryBuilder();

        $qb->select('m')
           ->from('App\Entity\Message', 'm')
           ->where($qb->expr()->andX($qb->expr()->eq('m.outgoing_msg', $this->getUser()->getId()), $qb->expr()->eq('m.incoming_msg', $user->getId())))
           ->orWhere($qb->expr()->andX($qb->expr()->eq('m.incoming_msg', $this->getUser()->getId()), $qb->expr()->eq('m.outgoing_msg', $user->getId())))
           ->orderBy('m.id', 'ASC');

        $qb = $qb->getQuery();

        $messages = $qb->execute();

        if($messages){
            foreach($messages as $mess){
                if(($mess->getStatus() < 2) && ($mess->getIncomingMsg() == $this->getUser())){
                    $mess->setStatus(2);
                    $this->em->flush();
                }
            }
        }

        $data = $this->apiResponseData($messages);

        return $this->json($data, Response::HTTP_OK);
    }

    // SEND MESSAGE

    public function send_message($request, $uuid): Response
    {

        $text = $request->get('text');
        $text = strip_tags($text);

        if(!$text){
            return $this->json('Not Acceptable', Response::HTTP_NOT_ACCEPTABLE);
        }
        
        $message = new Message;

        $user = $this->userrespository->findBy(['uuid' => $uuid])[0];

        $message->setIncomingMsg($user)
                ->setOutgoingMsg($this->getUser())
                ->setMsg($text)
                ->setCreated(new \DateTime())
                ->setStatus(0);

        $this->em->persist($message);
        $this->em->flush();

        return $this->json('Created', Response::HTTP_CREATED);
    }

    // DELETE MESSAGE
    
    public function delete_message($id): Response
    {
        if($this->getUser()){
            $message = $this->messagerespository->findBy(['id' => $id, 'outgoing_msg' => $this->getUser()->getId()])[0];
            if($message->getMsg() != '<i>Usunięta Wiadomość</i>'){
                $message->setMsg('<i>Usunięta Wiadomość</i>');
                $this->em->flush();
            }
        }else{
            return $this->json('FORBIDDEN', Response::HTTP_FORBIDDEN);
        }

        return $this->json('Deleted', Response::HTTP_OK);
    }
}
