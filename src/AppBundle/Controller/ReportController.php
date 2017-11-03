<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Notification;
use AppBundle\Entity\ReportCommunity;
use AppBundle\Form\ReportType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/report")
 */
class ReportController extends Controller
{
    /**
     * @Route("/", name="reportList")
     */
    public function reportListAction()
    {
        $user = $this->getUser();

        if($user->isIsSuspended() || $user->isIsBlock()){
            return $this->redirectToRoute('logout');
        }

        $reports = $this->getDoctrine()->getRepository('AppBundle:ReportCommunity')->findBy(
            array(
                'admin' => $user,
                'isDeleted' => false,
                'isActive' => true,
                'isClosed' => false
            )
        );

        $closed = $this->getDoctrine()->getRepository('AppBundle:ReportCommunity')->findBy(
            array(
                'admin' => $user,
                'isDeleted' => false,
                'isActive' => false,
                'isClosed' => true
            )
        );

        return $this->render('Report/reportList.html.twig', [
            'user' => $user,
            'reports' => $reports,
            'closed' => $closed
        ]);
    }

    /**
     * @Route("/{id}", name="viewReport")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function viewReportAction($id)
    {
        $user = $this->getUser();

        if($user->isIsSuspended() || $user->isIsBlock()){
            return $this->redirectToRoute('logout');
        }

        $report = $this->getDoctrine()->getRepository('AppBundle:ReportCommunity')->find($id);

        if($report){
            if($report->getAdmin() == $user && !$report->isDeleted()){

                return $this->render('Report/report.html.twig', [
                    'user' => $user,
                    'rep' => $report
                ]);
            }
        }

        return $this->redirectToRoute('reportList');
    }

    /**
     * @Route("/{id}/close", name="closeReport")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function closeReportAction($id)
    {
        $user = $this->getUser();

        if($user->isIsSuspended() || $user->isIsBlock()){
            return $this->redirectToRoute('logout');
        }

        $report = $this->getDoctrine()->getRepository('AppBundle:ReportCommunity')->find($id);

        if($report){
            if($report->getAdmin() == $user && !$report->isDeleted()){
                if(!$report->getisClosed()){
                    $report->setIsActive(0);
                    $report->setIsClosed(1);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($report);
                    $em->flush();
                }

                return $this->redirectToRoute('viewReport', ['id' => $id]);
            }
        }

        return $this->redirectToRoute('reportList');
    }

    /**
     * @Route("/{id}/new", name="newMessageReport")
     */
    public function newReportAction($id, Request $request)
    {
        $user = $this->getUser();

        if($user->isIsSuspended() || $user->isIsBlock()){
            return $this->redirectToRoute('logout');
        }

        $message = $this->getDoctrine()->getRepository('AppBundle:Message')->find($id);
        $error = 0;

        if($message){
            $report = new ReportCommunity();

            $form = $this->createForm(ReportType::class, $report);
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){
                $em = $this->getDoctrine()->getManager();

                $isReported = $this->getDoctrine()->getRepository('AppBundle:ReportCommunity')->findOneBy(
                    array(
                        'messageReported' => $message,
                        'informer' => $user,
                        'isDeleted' => false,
                        'isActive' => true,
                        'isClosed' => false
                    )
                );

                if(!$isReported) {
                    $report->setInformer($user);
                    $report->setMessageReported($message);
                    if ($message->getCommunity()->getAdmin()) {
                        $report->setAdmin($message->getCommunity()->getAdmin());
                    } else {
                        $report->setAdmin(null);
                    }
                    $report->setDate(new \DateTime("now"));
                    $report->setIsClosed(0);
                    $report->setIsActive(1);
                    $report->setIsDeleted(0);


                    $em->persist($report);
                    $em->flush();

                    $notification = new Notification();
                    $notification->setType('report');
                    if ($message->getCommunity()->getAdmin()) {
                        $notification->setUser($message->getCommunity()->getAdmin());
                    } else {
                        $notification->setUser(null);
                    }
                    $notification->setDate(new \DateTime("now"));
                    $notification->setDescription("Has recibido un nuevo reporte de tu comunidad.");

                    $em->persist($notification);
                    $em->flush();

                    $error = 2;
                }else{
                    $error = 1;
                }

                $nReports = $this->getDoctrine()->getRepository('AppBundle:ReportCommunity')->findBy(
                    array(
                        'isDeleted' => false,
                        'isClosed' => false,
                        'isActive' => true,
                        'messageReported' => $message
                    )
                );

                if(count($nReports) >= 10){
                    $message->setIsBlock(1);

                    $em->persist($message);
                    $em->flush();
                }
            }

            return $this->render('Report/newReport.html.twig', [
                'user' => $user,
                'message' => $message,
                'form' => $form->createView(),
                'error' => $error
            ]);
        }else{
            return $this->redirectToRoute('messageDetails', ['id' => $id]);
        }
    }
}