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

        if ($user->getIsAdmin()) {
            $messages = $this->getDoctrine()->getRepository('AppBundle:Message')->findAll();

            $users = $this->getDoctrine()->getRepository('AppBundle:User')->findAll();
        } else {
            return $this->redirectToRoute('index');
        }

        return $this->render('Admin/index.html.twig', [
            'user' => $user,
            'messages' => $messages,
            'users' => $users
        ]);
    }

    /**
     * @Route("/users", name="usersList")
     */
    public function usersListAction()
    {
        $user = $this->getUser();

        if (!$user->getIsAdmin()) {
            return $this->redirectToRoute('index');
        } else {
            $users = $this->getDoctrine()->getRepository('AppBundle:User')->findAll();
        }

        return $this->render('Admin/users.html.twig', [
            'user' => $user,
            'users' => $users
        ]);
    }

    /**
     * @Route("/user", name="userNULL")
     */
    public function userNULL()
    {
        return $this->redirectToRoute('usersList');
    }

    /**
     * @Route("/user/{id}", name="userDetailsAdmin")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function userDetailsAdminAction($id)
    {
        $user = $this->getUser();

        if (!$user->getIsAdmin()) {
            return $this->redirectToRoute('index');
        } else {
            $userInfo = $this->getDoctrine()->getRepository('AppBundle:User')->find($id);

            if (!$userInfo) {
                return $this->redirectToRoute('usersList');
            }
        }

        return $this->render('Admin/Users/user.html.twig', [
            'user' => $user,
            'userInfo' => $userInfo
        ]);
    }

    /**
     * @Route("/user/{id}/edit", name="editUserAdmin")
     */
    public function editUserAdmin(Request $request, $id)
    {
        $user = $this->getUser();

        if (!$user->getIsAdmin()) {
            return $this->redirectToRoute('index');
        }

        return $this->render('Admin/Users/editUser.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * @Route("/user/{id}/suspend", name="suspendUser")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function suspendUserAction($id)
    {
        $user = $this->getUser();

        if (!$user->getIsAdmin()) {
            return $this->redirectToRoute('index');
        } else {
            $userInfo = $this->getDoctrine()->getRepository('AppBundle:User')->find($id);

            if ($userInfo) {
                if(!$userInfo->isIsSuspended()){
                    $userInfo->setIsSuspended(1);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($userInfo);
                    $em->flush();
                }
            }

            return $this->redirectToRoute('usersList');
        }
    }

    /**
     * @Route("/user/{id}/activate", name="activateUser")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function activateUserAction($id)
    {
        $user = $this->getUser();

        if (!$user->getIsAdmin()) {
            return $this->redirectToRoute('index');
        } else {
            $userInfo = $this->getDoctrine()->getRepository('AppBundle:User')->find($id);

            if ($userInfo) {
                if($userInfo->isIsSuspended()){
                    $userInfo->setIsSuspended(0);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($userInfo);
                    $em->flush();
                }
            }

            return $this->redirectToRoute('usersList');
        }
    }

    /**
     * @Route("/user/{id}/block", name="blockUser")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function blockUserAction($id)
    {
        $user = $this->getUser();

        if (!$user->getIsAdmin()) {
            return $this->redirectToRoute('index');
        } else {
            $userInfo = $this->getDoctrine()->getRepository('AppBundle:User')->find($id);

            if ($userInfo) {
                if(!$userInfo->isIsBlock()){
                    $userInfo->setIsBlock(1);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($userInfo);
                    $em->flush();
                }
            }

            return $this->redirectToRoute('usersList');
        }
    }

    /**
     * @Route("/user/{id}/unblock", name="unblockUser")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function unblockUserAction($id)
    {
        $user = $this->getUser();

        if (!$user->getIsAdmin()) {
            return $this->redirectToRoute('index');
        } else {
            $userInfo = $this->getDoctrine()->getRepository('AppBundle:User')->find($id);

            if ($userInfo) {
                if($userInfo->isIsBlock()){
                    $userInfo->setIsBlock(0);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($userInfo);
                    $em->flush();
                }
            }

            return $this->redirectToRoute('usersList');
        }
    }
}