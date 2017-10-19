<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Message;
use AppBundle\Entity\UserCommunity;
use AppBundle\Form\newMessageType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/community")
 */
class CommunityController extends Controller
{
    /**
     * @Route("/", name="communityList")
     */
    public function communityListAction()
    {
        $user = $this->getUser();
        $signup = [];

        $joined = $this->getDoctrine()->getRepository('AppBundle:UserCommunity')->findBy(
            array(
                'user' => $user,
                'isActive' => true,
                'isDeleted' => false
            )
        );

        $communitiesUser = $this->getDoctrine()->getRepository('AppBundle:UserCommunity')->findBy([
            'user' => $user,
            'isActive' => true,
            'isDeleted' => false
        ]);

        foreach($communitiesUser as $community){
            array_push($signup, $community->getCommunity());
        }

        $officials = $this->getDoctrine()->getRepository('AppBundle:Community')->findBy([
            'privacy' => 'default',
            'isSuspended' => false,
            'isDeleted' => false,
            'isBlock' => false
        ]);

        $admin = $this->getDoctrine()->getRepository('AppBundle:Community')->findBy([
            'admin' => $user,
            'isSuspended' => false,
            'isDeleted' => false,
            'isBlock' => false
        ]);

        $last = $this->getDoctrine()->getRepository('AppBundle:Community')->findBy([
            'privacy' => [
                'public', 'protected', 'default'
            ],
            'isBlock' => false,
            'isDeleted' => false,
            'isSuspended' => false
        ],[
            'creationDate' => 'DESC'
        ], 15);

        $communityList = $this->getDoctrine()->getRepository('AppBundle:Community')->findBy([
            'isBlock' => false,
            'isSuspended' => false,
            'isDeleted' => false
        ]);

        return $this->render('Community/comunitiesList.html.twig', [
            'user' => $user,
            'signup' => $signup,
            'officials' => $officials,
            'admin' => $admin,
            'last' => $last,
            'communityList' => $communityList,
            'joined' => $joined
        ]);
    }

    /**
     * @Route("/general", name="generalCommunity")
     */
    public function generalCommunityAction(Request $request)
    {
        $user = $this->getUser();

        $community = $this->getDoctrine()->getRepository('AppBundle:Community')->findOneBy([
            'privacy' => 'default',
            'name' => 'General',
            'communityCreator' => null,
            'admin' => null
        ]);

        $msg = new Message();
        $form = $this->createForm(newMessageType::class, $msg);
        $form->handleRequest($request);

        $messages = $this->getDoctrine()->getRepository('AppBundle:Message')->findBy([
            'community' => $community,
            'isReply' => false,
            'isActive' => true,
            'isDeleted' => false,
            'isBlock' => false
        ],[
            'date' => 'DESC'
        ]);

        if($form->isSubmitted() && $form->isValid()){
            $x = 1;

            while($x < 3){
                $message = $msg->getMessage();

                switch($x){
                    case 1: {
                        $s = 'https://';
                        $n = substr_count($message, $s);
                        break;
                    }

                    case 2: {
                        $s = 'http://';
                        $n = substr_count($message, $s);
                        break;
                    }

                    default: {
                        $n = 0;
                        $s = '';
                        break;
                    }
                }

                for($i=0; $i<$n; $i++){
                    $pos = strpos($message, $s);

                    if($pos !== false){
                        $message = substr($message, $pos);
                        $pos2 = strpos($message, ' ');

                        if($pos2 === false){
                            $pos2 = strlen($message);
                        }

                        $link = substr($message, 0, $pos2);

                        if(strlen($link) != strlen($s)){
                            $replace = '<a href="'.$link.'">'.$link.'</a>';
                            $msg->setMessage((str_replace($link, $replace, $msg->getMessage())));

                        }

                        $message = substr($message, $pos2);
                    }
                }

                $x++;

            }

            $msg->setUser($user);
            $msg->setCommunity($community);
            $msg->setDate(new \DateTime("now"));
            $msg->setIsActive(1);
            $msg->setIsBlock(0);
            $msg->setIsDeleted(0);
            $msg->setIsReply(0);
            $msg->setIP($this->get('request_stack')->getCurrentRequest()->getClientIp());
            $msg->setNotificationRead(1);

            $em = $this->getDoctrine()->getManager();
            $em->persist($msg);
            $em->flush();

            return $this->redirectToRoute('generalCommunity');
        }

        return $this->render('Community/community.html.twig', [
            'user' => $user,
            'access' => 'default',
            'community' => $community,
            'msg' => $form->createView(),
            'messages' => $messages
        ]);
    }

