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
            $user = new User();

            $form = $this->createForm(UserType::class, $user);
            $form->handleRequest($request);

            $form2 = $this->createForm(ForgottedPasswordType::class);
            $form2->handleRequest($request);

            $sendMail = 0;

            if($form->isSubmitted() && $form->isValid()){
                $password = $this->get('security.password_encoder');

                $user->setPassword($password->encodePassword($user, $form->get('password')->get('first')->getData()));
                $user->setIsAdmin(0);
                $user->setSignUpDate(new \DateTime("now"));

                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                return $this->redirectToRoute('index');
            }

            if($form2->isSubmitted() && $form2->isValid()){
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

                    return $this->redirect('./#newPassword');
                }else{
                    $sendMail = 2;
                }
            }

            return $this->render('default/index.html.twig', [
                'form' => $form->createView(),
                'formForgottedPassword' => $form2->createView(),
                'sendMail' => $sendMail
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

            return $this->redirectToRoute('login');
        }

        return $this->render('userBase.html.twig');
    }
}
