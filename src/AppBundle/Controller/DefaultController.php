<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $repoAssociation = $em->getRepository("AppBundle:Association");
        $mainAssociation = null;
        if($this->container->hasParameter("app_name"))
            $mainAssociation = $repoAssociation->findOneByName($this->container->getParameter("app_name"));

        $query = $em->createQuery(
            'SELECT e
            FROM AppBundle:Event e
            WHERE e.endTime > :now
            ORDER BY e.startTime ASC'
        )->setParameter('now', new \DateTime());

        $nextEvents = $query->getResult();

        return $this->render('AppBundle:Default:index.html.twig', array(
            'mainAssociation' => $mainAssociation,
            'nextEvents' => $nextEvents,
        ));
    }

    public function licenseAction()
    {
        return $this->render('AppBundle:Default:license.html.twig');
    }

    public function associationAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAssociation = $em->getRepository("AppBundle:Association");
        $association = $repoAssociation->find($request->get("id"));

        return $this->render('AppBundle:Default:association.html.twig', array(
            'association' => $association,
        ));
    }

    public function associationsAction()
    {
        $em = $this->getDoctrine()->getManager();
        $repoAssociation = $em->getRepository("AppBundle:Association");
        $associations = $repoAssociation->findBy(array(), array("name" => "ASC"));

        return $this->render('AppBundle:Default:associations.html.twig', array(
            'associations' => $associations,
        ));
    }

    public function eventAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repoEvent = $em->getRepository("AppBundle:Event");
        $event = $repoEvent->find($request->get("id"));

        return $this->render('AppBundle:Default:event.html.twig', array(
            'event' => $event,
        ));
    }
}