    /**
     * @Route("/general/{id}", name="messageDetailsGeneral")
     */
    public function messageDetailsGeneralAction($id, Request $request)
    {
        $user = $this->getUser();
        $access = "";

        $message = $this->getDoctrine()->getRepository('AppBundle:Message')->find($id);
        $form = null;


        if(count($message) == 0){
            $message = null;
            $access = 'notFound';
        }else{
            if($message->getCommunity()->getId() != 1 || $message->getCommunity()->getName() != 'General'){
                return $this->redirectToRoute('messageDetails', [
                    'id' => $message->getId()
                ]);
            }
            if($message->isIsDeleted()){
                $access = 'deleted';
            }elseif ($message->isIsBlock()) {
                $access = 'block';
            }elseif ($message->isIsActive() == false){
                $access = 'inactive';
            }else{
                $access = 'default';
                $msg = new Message();
                $form = $this->createForm(newMessageType::class, $msg);
                $form->handleRequest($request);

                if($form->isSubmitted() && $form->isValid()){
                    $x = 1;

                    while($x < 3){
                        $messageTemp = $msg->getMessage();

                        switch($x){
                            case 1: {
                                $s = 'https://';
                                $n = substr_count($messageTemp, $s);
                                break;
                            }

                            case 2: {
                                $s = 'http://';
                                $n = substr_count($messageTemp, $s);
                                break;
                            }

                            default: {
                                $n = 0;
                                $s = '';
                                break;
                            }
                        }

                        for($i=0; $i<$n; $i++){
                            $pos = strpos($messageTemp, $s);

                            if($pos !== false){
                                $messageTemp = substr($messageTemp, $pos);
                                $pos2 = strpos($messageTemp, ' ');

                                if($pos2 === false){
                                    $pos2 = strlen($messageTemp);
                                }

                                $link = substr($messageTemp, 0, $pos2);

                                if(strlen($link) != strlen($s)){
                                    $replace = '<a href="'.$link.'">'.$link.'</a>';
                                    $msg->setMessage((str_replace($link, $replace, $msg->getMessage())));

                                }

                                $messageTemp = substr($messageTemp, $pos2);
                            }
                        }

                        $x++;

                    }

                    $msg->setUser($user);
                    $msg->setReply($message);
                    $msg->setCommunity($message->getCommunity());
                    $msg->setDate(new \DateTime("now"));
                    $msg->setIsActive(1);
                    $msg->setIsBlock(0);
                    $msg->setIsDeleted(0);
                    $msg->setIsReply(1);
                    $msg->setIP($this->get('request_stack')->getCurrentRequest()->getClientIp());
                    $msg->setNotificationRead(0);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($msg);
                    $em->flush();

                    return $this->redirectToRoute('messageDetailsGeneral', [
                        'id' => $message->getId()
                    ]);
                }

                $form = $form->createView();
            }
        }

        return $this->render('Community/messageDetails.html.twig', [
            'user' => $user,
            'access' => $access,
            'message' => $message,
            'isGeneral' => true,
            'form' => $form
        ]);
    }

