<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Invitation;
use AppBundle\Entity\Message;
use AppBundle\Entity\Notification;
use AppBundle\Entity\UserCommunity;
use AppBundle\Form\EditCommunityType;
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

        if($user->isIsSuspended() || $user->isIsBlock()){
            return $this->redirectToRoute('logout');
        }

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
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function generalCommunityAction(Request $request)
    {
        $user = $this->getUser();

        if($user->isIsSuspended() || $user->isIsBlock()){
            return $this->redirectToRoute('logout');
        }

        $nMSG = 0;

        $community = $this->getDoctrine()->getRepository('AppBundle:Community')->findOneBy([
            'privacy' => 'default',
            'name' => 'General',
            'communityCreator' => null,
            'admin' => null
        ]);

        $community->setVisits($community->getVisits()+1);

        $em = $this->getDoctrine()->getManager();
        $em->persist($community);
        $em->flush();

        $joined = $this->getDoctrine()->getRepository('AppBundle:UserCommunity')->findBy(
            array(
                'user' => $user,
                'isActive' => true,
                'isDeleted' => false,
                'community' => $community
            )
        );

        if($joined){
            $joined = true;
        }else{
            $joined = false;
        }

        $msg = new Message();
        $form = $this->createForm(newMessageType::class, $msg);
        $form->handleRequest($request);

        $page = 0;

        if(isset($_GET['page'])){
            if($_GET['page'] > 0){
                $page = $_GET['page'] - 1;
            }
        }

        $nMSG = $this->getDoctrine()->getRepository('AppBundle:Message')->findBy([
            'community' => $community,
            'isReply' => false,
            'isActive' => true,
            'isDeleted' => false,
            'isBlock' => false
        ]);

        $nMSG = count($nMSG);

        $messages = $this->getDoctrine()->getRepository('AppBundle:Message')->findBy([
            'community' => $community,
            'isReply' => false,
            'isActive' => true,
            'isDeleted' => false,
            'isBlock' => false
        ],[
            'date' => 'DESC'
        ], 10, 10*$page);

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
            'messages' => $messages,
            'joined' => $joined,
            'nMSG' => $nMSG
        ]);
    }

    /**
     * @Route("/general/{id}", name="messageDetailsGeneral")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function messageDetailsGeneralAction($id, Request $request)
    {
        $user = $this->getUser();

        if($user->isIsSuspended() || $user->isIsBlock()){
            return $this->redirectToRoute('logout');
        }

        $form = null;

        $message = $this->getDoctrine()->getRepository('AppBundle:Message')->find($id);

        if(!$message){
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
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteMessageAction($id)
    {
        $user = $this->getUser();

        if($user->isIsSuspended() || $user->isIsBlock()){
            return $this->redirectToRoute('logout');
        }

        $message = $this->getDoctrine()->getRepository('AppBundle:Message')->find($id);

        if(!$message){
            $status = 'Este mensaje no existe.';
        }else{
            if($message->getUser() == $user && $message->isIsActive() && !$message->isIsDeleted() && !$message->isIsBlock()){
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
        }

        return $this->render('Community/deleteMessage.html.twig', [
            'status' => $status
        ]);
    }

    /**
     * @Route("/{id}", name="viewCommunity")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function viewCommunityAction($id, Request $request)
    {
        $user = $this->getUser();

        if($user->isIsSuspended() || $user->isIsBlock()){
            return $this->redirectToRoute('logout');
        }

        $userAccess = false;
        $status = '';
        $messages = '';
        $form = '';
        $joined = 0;
        $nMSG = 0;

        $community = $this->getDoctrine()->getRepository('AppBundle:Community')->find($id);

        if($community){
            $community->setVisits($community->getVisits()+1);

            $em = $this->getDoctrine()->getManager();
            $em->persist($community);
            $em->flush();

            $joined = $this->getDoctrine()->getRepository('AppBundle:UserCommunity')->findBy(
                array(
                    'community' => $community,
                    'isDeleted' => false,
                    'isActive' => true,
                    'user' => $user
                )
            );

            if($joined){
                $joined = 1;
            }else{
                $joined = $this->getDoctrine()->getRepository('AppBundle:UserCommunity')->findBy(
                    array(
                        'community' => $community,
                        'user' => $user,
                        'isActive' => false,
                        'isDeleted' => false
                    )
                );

                if($joined){
                    $joined = 2;
                }else{
                    $joined = 3;
                }
            }

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

                        if($check){
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

                        if($check){
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
                    $page = 0;

                    if(isset($_GET['page'])){
                        if($_GET['page'] > 0){
                            $page = $_GET['page'] - 1;
                        }
                    }

                    $nMSG = $this->getDoctrine()->getRepository('AppBundle:Message')->findBy([
                        'community' => $community,
                        'isReply' => false,
                        'isActive' => true,
                        'isDeleted' => false,
                        'isBlock' => false
                    ]);

                    $nMSG = count($nMSG);

                    $messages = $this->getDoctrine()->getRepository('AppBundle:Message')->findBy([
                        'community' => $community,
                        'isReply' => false,
                        'isActive' => true,
                        'isDeleted' => false,
                        'isBlock' => false
                    ],[
                        'date' => 'DESC'
                    ], 10, 10*$page);

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
            'userAccess' => $userAccess,
            'joined' => $joined,
            'nMSG' => $nMSG
        ]);
    }

    /**
     * @Route("/message/{id}", name="messageDetails")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function messageDetailsAction($id, Request $request)
    {
        $user = $this->getUser();

        if($user->isIsSuspended() || $user->isIsBlock()){
            return $this->redirectToRoute('logout');
        }

        $userAccess = false;
        $status = "";
        $form = null;

        $message = $this->getDoctrine()->getRepository('AppBundle:Message')->find($id);

        if(!$message){
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
            }elseif(!$message->isIsActive()){
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

                        if($check){
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

                        if($check){
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
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function leaveCommunityNullAction()
    {
        return $this->redirectToRoute('communityList');
    }

    /**
     * @Route("/leave/{id}", name="leaveCommunity")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function leaveCommunityAction($id)
    {
        $user = $this->getUser();

        if($user->isIsSuspended() || $user->isIsBlock()){
            return $this->redirectToRoute('logout');
        }

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

            }else{
                $isJoin = $this->getDoctrine()->getRepository('AppBundle:UserCommunity')->findBy(
                    array(
                        'community' => $community,
                        'user' => $user,
                        'isDeleted' => false,
                        'isActive' => false
                    )
                );

                if(count($isJoin) == 1){
                    $isJoin = $isJoin[0];

                    $isJoin->setIsDeleted(1);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($isJoin);
                    $em->flush();
                }
            }

            return $this->redirectToRoute('viewCommunity', [
                'id' => $community->getId()
            ]);
        }else{
            return $this->redirectToRoute('viewCommunity', [
                'id' => $id
            ]);
        }
    }

    /**
     * @Route("/join", name="joinNULLCommunity")
     */
    public function joinCommunityNULLAction()
    {
        return $this->redirectToRoute('communityList');
    }

    /**
     * @Route("/join/{id}", name="joinCommunity")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function joinCommunityAction($id)
    {
        $user = $this->getUser();

        if($user->isIsSuspended() || $user->isIsBlock()){
            return $this->redirectToRoute('logout');
        }

        $community = $this->getDoctrine()->getRepository('AppBundle:Community')->find($id);

        if($community){
            if($community->getAdmin() == $user){
                $join = new UserCommunity();
                $join->setIsDeleted(0);
                $join->setDate(new \DateTime("now"));
                $join->setUser($user);
                $join->setCommunity($community);
                $join->setIsActive(1);

                $em = $this->getDoctrine()->getManager();
                $em->persist($join);
                $em->flush();

                return $this->redirectToRoute('viewCommunity', ['id' => $join->getCommunity()->getId()]);
            }

            $isJoined = $this->getDoctrine()->getRepository('AppBundle:UserCommunity')->findBy(
                array(
                    'community' => $community,
                    'user' => $user
                )
            );

            if(count($isJoined) > 0){
                for($i=0; $i<count($isJoined); $i++){
                    if($isJoined[$i]->getIsActive()){
                        return $this->redirectToRoute('viewCommunity', [
                            'id' => $community->getId()
                        ]);
                    }
                }
            }

            $isPending = $this->getDoctrine()->getRepository('AppBundle:UserCommunity')->findBy(
                array(
                    'community' => $community,
                    'user' => $user,
                    'isActive' => false,
                    'isDeleted' => false
                )
            );

            if(count($isPending) == 1){
                return $this->redirectToRoute('viewCommunity', ['id' => $community->getId()]);
            }

            $join = new UserCommunity();
            $join->setIsDeleted(0);
            $join->setDate(new \DateTime("now"));
            $join->setUser($user);
            $join->setCommunity($community);

            if($community->getPrivacy() == 'public' || $community->getPrivacy() == 'default') {
                $join->setIsActive(1);
            }else{
                $join->setIsActive(0);

                $notification = new Notification();
                $notification->setUser($community->getAdmin());
                $notification->setDate(new \DateTime("now"));
                $notification->setType("com_request");
                $notification->setDescription("El usuario " . $user->getUsername() . " quiere acceder a tu comunidad: " . $community->getName() . ".");

                $em = $this->getDoctrine()->getManager();
                $em->persist($notification);
                $em->flush();
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($join);
            $em->flush();

            return $this->redirectToRoute('viewCommunity', [
                'id' => $community->getId()
            ]);
        }else{
            return $this->redirectToRoute('viewCommunity', [
                'id' => $id
            ]);
        }
    }

    /**
     * @Route("/{id}/info", name="communityInfo")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function infoAction($id)
    {
        $user = $this->getUser();

        if($user->isIsSuspended() || $user->isIsBlock()){
            return $this->redirectToRoute('logout');
        }

        $community = $this->getDoctrine()->getRepository('AppBundle:Community')->find($id);

        if($community){
            $isJoined = $this->getDoctrine()->getRepository('AppBundle:UserCommunity')->findBy(
                array(
                    'user' => $user,
                    'community' => $community,
                    'isActive' => true,
                    'isDeleted' => false
                )
            );

            if(count($isJoined) == 1){
                $isJoined = true;
            }else{
                $isJoined = false;
            }
        }else{
            return $this->redirectToRoute('viewCommunity', [
                'id' => $id
            ]);
        }

        return $this->render('Community/communityInfo.html.twig', [
            'user' => $user,
            'community' => $community,
            'isJoined' => $isJoined
        ]);
    }

    /**
     * @Route("/{id}/admin", name="adminCommunity")
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function adminCommunityAction(Request $request, $id)
    {
        $user = $this->getUser();

        if($user->isIsSuspended() || $user->isIsBlock()){
            return $this->redirectToRoute('logout');
        }

        $error = 0;

        if(isset($_POST['newUser'])){
            $username = $_POST['newUser'];
        }else{
            $username = null;
        }

        $community = $this->getDoctrine()->getRepository('AppBundle:Community')->find($id);

        if($community){
            if($community->getAdmin() == $user || $user->getIsAdmin()){
                $em = $this->getDoctrine()->getManager();

                $joined = $this->getDoctrine()->getRepository('AppBundle:UserCommunity')->findBy(
                    array(
                        'community' => $community,
                        'isDeleted' => false,
                        'isActive' => true
                    )
                );

                $pending = $this->getDoctrine()->getRepository('AppBundle:UserCommunity')->findBy(
                    array(
                        'community' => $community,
                        'isDeleted' => false,
                        'isActive' => false
                    )
                );

                if($username){
                    $newUser = $this->getDoctrine()->getRepository('AppBundle:User')->findOneBy(
                        array(
                            'username' => $username
                        )
                    );

                    if($newUser){
                        $isJoined = $this->getDoctrine()->getRepository('AppBundle:UserCommunity')->findOneBy(
                            array(
                                'user' => $newUser,
                                'community' => $community,
                                'isDeleted' => false,
                                'isActive' => true
                            )
                        );

                        if(!$isJoined){
                            if($newUser->isIsSuspended()){
                                $error = 3;
                            }elseif($newUser->isIsBlock()){
                                $error = 4;
                            }else{
                                $invitation = new Invitation();
                                $invitation->setUser($newUser);
                                $invitation->setIsUsed(0);
                                $invitation->setIsActive(1);
                                $invitation->setIsDeleted(0);
                                $invitation->setAcceptedDeclineDate(null);
                                $invitation->setIsAccepted(0);
                                $invitation->setCommunity($community);
                                $invitation->setDate(new \DateTime("now"));
                                $invitation->setMessage("Invitación a: " . $community->getName());

                                $em->persist($invitation);
                                $em->flush();

                                $notification = new Notification();
                                $notification->setDate(new \DateTime("now"));
                                $notification->setUser($newUser);
                                $notification->setDescription("Has recibido una invitación para unirte a " . $community->getName());
                                $notification->setType("com_invitation");

                                $em->persist($notification);
                                $em->flush();

                                $error = 5;
                            }
                        }else{
                            $error = 2;
                        }
                    }else{
                        $error = 1;
                    }
                }

                $info = $this->createForm(EditCommunityType::class, $community);
                $info->handleRequest($request);

                if($info->isSubmitted() && $info->isValid()){
                    $em->persist($community);
                    $em->flush();
                }
            }else{
                return $this->redirectToRoute('viewCommunity', ['id' => $community->getId()]);
            }
        }else{
            return $this->redirectToRoute('viewCommunity', ['id' => $id]);
        }

        return $this->render('Community/communityAdmin.html.twig', [
            'user' => $user,
            'community' => $community,
            'joined' => $joined,
            'error' => $error,
            'pending' => $pending,
            'info' => $info->createView()
        ]);
    }

    /**
     * @Route("/{id}/kick/", name="NULLKickUser")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function KickNULLUserAction($id){
        $community = $this->getDoctrine()->getRepository('AppBundle:Community')->find($id);

        if($community){
            return $this->redirectToRoute('viewCommunity', ['id' => $id]);
        }else{
            return $this->redirectToRoute('homepage');
        }
    }

    /**
     * @Route("/{id}/kick/{idUser}", name="KickUser")
     * @param $id
     * @param $idUser
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function kickUserAction($id, $idUser){
        $user = $this->getUser();

        if($user->isIsSuspended() || $user->isIsBlock()){
            return $this->redirectToRoute('logout');
        }

        $community = $this->getDoctrine()->getRepository('AppBundle:Community')->find($id);

        if($community){
            $em = $this->getDoctrine()->getManager();

            if($community->getAdmin() == $user || $user->getIsAdmin()){
                $userToKick = $this->getDoctrine()->getRepository('AppBundle:User')->find($idUser);

                if($userToKick){
                    $users = $this->getDoctrine()->getRepository('AppBundle:UserCommunity')->findBy(
                        array(
                            'user' => $userToKick,
                            'community' => $community,
                            'isActive' => true,
                            'isDeleted' => false
                        )
                    );

                    for($i=0; $i<count($users); $i++){
                        $users[$i]->setIsActive(0);
                        $users[$i]->setIsDeleted(1);

                        $em->persist($users[$i]);
                        $em->flush();
                    }
                }
            }
        }

        return $this->redirectToRoute('viewCommunity', ['id' => $id]);
    }

    /**
     * @Route("/{id}/changeAdmin", name="changeAdministrator")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function changeAdministratorAction($id)
    {
        $user = $this->getUser();

        if($user->isIsSuspended() || $user->isIsBlock()){
            return $this->redirectToRoute('logout');
        }

        $error = 0;

        if(isset($_POST['username'])){
            $username = $_POST['username'];
        }else{
            $username = null;
        }

        $community = $this->getDoctrine()->getRepository('AppBundle:Community')->find($id);

        if($community){
            if($community->getAdmin() == $user || $user->getIsAdmin()){
                if($username){
                    $admin = $this->getDoctrine()->getRepository('AppBundle:User')->findOneBy(
                        array(
                            'username' => $username
                        )
                    );

                    if($admin){
                        if($admin == $user){
                            $error = 2;
                        }else{
                            if($admin->isIsSuspended()){
                                $error = 3;
                            }elseif($admin->isIsBlock()){
                                $error = 4;
                            }else{
                                $community->setAdmin($admin);

                                $em = $this->getDoctrine()->getManager();
                                $em->persist($community);
                                $em->flush();

                                $notification = new Notification();
                                $notification->setType('new_admin');
                                $notification->setDescription('Eres el nuevo administrador de la comunidad: ' .$community->getName());
                                $notification->setUser($admin);
                                $notification->setDate(new \DateTime("now"));

                                $em->persist($notification);
                                $em->flush();

                                $error = 5;
                            }
                        }
                    }else{
                        $error = 1;
                    }
                }
            }else{
                return $this->redirectToRoute('viewCommunity', ['id' => $id]);
            }
        }else{
            return $this->redirectToRoute('viewCommunity', ['id' => $id]);
        }

        return $this->render('Community/communityChangeAdmin.html.twig', [
            'user' => $user,
            'community' => $community,
            'error' => $error
        ]);
    }

    /**
     * @Route("/{id}/accept", name="NULLacceptRequestNULL")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function NULLAcceptRequestAction($id)
    {
        return $this->redirectToRoute('viewCommunity', ['id' => $id]);
    }

    /**
     * @Route("/{id}/accept/{idRequest}", name="acceptRequest")
     */
    public function acceptRequestAction($id, $idRequest)
    {
        $user = $this->getUser();

        if($user->isIsSuspended() || $user->isIsBlock()){
            return $this->redirectToRoute('logout');
        }

        $community = $this->getDoctrine()->getRepository('AppBundle:Community')->find($id);
        $request = $this->getDoctrine()->getRepository('AppBundle:UserCommunity')->find($idRequest);

        if($community){
            if($request) {
                if($community->getAdmin() == $user && $community == $request->getCommunity()) {
                    if(!$request->getIsDeleted() && !$request->getIsActive()){
                        $request->setIsActive(1);

                        $em = $this->getDoctrine()->getManager();
                        $em->persist($request);
                        $em->flush();

                        return $this->redirectToRoute('adminCommunity', ['id' => $id]);
                    }
                }
            }
        }

        return $this->redirectToRoute('viewCommunity', ['id' => $id]);
    }

    /**
     * @Route("/{id}/reject", name="NULLrejectRequestNULL")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function NULLRejectRequestAction($id)
    {
        return $this->redirectToRoute('viewCommunity', ['id' => $id]);
    }

    /**
     * @Route("/{id}/reject/{idRequest}", name="rejectRequest")
     */
    public function rejectRequestAction($id, $idRequest)
    {
        $user = $this->getUser();

        if($user->isIsSuspended() || $user->isIsBlock()){
            return $this->redirectToRoute('logout');
        }

        $community = $this->getDoctrine()->getRepository('AppBundle:Community')->find($id);
        $request = $this->getDoctrine()->getRepository('AppBundle:UserCommunity')->find($idRequest);

        if($community) {
            if ($request) {
                if ($community->getAdmin() == $user && $community == $request->getCommunity()) {
                    if (!$request->getIsDeleted() && !$request->getIsActive()) {
                        $request->setIsDeleted(1);

                        $em = $this->getDoctrine()->getManager();
                        $em->persist($request);
                        $em->flush();
                    }
                }
            }
        }

        return $this->redirectToRoute('viewCommunity', ['id' => $id]);
    }
}