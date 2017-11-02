<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/admin")
 */
class AdminController extends Controller
{
    /**
     * @Route("/", name="adminPanelIndex")
     */
    public function adminPanelAction()
    {
        $user = $this->getUser();

        if($user->getIsAdmin()){
            $messages = $this->getDoctrine()->getRepository('AppBundle:Message')->findAll();

            $users = $this->getDoctrine()->getRepository('AppBundle:User')->findAll();
        }else{
            return $this->redirectToRoute('index');
        }

        return $this->render('Admin/index.html.twig', [
            'user' => $user,
            'messages' => $messages,
            'users' => $users
        ]);
    }
}