    /**
     * @Route("/deleteMessage/{id}", name="deleteMessage")
     */
    public function deleteMessageAction($id)
    {
        $status = 'Se ha producido un error desconocido.';
        $user = $this->getUser();

        $message = $this->getDoctrine()->getRepository('AppBundle:Message')->find($id);

        if(($message->getUser() == $user) && ($message->isIsActive() == true) && ($message->isIsDeleted() == false) && ($message->isIsBlock() == false)){
            if(count($message) == 1){
                $em = $this->getDoctrine()->getManager();

                $replies = $this->getDoctrine()->getRepository('AppBundle:Message')->findBy([
                    'isReply' => true,
                    'reply' => $message,
                    'isDeleted' => false,
                    'isActive' => true,
                    'isBlock' => false
                ]);

                for($i=0; $i<count($replies); $i++){
                    $replies[$i]->setIsDeleted(1);
                    $em->persist($replies[$i]);
                    $em->flush();
                }

                $message->setIsDeleted(1);
                $em->persist($message);
                $em->flush();

                $status = 'Mensaje eliminado correctamente.';
            }else{
                $status = 'No tienes permisos para borrar este mensaje.';
            }
        }else{
            $status = 'No tienes permisos para borrar este mensaje.';
        }

        return $this->render('Community/deleteMessage.html.twig', [
            'status' => $status
        ]);
    }

    /**
     * @Route("/{id}", name="viewCommunity")
     */
    public function viewCommunityAction($id, Request $request)
    {
        $community = $this->getDoctrine()->getRepository('AppBundle:Community')->find($id);

        $user = $this->getUser();
        $userAccess = false;
        $status = '';
        $messages = '';
        $form = '';

        if(count($community) == 1){
            if($community->getPrivacy() == 'default' && $community->getName() == 'General'){
                return $this->redirectToRoute('generalCommunity');
            }

            if($community->isIsBlock()){
                $access = 'blocked';
            }elseif($community->isIsSuspended()){
                $access = 'suspended';
            }elseif($community->isIsDeleted()){
                $access = 'deleted';
            }else{
                $access = 'active';
                $privacy = $community->getPrivacy();

                switch($privacy){
                    case 'public': {
                        $userAccess = true;
                        $status = 'full';
                        break;
                    }

                    case 'private': {
                        $check = $this->getDoctrine()->getRepository('AppBundle:UserCommunity')->findOneBy([
                            'community' => $community,
                            'user' => $user,
                            'isDeleted' => false
                        ]);

                        if(count($check) == 1){
                            if($check->getIsActive()){
                                $userAccess = true;
                                $status = 'full';
                            }else{
                                $status = 'notAccepted';
                            }
                        }else{
                            $userAccess = false;
                            $status = 'userRelationNotFound';
                        }

                        break;
                    }

                    case 'protected': {
                        $userAccess = true;

                        $check = $this->getDoctrine()->getRepository('AppBundle:UserCommunity')->findOneBy([
                            'community' => $community,
                            'user' => $user,
                            'isDeleted' => false
                        ]);

                        if(count($check) == 1){
                            if($check->getIsActive()){
                                $status = 'protectedAllow';
                            }else{
                                $status = 'protectedDeny';
                            }
                        }else{
                            $status = 'protectedDeny';
                        }

                        break;
                    }

                    case 'default': {
                        $userAccess = true;
                        $status = 'full';
                        break;
                    }
                }

                if($userAccess){
                    $messages = $this->getDoctrine()->getRepository('AppBundle:Message')->findBy([
                        'community' => $community,
                        'isReply' => false,
                        'isActive' => true,
                        'isDeleted' => false,
                        'isBlock' => false
                    ],[
                        'date' => 'DESC'
                    ]);

                    if($status == 'protectedAllow' || $status == 'full'){
                        $msg = new Message();
                        $form = $this->createForm(newMessageType::class, $msg);
                        $form->handleRequest($request);

                        if($form->isSubmitted() && $form->isValid()){
                            $x = 1;

                            while($x < 3){
                                $message = $msg->getMessage();

                                switch($x){
                                    case 1: {
                                        $s = 'https://';
                                        $n = substr_count($message, $s);
                                        break;
                                    }

                                    case 2: {
                                        $s = 'http://';
                                        $n = substr_count($message, $s);
                                        break;
                                    }

                                    default: {
                                        $n = 0;
                                        $s = '';
                                        break;
                                    }
                                }

                                for($i=0; $i<$n; $i++){
                                    $pos = strpos($message, $s);

                                    if($pos !== false){
                                        $message = substr($message, $pos);
                                        $pos2 = strpos($message, ' ');

                                        if($pos2 === false){
                                            $pos2 = strlen($message);
                                        }

                                        $link = substr($message, 0, $pos2);

                                        if(strlen($link) != strlen($s)){
                                            $replace = '<a href="'.$link.'">'.$link.'</a>';
                                            $msg->setMessage((str_replace($link, $replace, $msg->getMessage())));

                                        }

                                        $message = substr($message, $pos2);
                                    }
                                }

                                $x++;

                            }

                            $msg->setUser($user);
                            $msg->setCommunity($community);
                            $msg->setDate(new \DateTime("now"));
                            $msg->setIsActive(1);
                            $msg->setIsBlock(0);
                            $msg->setIsDeleted(0);
                            $msg->setIsReply(0);
                            $msg->setIP($this->get('request_stack')->getCurrentRequest()->getClientIp());
                            $msg->setNotificationRead(1);

                            $em = $this->getDoctrine()->getManager();
                            $em->persist($msg);
                            $em->flush();

                            return $this->redirectToRoute('viewCommunity', ['id'=>$community->getId()]);
                        }

                        $form = $form->createView();
                    }
                }
            }

        }else{
            $community = null;
            $access = 'notFound';
        }


        return $this->render('Community/community.html.twig', [
            'user' => $user,
            'community' => $community,
            'access' => $access,
            'status' => $status,
            'messages' => $messages,
            'msg' => $form,
            'userAccess' => $userAccess
        ]);
    }

