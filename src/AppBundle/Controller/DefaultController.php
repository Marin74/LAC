<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\Query\Expr\Join;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $repoEvent = $em->getRepository("AppBundle:Event");
        $repoAssociation = $em->getRepository("AppBundle:Association");
        $mainAssociation = null;
        if($this->container->hasParameter("app_name")) {
            $mainAssociation = $repoAssociation->findOneByName($this->getParameter("app_name"));
        }
        
        $now = new \DateTime();
        
        /*$query = $em->createQuery(
            'SELECT e
            FROM AppBundle:Event e
            WHERE e.endTime > :now
        	AND e.published = true
            ORDER BY e.startTime ASC'
        )->setParameter('now', $now);*/
        
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
        ->andWhere($qb->expr()->gte("e.endTime", ":now"))
        ->setParameter("published", true)
        ->setParameter("displayed", true)
        ->setParameter("now", $now)
        ->orderBy("e.startTime", "ASC")
        ;

        $tempEvents = $qb->getQuery()->getResult();
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
        
        foreach($tempArrayEvents as $event) {
            
            $nextEvents[] = $event;
        }
        
        $tempWorkshop = $repoAssociation->findOneBy(array("displayed" => true, "isWorkshop" => true));
        
        $workshopExists = $tempWorkshop != null;
        
        return $this->render('AppBundle:Default:index.html.twig', array(
            'mainAssociation'   => $mainAssociation,
            'nextEvents'        => $nextEvents,
            'workshopExists'    => $workshopExists
        ));
    }

    public function associationAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAssociation = $em->getRepository("AppBundle:Association");
        $association = $repoAssociation->findOneBy(array("id" => $request->get("id"), "displayed" => true, "isWorkshop" => false));
        $mainAssociation = null;
        if($this->container->hasParameter("app_name")) {
            $mainAssociation = $repoAssociation->findOneByName($this->getParameter("app_name"));
        }

        return $this->render('AppBundle:Default:association.html.twig', array(
        	'mainAssociation'	=> $mainAssociation,
            'association'		=> $association
        ));
    }

    public function associationsAction()
    {
        $em = $this->getDoctrine()->getManager();
        $repoAssociation = $em->getRepository("AppBundle:Association");
        $associations = $repoAssociation->findBy(array("displayed" => true, "isWorkshop" => false), array("name" => "ASC"));
        $mainAssociation = null;
        if($this->container->hasParameter("app_name")) {
            $mainAssociation = $repoAssociation->findOneByName($this->getParameter("app_name"));
        }

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
        if($this->container->hasParameter("app_name")) {
            $mainAssociation = $repoAssociation->findOneByName($this->getParameter("app_name"));
        }
        
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
        $repoEvent = $em->getRepository("AppBundle:Event");
    	$repoAssociation = $em->getRepository("AppBundle:Association");
    	
    	/*$query = $em->createQuery(
    		'SELECT e
            FROM AppBundle:Event e
            WHERE e.endTime > :now
    		AND e.published = :published
            ORDER BY e.startTime ASC'
		)
    	->setParameter('now', new \DateTime())
		->setParameter('published', true);*/
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
	    ->andWhere($qb->expr()->gte("e.endTime", ":now"))
	    ->setParameter("published", true)
	    ->setParameter("displayed", true)
	    ->setParameter("now", new \DateTime())
	    ->orderBy("e.startTime", "ASC")
	    ;
    	
	    $tempEvents = $qb->getQuery()->getResult();
    	
    	$events = array();
    	
    	foreach($tempEvents as $event) {
    		if($event->getAssociation()->isDisplayed())
    			$events[] = $event;
    	}
    	
    	$mainAssociation = null;
    	if($this->container->hasParameter("app_name")) {
    		$mainAssociation = $repoAssociation->findOneByName($this->getParameter("app_name"));
    	}
    
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
        if($this->container->hasParameter("app_name")) {
            $mainAssociation = $repoAssociation->findOneByName($this->getParameter("app_name"));
        }
    
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
        if($this->container->hasParameter("app_name")) {
            $mainAssociation = $repoAssociation->findOneByName($this->getParameter("app_name"));
        }
    	
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
            $mainAssociation = $repoAssociation->findOneByName($this->getParameter("app_name"));
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
        ->andWhere($qb->expr()->lte("e.endTime", ":now"))
        ->setParameter("published", true)
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
        ->andWhere($qb->expr()->lte("e.endTime", ":now"))
        ->setParameter("published", true)
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
            $mainAssociation = $repoAssociation->findOneByName($this->getParameter("app_name"));
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
            ->andWhere($qb->expr()->eq("a.isWorkshop", ":isWorkshop"))
            ->setParameter("pattern", "%".str_replace(" ", "%", $q)."%")
            ->setParameter("displayed", true)
            ->setParameter("isWorkshop", false)
            ->orderBy("a.name", "ASC")
            ->getQuery();
            
            $associations = $qb->getQuery()->getResult();
            
            
            
            
            // Get workshops
            $qb = $repoAssociation->createQueryBuilder('a');
            $qb
            ->where(
                $qb->expr()->orX(
                    $qb->expr()->like($qb->expr()->concat("a.name", $qb->expr()->concat($qb->expr()->literal('%'), "a.name")), ":pattern"),
                    $qb->expr()->like($qb->expr()->concat("a.name", $qb->expr()->concat($qb->expr()->literal('%'), "a.name")), ":pattern")
                )
            )
            ->andWhere($qb->expr()->eq("a.displayed", ":displayed"))
            ->andWhere($qb->expr()->eq("a.isWorkshop", ":isWorkshop"))
            ->setParameter("pattern", "%".str_replace(" ", "%", $q)."%")
            ->setParameter("displayed", true)
            ->setParameter("isWorkshop", true)
            ->orderBy("a.name", "ASC")
            ->getQuery();
            
            $workshops = $qb->getQuery()->getResult();
        }
        
        return $this->render('AppBundle:Default:search.html.twig', array(
            'mainAssociation'	=> $mainAssociation,
            'q'                 => $q,
            'events'            => $events,
            'associations'      => $associations,
            'workshops'         => $workshops
        ));
    }
    
    public function icsAction(Request $request) {
        
        // Library doc: https://packagist.org/packages/bomo/ical-bundle?q=&p=0
        
        $em = $this->getDoctrine()->getManager();
        $repoEvent = $em->getRepository("AppBundle:Event");
        $event = $repoEvent->findOneBy(array("id" => $request->get("id"), "published" => true));
        
        if($event != null) {
            
            $timezone = $this->getParameter("time_zone");
            
            $provider = $this->get('bomo_ical.ics_provider');
            
            $tz = $provider->createTimezone();
            $tz
            ->setTzid($timezone)
            ->setProperty('X-LIC-LOCATION', $tz->getTzid())
            ;
            
            $cal = $provider->createCalendar($tz);
            
            $calEvent = $cal->newEvent();
            $calEvent
            ->setStartDate($event->getStartTime())
            ->setEndDate($event->getEndTime())
            ->setName($event->getName())
            ->setDescription($event->getDescription())
            ->setOrganizer($event->getAssociation()->getName())
            ;
            
            // Location
            if($event->getPlaceEntity() != null) {
                
                $place = $event->getPlaceEntity();
                
                $location = $place->getName();
                
                if(!empty($place->getStreet())) {
                    $location .= ", " . $place->getStreet();
                }
                
                if(!empty($place->getStreet2())) {
                    $location .= ", " . $place->getStreet2();
                }
                
                if(!empty($place->getZipCode())) {
                    $location .= ", " . $place->getZipCode();
                }
                
                if(!empty($place->getCity())) {
                    
                    if(empty($place->getZipCode())) {
                        $location .= ",";
                    }
                    
                    $location .= " " . $place->getCity();
                }
                
                $calEvent->setLocation($location);
            }
            
            $alarm = $calEvent->newAlarm();
            $alarm
            ->setAction('DISPLAY')
            ->setDescription($calEvent->getProperty('description'))
            ->setTrigger('-PT1H')//Dateinterval string format
            ;
            
            // All Day event
            /*$event = $cal->newEvent();
            $event
            ->isAllDayEvent()
            ->setStartDate($datetime)
            ->setEndDate($datetime->modify('+10 days'))
            ->setName('All day event')
            ->setDescription('All day visualisation')
            ;*/
            
            $calStr = $cal->returnCalendar();
            
            return new Response(
                $calStr,
                200,
                array(
                    'Content-Type' => 'text/calendar; charset=utf-8',
                    'Content-Disposition' => 'attachment; filename="calendar.ics"',
                )
            );
        }
        
        die("null");
    }
    
    public function workshopAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAssociation = $em->getRepository("AppBundle:Association");
        $association = $repoAssociation->findOneBy(array("id" => $request->get("id"), "displayed" => true, "isWorkshop" => true));
        $mainAssociation = null;
        if($this->container->hasParameter("app_name")) {
            $mainAssociation = $repoAssociation->findOneByName($this->getParameter("app_name"));
        }
        
        return $this->render('AppBundle:Default:workshop.html.twig', array(
            'mainAssociation'	=> $mainAssociation,
            'association'		=> $association
        ));
    }
    
    public function workshopsAction()
    {
        $em = $this->getDoctrine()->getManager();
        $repoAssociation = $em->getRepository("AppBundle:Association");
        $associations = $repoAssociation->findBy(array("displayed" => true, "isWorkshop" => true), array("name" => "ASC"));
        $mainAssociation = null;
        if($this->container->hasParameter("app_name")) {
            $mainAssociation = $repoAssociation->findOneByName($this->getParameter("app_name"));
        }
        
        return $this->render('AppBundle:Default:associations.html.twig', array(
            'mainAssociation' => $mainAssociation,
            'associations' => $associations
        ));
    }
    
    public function mapAction()
    {
        $em = $this->getDoctrine()->getManager();
        $repoEvent = $em->getRepository("AppBundle:Event");
        $repoAssociation = $em->getRepository("AppBundle:Association");
        $mainAssociation = null;
        if($this->container->hasParameter("app_name")) {
            $mainAssociation = $repoAssociation->findOneByName($this->getParameter("app_name"));
        }
        
        /*$query = $em->createQuery(
            'SELECT e
            FROM AppBundle:Event e
            WHERE e.endTime > :now
        	AND e.published = true
            AND e.placeEntity IS NOT NULL
            ORDER BY e.startTime ASC'
        )->setParameter('now', new \DateTime());*/
        
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
        ->andWhere($qb->expr()->gte("e.endTime", ":now"))
        ->andWhere($qb->expr()->isNotNull("e.placeEntity"))
        ->setParameter("published", true)
        ->setParameter("displayed", true)
        ->setParameter("now", new \DateTime())
        ->orderBy("e.startTime", "ASC")
        ;
        
        $events = $qb->getQuery()->getResult();
        
        return $this->render('AppBundle:Default:map.html.twig', array(
            'mainAssociation'   => $mainAssociation,
            'events'            => $events
        ));
    }
    
    public function legalTermsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repoAssociation = $em->getRepository("AppBundle:Association");
        $mainAssociation = null;
        if($this->container->hasParameter("app_name")) {
            $mainAssociation = $repoAssociation->findOneByName($this->getParameter("app_name"));
        }
        
        return $this->render('AppBundle:Default:legalTerms.html.twig', array(
            'mainAssociation'	=> $mainAssociation
        ));
    }
}
