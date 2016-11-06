<?php

namespace AppBundle\Controller;

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
        	AND e.published = true
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
        $event = $repoEvent->findOneBy(array("id" => $request->get("id"), "published" => true));

        return $this->render('AppBundle:Default:event.html.twig', array(
            'event' => $event,
        ));
    }
    
    public function quizAction(Request $request)
    {
    	$em = $this->getDoctrine()->getManager();
    	$repoQuizCategory = $em->getRepository("AppBundle:QuizCategory");
    	$quizCategories = $repoQuizCategory->findBy(array(), array("order" => "ASC"));
    
    	return $this->render('AppBundle:Default:quiz.html.twig', array(
    		'quizCategories' => $quizCategories,
    	));
    }
    
    public function quizResultAction(Request $request)
    {
    	$em = $this->getDoctrine()->getManager();
    	$repoQuizCategory = $em->getRepository("AppBundle:QuizCategory");
    	$repoQuizScores = $em->getRepository("AppBundle:QuizScore");
    	$repoAssociation = $em->getRepository("AppBundle:Association");
    	$input = $request->get("categories");
    	$quizCategories = array();
    	$quizCategoriesNames = array();
    	$associationsSelected = array();
    	$associations = array();
    	
    	if(!empty($input)) {
    		$names = explode(",", $input);
    		
    		foreach($names as $name) {

    			$category = $repoQuizCategory->findOneByName($name);
    			
    			if($category != null) {
    				$quizCategories[] = $category;
    				$quizCategoriesNames[] = $category->getName();
    				
    				$scores = $repoQuizScores->findBy(array("quizCategory" => $category));
    				
    				foreach($scores as $score) {
    					
    					if($score->getScore() > 0) {
    						
    						// Add the association if it's not already in the list and add the score
    						if(array_key_exists($score->getAssociation()->getName(), $associationsSelected))
    							$associationsSelected[$score->getAssociation()->getName()] += $score->getScore();
    						else {
    							$associationsSelected[$score->getAssociation()->getName()] = $score->getScore();
    						}
    					}
    				}
    			}
    		}
    	}
    	
    	//die(var_dump($associationsSelected));
    	arsort($associationsSelected);
    	
    	foreach($associationsSelected as $associationSelected => $value) {
    		
    		$association = $repoAssociation->findOneByName($associationSelected);
    		
    		if($association != null)
    			$associations[] = $association;
    	}
    
    	return $this->render('AppBundle:Default:quiz_result.html.twig', array(
    		'quizCategories'		=> $quizCategories,
    		'quizCategoriesNames'	=> $quizCategoriesNames,
    		'associations'			=> $associations
    	));
    }
}