    /**
     * @Route("/message/{id}", name="messageDetails")
     */
    public function messageDetailsAction($id, Request $request)
    {
        $user = $this->getUser();
        $access = "";
        $userAccess = false;
        $status = "";

        $message = $this->getDoctrine()->getRepository('AppBundle:Message')->find($id);

        $form = null;


        if(count($message) == 0){
            $message = null;
            $access = 'notFound';
        }else{
            if($message->getCommunity()->getPrivacy() == 'default' && $message->getCommunity()->getName() == 'General'){
                return $this->redirectToRoute('messageDetailsGeneral', ['id'=>$message->getId()]);
            }

            if($message->isIsDeleted()){
                $access = 'deleted';
            }elseif ($message->isIsBlock()) {
                $access = 'block';
            }elseif ($message->isIsActive() == false){
                $access = 'inactive';
            }else{
                $access = 'active';
                $privacy = $message->getCommunity()->getPrivacy();

                switch($privacy){
                    case 'public': {
                        $userAccess = true;
                        $status = 'full';
                        break;
                    }

                    case 'private': {
                        $check = $this->getDoctrine()->getRepository('AppBundle:UserCommunity')->findOneBy([
                            'community' => $message->getCommunity(),
                            'user' => $user,
                            'isDeleted' => false
                        ]);

                        if(count($check) == 1){
                            if($check->getIsActive()){
                                $userAccess = true;
                                $status = 'full';
                            }else{
                                $status = 'notAccepted';
                            }
                        }else{
                            $userAccess = false;
                            $status = 'userRelationNotFound';
                        }

                        break;
                    }

                    case 'protected': {
                        $userAccess = true;

                        $check = $this->getDoctrine()->getRepository('AppBundle:UserCommunity')->findOneBy([
                            'community' => $message->getCommunity(),
                            'user' => $user,
                            'isDeleted' => false
                        ]);

                        if(count($check) == 1){
                            if($check->getIsActive()){
                                $status = 'protectedAllow';
                            }else{
                                $status = 'protectedDeny';
                            }
                        }else{
                            $status = 'protectedDeny';
                        }

                        break;
                    }

                    case 'default': {
                        $status = null;
                        break;
                    }
                }

                if($userAccess){
                    if($status == 'protectedAllow' || $status == 'full'){
                        $msg = new Message();
                        $form = $this->createForm(newMessageType::class, $msg);
                        $form->handleRequest($request);

                        if($form->isSubmitted() && $form->isValid()){
                            $x = 1;

                            while($x < 3){
                                $messageTemp = $msg->getMessage();

                                switch($x){
                                    case 1: {
                                        $s = 'https://';
                                        $n = substr_count($messageTemp, $s);
                                        break;
                                    }

                                    case 2: {
                                        $s = 'http://';
                                        $n = substr_count($messageTemp, $s);
                                        break;
                                    }

                                    default: {
                                        $n = 0;
                                        $s = '';
                                        break;
                                    }
                                }

                                for($i=0; $i<$n; $i++){
                                    $pos = strpos($messageTemp, $s);

                                    if($pos !== false){
                                        $messageTemp = substr($messageTemp, $pos);
                                        $pos2 = strpos($messageTemp, ' ');

                                        if($pos2 === false){
                                            $pos2 = strlen($messageTemp);
                                        }

                                        $link = substr($messageTemp, 0, $pos2);

                                        if(strlen($link) != strlen($s)){
                                            $replace = '<a href="'.$link.'">'.$link.'</a>';
                                            $msg->setMessage((str_replace($link, $replace, $msg->getMessage())));

                                        }

                                        $messageTemp = substr($messageTemp, $pos2);
                                    }
                                }

                                $x++;

                            }

                            $msg->setUser($user);
                            $msg->setReply($message);
                            $msg->setCommunity($message->getCommunity());
                            $msg->setDate(new \DateTime("now"));
                            $msg->setIsActive(1);
                            $msg->setIsBlock(0);
                            $msg->setIsDeleted(0);
                            $msg->setIsReply(1);
                            $msg->setIP($this->get('request_stack')->getCurrentRequest()->getClientIp());
                            $msg->setNotificationRead(0);

                            $em = $this->getDoctrine()->getManager();
                            $em->persist($msg);
                            $em->flush();

                            return $this->redirectToRoute('messageDetails', [
                                'id' => $message->getId()
                            ]);
                        }

                        $form = $form->createView();
                    }
                }
            }
        }

        return $this->render('Community/messageDetails.html.twig', [
            'user' => $user,
            'access' => $access,
            'message' => $message,
            'isGeneral' => false,
            'status' => $status,
            'form' => $form
        ]);
    }

