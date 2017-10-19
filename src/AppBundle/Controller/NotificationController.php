<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/notifications")
 */
class NotificationController extends Controller
{
    /**
     * @Route("/", name="notifications")
     */
    public function notificationsAction(Request $request)
    {
        $user = $this->getUser();

        $notifications = $this->getDoctrine()->getRepository('AppBundle:Notification')->findBy(
            array(
                'user' => $user
            )
        );

        return $this->render('default/notifications.html.twig', [
            'user' => $user,
            'notification' => $notifications
        ]);
    }

    /**
     * @Route("/delete", name="deleteAllNotifications")
     */
    public function deleteAllAction(Request $request)
    {
        $user = $this->getUser();

        $notifications = $this->getDoctrine()->getRepository('AppBundle:Notification')->findBy(
            array(
                'user' => $user
            )
        );

        if($notifications){
            $em = $this->getDoctrine()->getManager();

            for($i=0; $i<count($notifications); $i++){
                $em->remove($notifications[$i]);
                $em->flush();
            }

            return $this->redirectToRoute('notifications');
        }

        return $this->redirectToRoute('notifications');
    }

    /**
     * @Route("/delete/{id}", name="deleteNotification")
     */
    public function deleteNotificationAction(Request $request, $id)
    {
        $user = $this->getUser();

        $notification = $this->getDoctrine()->getRepository('AppBundle:Notification')->findBy(
            array(
                'user' => $user,
                'id' => $id
            )
        );

        if(count($notification) == 1){
            $em = $this->getDoctrine()->getManager();
            $em->remove($notification[0]);
            $em->flush();

            return $this->redirectToRoute('notifications');
        }

        return $this->redirectToRoute('notifications');
    }
}