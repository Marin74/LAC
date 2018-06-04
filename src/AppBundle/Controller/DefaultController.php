<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Query\Expr\Join;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $repoAssociation = $em->getRepository("AppBundle:Association");
        $mainAssociation = null;
        if($this->container->hasParameter("app_name"))
            $mainAssociation = $repoAssociation->findOneByName($this->container->getParameter("app_name"));
        
        $now = new \DateTime();
        
        $query = $em->createQuery(
            'SELECT e
            FROM AppBundle:Event e
            WHERE e.endTime > :now
        	AND e.published = true
            ORDER BY e.startTime ASC'
        )->setParameter('now', $now);

        $tempEvents = $query->getResult();
        $tempArrayEvents = array();
        
        foreach($tempEvents as $event) {
        	
            if($event->getAssociation()->isDisplayed()) {
                
                // Check if the event is long like an exhibition
                $diff = $now->diff($event->getEndTime());
                $diff->format('%R');
                
                $sortDate = $event->getStartTime();
                
                if($event->getStartTime() <= $now) {
                    
                    if($diff->days >= 10) {
                        
                        $sortDate = $event->getEndTime();
                        
                        $sortDate->modify("-7 days");
                    }
                }
                
                $tempArrayEvents[$sortDate->format("Y-m-d H:i:s")."_".$event->getId()] = $event;
            }
        }
        
        ksort($tempArrayEvents);
        
        $nextEvents = array();
        $hasNextEventsWithPlace = false;
        
        foreach($tempArrayEvents as $event) {
            
            $nextEvents[] = $event;
            
            if($event->getPlaceEntity() != null) {
                $hasNextEventsWithPlace = true;
            }
        }
        
        
        // Get random association
        $randomAssociation = null;
        
        $qb = $repoAssociation->createQueryBuilder('a');
        $qb->select("COUNT(a)");
        $qb->where($qb->expr()->eq("a.displayed", ":displayed"))
        ->setParameter("displayed", true);
        
        if($mainAssociation != null) {
            $qb->andWhere(
                $qb->expr()->neq("a.name", ":name")
            )
            ->setParameter("name", $mainAssociation->getName());
        }
        
        $nbAssociations = $qb->getQuery()->getSingleScalarResult();
        
        while($nbAssociations > 1 && $randomAssociation == null) {
            
            $index = rand(0, $nbAssociations-1);
            
            $qb = $repoAssociation->createQueryBuilder('a');
            $qb->where($qb->expr()->eq("a.displayed", ":displayed"))
            ->setParameter("displayed", true);
            
            if($mainAssociation != null) {
                $qb->andWhere(
                    $qb->expr()->neq("a.name", ":name")
                )
                ->setParameter("name", $mainAssociation->getName());
            }
            
            $qb->setFirstResult($index);
            $qb->setMaxResults(1);
            
            $results = $qb->getQuery()->getResult();
            
            if(count($results) > 0) {
                $randomAssociation = $results[0];
            }
        }
        

        return $this->render('AppBundle:Default:index.html.twig', array(
            'mainAssociation'           => $mainAssociation,
            'nextEvents'                => $nextEvents,
            'hasNextEventsWithPlace'    => $hasNextEventsWithPlace,
            'randomAssociation'         => $randomAssociation
        ));
    }

    public function licenseAction()
    {
        $em = $this->getDoctrine()->getManager();
        $repoAssociation = $em->getRepository("AppBundle:Association");
        $mainAssociation = null;
        if($this->container->hasParameter("app_name"))
            $mainAssociation = $repoAssociation->findOneByName($this->container->getParameter("app_name"));
        
        return $this->render('AppBundle:Default:license.html.twig', array(
        	'mainAssociation' => $mainAssociation
        ));
    }

    public function associationAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAssociation = $em->getRepository("AppBundle:Association");
        $association = $repoAssociation->findOneBy(array("id" => $request->get("id"), "displayed" => true));
        $mainAssociation = null;
        if($this->container->hasParameter("app_name"))
            $mainAssociation = $repoAssociation->findOneByName($this->container->getParameter("app_name"));

        return $this->render('AppBundle:Default:association.html.twig', array(
        	'mainAssociation'	=> $mainAssociation,
            'association'		=> $association
        ));
    }

    public function associationsAction()
    {
        $em = $this->getDoctrine()->getManager();
        $repoAssociation = $em->getRepository("AppBundle:Association");
        $associations = $repoAssociation->findBy(array("displayed" => true), array("name" => "ASC"));
        $mainAssociation = null;
        if($this->container->hasParameter("app_name"))
            $mainAssociation = $repoAssociation->findOneByName($this->container->getParameter("app_name"));

        return $this->render('AppBundle:Default:associations.html.twig', array(
        	'mainAssociation' => $mainAssociation,
            'associations' => $associations
        ));
    }

    public function eventAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repoEvent = $em->getRepository("AppBundle:Event");
        $event = $repoEvent->findOneBy(array("id" => $request->get("id"), "published" => true));
        $repoAssociation = $em->getRepository("AppBundle:Association");
        $mainAssociation = null;
        if($this->container->hasParameter("app_name"))
            $mainAssociation = $repoAssociation->findOneByName($this->container->getParameter("app_name"));
        
        if($event != null && !$event->getAssociation()->isDisplayed())
        	$event = null;

        return $this->render('AppBundle:Default:event.html.twig', array(
        	'mainAssociation'	=> $mainAssociation,
            'event'				=> $event
        ));
    }
    
    public function eventsAction()
    {
    	$em = $this->getDoctrine()->getManager();
    	$repoAssociation = $em->getRepository("AppBundle:Association");
    	
    	$query = $em->createQuery(
    		'SELECT e
            FROM AppBundle:Event e
            WHERE e.endTime > :now
    		AND e.published = :published
            ORDER BY e.startTime ASC'
		)
    	->setParameter('now', new \DateTime())
		->setParameter('published', true);
		$tempEvents = $query->getResult();
    	
    	$events = array();
    	
    	foreach($tempEvents as $event) {
    		if($event->getAssociation()->isDisplayed())
    			$events[] = $event;
    	}
    	
    	$mainAssociation = null;
    	if($this->container->hasParameter("app_name"))
    		$mainAssociation = $repoAssociation->findOneByName($this->container->getParameter("app_name"));
    
    	return $this->render('AppBundle:Default:events.html.twig', array(
    			'mainAssociation'	=> $mainAssociation,
    			'events'			=> $events
    		)
    	);
    }
    
    public function quizAction(Request $request)
    {
    	$em = $this->getDoctrine()->getManager();
    	$repoQuizCategory = $em->getRepository("AppBundle:QuizCategory");
    	$quizCategories = $repoQuizCategory->findBy(array(), array("order" => "ASC"));
        $repoAssociation = $em->getRepository("AppBundle:Association");
        $mainAssociation = null;
        if($this->container->hasParameter("app_name"))
            $mainAssociation = $repoAssociation->findOneByName($this->container->getParameter("app_name"));
    
    	return $this->render('AppBundle:Default:quiz.html.twig', array(
    		'mainAssociation' => $mainAssociation,
    		'quizCategories' => $quizCategories
    	));
    }
    
    public function quizResultAction(Request $request)
    {
    	$em = $this->getDoctrine()->getManager();
    	$repoQuizCategory = $em->getRepository("AppBundle:QuizCategory");
    	$repoQuizScore = $em->getRepository("AppBundle:QuizScore");
    	$repoAssociation = $em->getRepository("AppBundle:Association");
    	$input = $request->get("categories");
    	$quizCategories = array();
    	$associationsSelected = array();
    	$associations = array();
        $mainAssociation = null;
        if($this->container->hasParameter("app_name"))
            $mainAssociation = $repoAssociation->findOneByName($this->container->getParameter("app_name"));
    	
    	if(!empty($input)) {
    		$names = explode(",", $input);
    		
    		foreach($names as $name) {

    			$category = $repoQuizCategory->findOneByName($name);
    			
    			if($category != null) {
    				$quizCategories[] = $category;
    			}
    		}
    		
    		foreach($repoAssociation->findByDisplayed(true) as $association) {
    			
    			$totalScore = 0;
    			
    			foreach($quizCategories as $category) {
    				
    				$score = $repoQuizScore->findOneBy(array("quizCategory" => $category, "association" => $association));
    					
    				if($score != null && $score->getScore() > 0) {
    					$totalScore += $score->getScore();
    				}
    			}
    			
    			if($totalScore > 0)
    				$associationsSelected[$association->getName()] = $totalScore;
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
    		'mainAssociation'	=> $mainAssociation,
    		'quizCategories'	=> $quizCategories,
    		'associations'		=> $associations
    	));
    }
    
    public function archivesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repoEvent = $em->getRepository("AppBundle:Event");
        $repoAssociation = $em->getRepository("AppBundle:Association");
        $mainAssociation = null;
        if($this->container->hasParameter("app_name")) {
            $mainAssociation = $repoAssociation->findOneByName($this->container->getParameter("app_name"));
        }
        
        $page = intval($request->get("page"));
        if($page <= 0) {
            $page = 1;
        }
        
        $NB_ITEMS = 10;
        
        $offset = ($page - 1) * $NB_ITEMS;
        
        $now = new \DateTime();
        
        $qb = $repoEvent->createQueryBuilder("e");
        $qb
        ->innerJoin(
            'AppBundle:Association',
            'a',
            Join::WITH,
            $qb->expr()->eq('e.association', 'a')
        )
        ->where($qb->expr()->eq("e.published", ":published"))
        ->andWhere($qb->expr()->eq("a.displayed", ":displayed"))
        ->andWhere($qb->expr()->lte("e.endTime", ":now"))
        ->setParameter("published", true)
        ->setParameter("displayed", true)
        ->setParameter("now", $now)
        ->orderBy("e.startTime", "DESC")
        ->setFirstResult($offset)
        ->setMaxResults($NB_ITEMS)
        ;
        
        $events = $qb->getQuery()->getResult();
        
        $nbDisplayedEvents = $offset + 1 + count($events);
        
        
        // Check if there are events after
        $offset += $NB_ITEMS;
        
        $qb = $repoEvent->createQueryBuilder("e");
        $qb->select('count(e.id)');
        $qb
        ->innerJoin(
            'AppBundle:Association',
            'a',
            Join::WITH,
            $qb->expr()->eq('e.association', 'a')
        )
        ->where($qb->expr()->eq("e.published", ":published"))
        ->andWhere($qb->expr()->eq("a.displayed", ":displayed"))
        ->andWhere($qb->expr()->lte("e.endTime", ":now"))
        ->setParameter("published", true)
        ->setParameter("displayed", true)
        ->setParameter("now", $now)
        ->orderBy("e.endTime", "DESC")
        //->setFirstResult($offset)
        //->setMaxResults(1)
        ;
        
        $nbOldEvents = $qb->getQuery()->getSingleScalarResult();
        
        
        return $this->render('AppBundle:Default:archives.html.twig', array(
            'mainAssociation'   => $mainAssociation,
            'events'            => $events,
            'displayNext'       => ($nbOldEvents > $nbDisplayedEvents),
            'page'              => $page
        ));
    }
    
    public function searchAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAssociation = $em->getRepository("AppBundle:Association");
        $repoEvent = $em->getRepository("AppBundle:Event");
        $mainAssociation = null;
        if($this->container->hasParameter("app_name")) {
            $mainAssociation = $repoAssociation->findOneByName($this->container->getParameter("app_name"));
        }
        
        $q = trim($request->get("q"));
        
        $association = $repoAssociation->findOneBy(array("id" => $request->get("id"), "displayed" => true));
        
        $events = array();
        $associations = array();
        
        /*
        $qb = $repoPlace->createQueryBuilder('p');
        $qb->where(
            $qb->expr()->orX(
                $qb->expr()->like($qb->expr()->concat("p.name", $qb->expr()->concat($qb->expr()->literal('%'), "p.city")), ":name"),
                $qb->expr()->like($qb->expr()->concat("p.city", $qb->expr()->concat($qb->expr()->literal('%'), "p.name")), ":name")
            )
        )
        ->setParameter("name", "%".str_replace(" ", "%", trim($search))."%")
        ->orderBy("p.name", "ASC")
        ->getQuery();
        
        $places = $qb->getQuery()->getResult();
        */
        
        if(!empty($q)) {
            // Get events
            $qb = $repoEvent->createQueryBuilder('e');
            
            $qb
            ->innerJoin(
                'AppBundle:Association',
                'a',
                Join::WITH,
                $qb->expr()->eq('e.association', 'a')
            )
            ->leftJoin("e.placeEntity", "p")
            /*->innerJoin(
                'AppBundle:Place',
                'p',
                Join::WITH,
                $qb->expr()->eq('e.placeEntity', 'a')
            )*/
            ->where(
                $qb->expr()->orX(
                    $qb->expr()->like($qb->expr()->concat("e.name", $qb->expr()->concat($qb->expr()->literal('%'), "a.name")), ":pattern"),// Event       + Association
                    $qb->expr()->like($qb->expr()->concat("a.name", $qb->expr()->concat($qb->expr()->literal('%'), "e.name")), ":pattern"),// Association + Event
                    $qb->expr()->like($qb->expr()->concat("e.name", $qb->expr()->concat($qb->expr()->literal('%'), "p.city")), ":pattern"),// Event       + City
                    $qb->expr()->like($qb->expr()->concat("p.city", $qb->expr()->concat($qb->expr()->literal('%'), "e.name")), ":pattern"),// City        + Event
                    $qb->expr()->like($qb->expr()->concat("p.name", $qb->expr()->concat($qb->expr()->literal('%'), "p.city")), ":pattern"),// Place       + City
                    $qb->expr()->like($qb->expr()->concat("p.city", $qb->expr()->concat($qb->expr()->literal('%'), "p.name")), ":pattern"),// City        + Place
                    $qb->expr()->like("a.city", ":pattern") // Association city
                )
            )
            ->andWhere($qb->expr()->eq("e.published", ":published"))
            ->andWhere($qb->expr()->eq("a.displayed", ":displayed"))
            ->andWhere($qb->expr()->gte("e.endTime", ":now"))
            ->setParameter("pattern", "%".str_replace(" ", "%", $q)."%")
            ->setParameter("published", true)
            ->setParameter("displayed", true)
            ->setParameter('now', new \DateTime())
            ->orderBy("e.startTime", "ASC")
            ->getQuery();
            
            $events = $qb->getQuery()->getResult();
            
            
            
            
            // Get associations
            $qb = $repoAssociation->createQueryBuilder('a');
            $qb
            ->where(
                $qb->expr()->orX(
                    $qb->expr()->like($qb->expr()->concat("a.name", $qb->expr()->concat($qb->expr()->literal('%'), "a.name")), ":pattern"),
                    $qb->expr()->like($qb->expr()->concat("a.name", $qb->expr()->concat($qb->expr()->literal('%'), "a.name")), ":pattern")
                )
            )
            ->andWhere($qb->expr()->eq("a.displayed", ":displayed"))
            ->setParameter("pattern", "%".str_replace(" ", "%", $q)."%")
            ->setParameter("displayed", true)
            ->orderBy("a.name", "ASC")
            ->getQuery();
            
            $associations = $qb->getQuery()->getResult();
        }
        
        return $this->render('AppBundle:Default:search.html.twig', array(
            'mainAssociation'	=> $mainAssociation,
            'q'                 => $q,
            'events'            => $events,
            'associations'      => $associations
        ));
    }
}