    /**
     * @Route("/leave", name="leaveNULLCommunity")
     */
    public function leaveCommunityNullAction(Request $request)
    {
        return $this->redirectToRoute('communityList');
    }

    /**
     * @Route("/leave/{id}", name="leaveCommunity")
     */
    public function leaveCommunityAction(Request $request, $id)
    {
        $user = $this->getUser();

        $community = $this->getDoctrine()->getRepository('AppBundle:Community')->find($id);

        if($community){
            $isJoin = $this->getDoctrine()->getRepository('AppBundle:UserCommunity')->findBy(
                array(
                    'community' => $community,
                    'user' => $user,
                    'isDeleted' => false,
                    'isActive' => true
                )
            );

            if(count($isJoin) == 1){
                $isJoin = $isJoin[0];

                $isJoin->setIsDeleted(1);
                $isJoin->setIsActive(0);

                $em = $this->getDoctrine()->getManager();
                $em->persist($isJoin);
                $em->flush();

            }
        }

        return $this->redirectToRoute('communityList');
    }

    /**
     * @Route("/join", name="joinNULLCommunity")
     */
    public function joinCommunityNULLAction(Request $request)
    {
        return $this->redirectToRoute('communityList');
    }

    /**
     * @Route("/join/{id}", name="joinCommunity")
     */
    public function joinCommunityAction(Request $request, $id)
    {
        $user = $this->getUser();

        $community = $this->getDoctrine()->getRepository('AppBundle:Community')->find($id);

        if($community){
            $isJoined = $this->getDoctrine()->getRepository('AppBundle:UserCommunity')->findBy(
                array(
                    'community' => $community,
                    'user' => $user
                )
            );

            if(count($isJoined) > 0){
                for($i=0; $i<count($isJoined); $i++){
                    if($isJoined[$i]->getIsActive()){
                        return $this->redirectToRoute('communityList');
                    }
                }
            }

            if($community->getPrivacy() == 'public' || $community->getPrivacy() == 'default') {
                $join = new UserCommunity();
                $join->setIsActive(1);
                $join->setIsDeleted(0);
                $join->setDate(new \DateTime("now"));
                $join->setUser($user);
                $join->setCommunity($community);

                $em = $this->getDoctrine()->getManager();
                $em->persist($join);
                $em->flush();
            }
        }

        return $this->redirectToRoute('communityList');
    }
}