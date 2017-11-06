<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Community;
use AppBundle\Entity\ForgottedPassword;
use AppBundle\Entity\Mail;
use AppBundle\Entity\MailAnon;
use AppBundle\Entity\User;
use AppBundle\Entity\UserCommunity;
use AppBundle\Form\AnonContactType;
use AppBundle\Form\CommunityType;
use AppBundle\Form\ForgottedPasswordType;
use AppBundle\Form\MailType;
use AppBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\DateTime;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="index")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        if($this->isGranted('ROLE_USER')){
            $user = $this->getUser();

            if($user->isIsSuspended() || $user->isIsBlock()){
                return $this->redirectToRoute('logout');
            }

            $messages = [];
            $replies = [];

            $communities = $this->getDoctrine()->getRepository('AppBundle:UserCommunity')->findBy(
                array(
                    'user' => $user,
                    'isActive' => true,
                    'isDeleted' => false
                )
            );

            foreach($communities as $community){
                $msgs = $this->getDoctrine()->getRepository('AppBundle:Message')->findBy(
                    array(
                        'community' => $community->getCommunity(),
                        'isActive' => true,
                        'isDeleted' => false,
                        'isBlock' => false,
                        'isReply' => false
                    ),
                    array(
                        'date' => 'DESC'
                    ), 15
                );

                $r = $this->getDoctrine()->getRepository('AppBundle:Message')->findBy(
                    array(
                        'community' => $community->getCommunity(),
                        'isActive' => true,
                        'isDeleted' => false,
                        'isBlock' => false,
                        'isReply' => true
                    ),
                    array(
                        'date' => 'DESC'
                    ), 15
                );

                foreach($msgs as $msg){
                    array_push($messages, $msg);
                }

                foreach($r as $reply){
                    array_push($replies, $reply);
                }
            }

            // Order Messages
            $dates = [];
            $messages_ordered = [];

            for($i=0; $i<count($messages); $i++){
                array_push($dates, $messages[$i]->getDate());
            }

            $x = 0;
            $count = count($messages);

            while($x != $count){
                $max = max($dates);

                for($i=0; $i<count($messages); $i++){
                    if(strtotime(date_format($messages[$i]->getDate(), 'd-m-Y H:i:s')) == strtotime(date_format($max, 'd-m-Y H:i:s'))){
                        array_push($messages_ordered, $messages[$i]);
                        unset($messages[$i]);
                        unset($dates[$i]);

                        $messages = array_values($messages);
                        $dates = array_values($dates);
                    }
                }

                $x++;
            }

            // Order Replies
            $dates = [];
            $replies_ordered = [];

            for($i=0; $i<count($replies); $i++){
                array_push($dates, $replies[$i]->getDate());
            }

            $x = 0;
            $count = count($replies);

            while($x != $count){
                $max = max($dates);

                for($i=0; $i<count($replies); $i++){
                    if(strtotime(date_format($replies[$i]->getDate(), 'd-m-Y H:i:s')) == strtotime(date_format($max, 'd-m-Y H:i:s'))){
                        array_push($replies_ordered, $replies[$i]);
                        unset($replies[$i]);
                        unset($dates[$i]);

                        $replies = array_values($replies);
                        $dates = array_values($dates);
                    }
                }

                $x++;
            }

            return $this->render('default/index.html.twig', [
                'user' => $user,
                'messages' => $messages_ordered,
                'replies' => $replies_ordered
            ]);
        }else{
            $auth = $this->get('security.authentication_utils');

            $user = new User();
            $anonMail = new MailAnon();

            if(isset($_GET['r'])){
                $user->setReferred($_GET['r']);
            }

            $form = $this->createForm(UserType::class, $user);
            $form->handleRequest($request);

            $form2 = $this->createForm(ForgottedPasswordType::class);
            $form2->handleRequest($request);

            $form3 = $this->createForm(AnonContactType::class, $anonMail);
            $form3->handleRequest($request);

            $sendMail = 0;
            $options = 0;
            $status = 0;

            if (isset($_GET['o'])) {
                $options = $_GET['o'];
            }

            if ($form->isSubmitted() && $form->isValid()) {
                if(isset($_GET['r'])){
                    $userReferred = $this->getDoctrine()->getRepository('AppBundle:User')->findOneBy([
                        'username' => $_GET['r']
                    ]);

                    if($userReferred){
                        $user->setReferred($userReferred);
                    }else{
                        $user->setReferred(null);
                    }
                }else{
                    if($form->get('referred')->getData()){
                        $userReferred = $this->getDoctrine()->getRepository('AppBundle:User')->findOneBy([
                            'username' => $form->get('referred')->getData()
                        ]);

                        if($userReferred){
                            $user->setReferred($userReferred);
                        }else{
                            $status = 404;
                        }
                    }
                }

                $password = $this->get('security.password_encoder');

                $user->setPassword($password->encodePassword($user, $form->get('password')->get('first')->getData()));
                $user->setIsAdmin(0);
                $user->setSignUpDate(new \DateTime("now"));

                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                return $this->redirectToRoute('login');
            }

            if ($form2->isSubmitted()) {
                $email = $form2->getData()->getEmail();

                $nUsers = $this->getDoctrine()->getRepository('AppBundle:User')->findBy([
                    'email' => $email,
                    'isBlock' => false,
                    'isSuspended' => false
                ]);

                if (count($nUsers) == 1) {
                    $code = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10);

                    $fp = new ForgottedPassword();
                    $fp->setDate(new \DateTime("now"));
                    $fp->setIsActive(1);
                    $fp->setCode($code);
                    $fp->setEmail($email);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($fp);
                    $em->flush();

                    $message = \Swift_Message::newInstance(null)
                        ->setSubject('Reestablece tu contraseña')
                        ->setFrom('admin@teclea.me')
                        ->setTo($email)
                        ->setBody(
                            $this->renderView(
                                'mails/forgottedPassword.html.twig', [
                                    'code' => $code,
                                    'email' => $email
                                ]
                            ),
                            'text/html'
                        );

                    $this->get('mailer')->send($message);

                    $sendMail = 1;
                } else {
                    $sendMail = 2;
                }
            }

            if($form3->isSubmitted() && $form3->isValid()){
                $anonMail->setIP($this->get('request_stack')->getCurrentRequest()->getClientIp());
                $anonMail->setDate(new \DateTime("now"));

                $sendMail = 5;

                $em = $this->getDoctrine()->getManager();
                $em->persist($anonMail);
                $em->flush();
            }

            return $this->render('default/index.html.twig', [
                'last_username' => $auth->getLastUsername(),
                'error' => $auth->getLastAuthenticationError(),
                'form' => $form->createView(),
                'formForgottedPassword' => $form2->createView(),
                'contactForm' => $form3->createView(),
                'sendMail' => $sendMail,
                'option' => $options,
                'status' => $status
            ]);
        }
    }

    /**
     * @Route("/newPassword", name="newPassword")
     */
    public function newPasswordAction()
    {
        $pass = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10);

        if(isset($_GET['code']) && isset($_GET['email'])){
            $code = $_GET['code'];
            $email = $_GET['email'];

            $check = $this->getDoctrine()->getRepository('AppBundle:ForgottedPassword')->findBy([
                'code' => $code,
                'email' => $email,
                'isActive' => true
            ]);

            if(count($check) == 1){
                $check[0]->setIsActive(0);

                $em = $this->getDoctrine()->getManager();
                $em->persist($check[0]);
                $em->flush();

                $m = $this->getDoctrine()->getRepository('AppBundle:ForgottedPassword')->findBy([
                    'email' => $email,
                    'isActive' => true
                ]);

                if(count($m) > 0){
                    for($i=0; $i<count($m); $i++){
                        $m[$i]->setIsActive(0);

                        $em->persist($m[$i]);
                        $em->flush();
                    }
                }

                $user = $this->getDoctrine()->getRepository('AppBundle:User')->findBy([
                    'email' => $email
                ])[0];

                $password = $this->get('security.password_encoder');
                $user->setPassword($password->encodePassword($user, $pass));

                $em->persist($user);
                $em->flush();

                $message = \Swift_Message::newInstance(null)
                    ->setSubject('Nueva contraseña')
                    ->setFrom('admin@teclea.me')
                    ->setTo($user->getEmail())
                    ->setBody(
                        $this->renderView(
                            'mails/newPassword.html.twig', [
                                'pass' => $pass
                            ]
                        ),
                        'text/html'
                    );

                $this->get('mailer')->send($message);

                return $this->redirect('./#loginPasswordChanged');
            }else{
                return $this->redirect('./#invalidCode');
            }
        }else{
            return $this->redirectToRoute('index');
        }
    }

    /**
     * @Route("/new", name="newCommunity")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newCommunityAction(Request $request)
    {
        $user = $this->getUser();

        if($user->isIsSuspended() || $user->isIsBlock()){
            return $this->redirectToRoute('logout');
        }

        $community = new Community();
        $form = $this->createForm(CommunityType::class, $community);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $community->setCommunityCreator($user);
            $community->setAdmin($user);
            $community->setCreationDate(new \DateTime("now"));
            $community->setIsBlock(0);
            $community->setIsSuspended(0);
            $community->setIsDeleted(0);

            $em = $this->getDoctrine()->getManager();
            $em->persist($community);
            $em->flush();

            $lastCommunity = $this->getDoctrine()->getRepository('AppBundle:Community')->findBy([
                'communityCreator' => $user
            ],[
                'creationDate' => 'DESC'
            ])[0];

            $newRelation = new UserCommunity();
            $newRelation->setCommunity($lastCommunity);
            $newRelation->setUser($user);
            $newRelation->setDate(new \DateTime("now"));
            $newRelation->setIsDeleted(0);
            $newRelation->setIsActive(1);

            $em->persist($newRelation);
            $em->flush();

            return $this->redirectToRoute('viewCommunity', ['id'=>$lastCommunity->getId()]);
        }

        return $this->render('Community/newCommunity.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/searchCommunity", name="searchCommunity")
     */
    public function searchCommunityAction()
    {
        $user = $this->getUser();
        $string = "";
        $status = 0;
        $communities = [];

        $joined = $this->getDoctrine()->getRepository('AppBundle:UserCommunity')->findBy(
            array(
                'user' => $user,
                'isActive' => true,
                'isDeleted' => false
            )
        );

        if(isset($_GET['s'])){
            $status = 1;
            $string = $_GET['s'];

            if(strlen($string) == 0){
                return $this->redirectToRoute('searchCommunity');
            }else{
                $communities = $this->getDoctrine()->getManager()->createQuery(
                    'SELECT p FROM AppBundle:Community p WHERE p.name like :string AND p.isBlock = FALSE AND p.isDeleted = FALSE AND p.isSuspended = FALSE'
                )->setParameter('string', '%' . $string . '%')->getResult();
            }
        }

        return $this->render('default/search.html.twig', [
            'user' => $user,
            'status' => $status,
            'string' => $string,
            'communities' => $communities,
            'joined' => $joined
        ]);
    }

    /**
     * @Route("/help", name="help")
     */
    public function helpAction(Request $request)
    {
        $user = $this->getUser();

        if($user->isIsSuspended() || $user->isIsBlock()){
            return $this->redirectToRoute('logout');
        }

        $error = 0;

        $mail = new Mail();

        $form = $this->createForm(MailType::class, $mail);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $mail->setDate(new \DateTime("now"));
            $mail->setIP($this->get('request_stack')->getCurrentRequest()->getClientIp());
            $mail->setUser($user);
            $mail->setEmail($user->getEMail());

            $em = $this->getDoctrine()->getManager();
            $em->persist($mail);
            $em->flush();

            $error = 1;
        }

        return $this->render('default/help.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'error' => $error
        ]);
    }
}
