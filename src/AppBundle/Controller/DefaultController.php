<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ForgottedPassword;
use AppBundle\Entity\User;
use AppBundle\Form\ForgottedPasswordType;
use AppBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="index")
     */
    public function indexAction(Request $request)
    {
        if($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_ANONYMOUSLY')){
            $auth = $this->get('security.authentication_utils');

            $user = new User();

            $form = $this->createForm(UserType::class, $user);
            $form->handleRequest($request);

            $form2 = $this->createForm(ForgottedPasswordType::class);
            $form2->handleRequest($request);

            $sendMail = 0;
            $options = 0;

            if(isset($_GET['o'])){
                $options = $_GET['o'];
            }

            if($form->isSubmitted() && $form->isValid()){
                $password = $this->get('security.password_encoder');

                $user->setPassword($password->encodePassword($user, $form->get('password')->get('first')->getData()));
                $user->setIsAdmin(0);
                $user->setSignUpDate(new \DateTime("now"));

                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                return $this->redirectToRoute('login');
            }

            if($form2->isSubmitted()){
                $email = $form2->getData()->getEmail();

                $nUsers = $this->getDoctrine()->getRepository('AppBundle:User')->findBy([
                    'email' => $email,
                    'isBlock' => false,
                    'isSuspended' => false
                ]);

                if(count($nUsers) == 1){
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
                        ->setFrom('tecleadotme@gmail.com')
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
                }else{
                    $sendMail = 2;
                }
            }

            return $this->render('default/index.html.twig', [
                'last_username' => $auth->getLastUsername(),
                'error' => $auth->getLastAuthenticationError(),
                'form' => $form->createView(),
                'formForgottedPassword' => $form2->createView(),
                'sendMail' => $sendMail,
                'option' => $options
            ]);
        }else{
            return $this->render('default/index.html.twig');
        }
    }

    /**
     * @Route("/newPassword", name="newPassword")
     */
    public function newPasswordAction(Request $request)
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
                    ->setFrom('tecleadotme@gmail.com')
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
}
