<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
        $report = $this->getDoctrine()->getRepository('AppBundle:ReportCommunity')->find($id);

        if($report){
            if($report->getAdmin() == $user && !$report->isDeleted()){

                return $this->render('Report/report.html.twig', [
                    'user' => $user,
                    'report' => $report
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
}