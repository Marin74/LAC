<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repoEvent = $em->getRepository("AppBundle:Event");

        $query = $em->createQuery(
            'SELECT e
            FROM AppBundle:Event e
            WHERE e.endTime > :now
            ORDER BY e.startTime ASC'
        )->setParameter('now', new \DateTime());

        $nextEvents = $query->getResult();

        return $this->render('AppBundle:Default:index.html.twig', array(
            'nextEvents' => $nextEvents,
        ));
    }
}
