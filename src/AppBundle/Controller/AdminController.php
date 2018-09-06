<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Event;
use AppBundle\Form\AssociationFormType;
use AppBundle\Form\EventFormType;
use AppBundle\Form\WorkshopFormType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\DocumentFormType;
use AppBundle\Entity\Document;
use AppBundle\Entity\Place;
use AppBundle\Form\PlaceFormType;
use AppBundle\Entity\Association;

class AdminController extends Controller
{
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $translator = $this->get("translator");
        $repoDocument = $em->getRepository("AppBundle:Document");
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $form = $this->get('form.factory')->createBuilder(AssociationFormType::class, $user->getAssociation())->getForm();
        $formDocument = null;
        $newDocument = null;
        
        $deleteId = $request->get("deleteId");
        
        // If the user is the owner of the admin association, he can add document (other associations can't)
        if($user->getAssociation() != null) {
        	
        	$newDocument = new Document();
        	$newDocument->setAssociation($user->getAssociation());
        	$formDocument = $this->get('form.factory')->createBuilder(DocumentFormType::class, $newDocument)->getForm();
        }

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            // Manage association form
            if($form->isSubmitted()) {
	            if ($form->isValid()) {
	
	                $user->getAssociation()->uploadPicture();
	                $em->flush();
	                
	                $em->refresh($user);
	
	                $request->getSession()->getFlashBag()->add('success', $translator->trans("association_updated"));
	            }
	            else
	                $request->getSession()->getFlashBag()->add('warning', $translator->trans("error_field"));
            }
            
            // Manage document form
            if($formDocument != null) {
            	$formDocument->handleRequest($request);
            	
            	if($formDocument->isSubmitted()) {
            		if ($formDocument->isValid()) {
            			
            			// Upload the document
          				$em->persist($newDocument);
            			$res = $newDocument->upload();
            			
            			if($res) {
	           				$request->getSession()->getFlashBag()->add('success', $translator->trans("document_added"));
			                
			                // Reinit the form
			                $newDocument = new Document();
			                $newDocument->setAssociation($user->getAssociation());
			                $formDocument = $this->get('form.factory')->createBuilder(DocumentFormType::class, $newDocument)->getForm();
            			}
           				else {
           					$em->remove($newDocument);
	           				$request->getSession()->getFlashBag()->add('warning', $translator->trans("unknown_error"));
           				}
           				
           				$em->flush();
           				
           				$em->refresh($user);
           				$em->refresh($user->getAssociation());
            		}
            		else
	                	$request->getSession()->getFlashBag()->add('warning', $translator->trans("error_field"));
            	}
            }
            


            // Delete request
            if ($request->isMethod('POST') && !empty($deleteId)) {
            	 
            	$document = $repoDocument->find($deleteId);
            	 
            	if($document != null && $user->getAssociation() != null && $document->getAssociation()->getId() == $user->getAssociation()->getId()) {
            		$em->remove($document);
            		$em->flush();
            		$request->getSession()->getFlashBag()->add('success', $translator->trans("document_deleted"));
            	}
            }
        }

        return $this->render('AppBundle:Admin:index.html.twig', array(
            "form"          => $form->createView(),
        	"formDocument" => $formDocument == null ? null: $formDocument->createView(),
        ));
    }

    public function eventsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $translator = $this->get("translator");
        $repoEvent = $em->getRepository("AppBundle:Event");
        $repoPlace = $em->getRepository("AppBundle:Place");
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $events = array();
        $isDuplicatingEvent = false;

        $action = $request->get("action");
        $placeId = $request->get("placeId");
        $eventId = $request->get("eventId");
        $deleteId = $request->get("deleteId");
        $eventToDuplicateId = $request->get("eventToDuplicateId");
        $eventToDuplicate = null;

        $newEvent = new Event();
        
        if(!empty($action) && $action == "duplicate" && !empty($eventId)) {
        	// Duplicate an action
        	 
        	$eventToDuplicate = $repoEvent->find($eventId);
        	 
        	if($eventToDuplicate != null) {
        		$isDuplicatingEvent = true;
        
        		$newEvent->setAssociation($eventToDuplicate->getAssociation());
        		$newEvent->setDescription($eventToDuplicate->getDescription());
        		$newEvent->setEndTime($eventToDuplicate->getEndTime());
        		$newEvent->setFree($eventToDuplicate->getFree());
        		$newEvent->setName($eventToDuplicate->getName());
        		$newEvent->setPicture($eventToDuplicate->getPicture());
        		$newEvent->setPlaceEntity($eventToDuplicate->getPlaceEntity());
        		$newEvent->setPricing($eventToDuplicate->getPricing());
        		$newEvent->setPublished($eventToDuplicate->isPublished());
        		$newEvent->setSearchVolunteers($eventToDuplicate->getSearchVolunteers());
        		$newEvent->setStartTime($eventToDuplicate->getStartTime());
        		$newEvent->setWebsite($eventToDuplicate->getWebsite());
        	}
        }
        
        // Add request
        if ($user->getAssociation() != null && !empty($placeId)) {
            
            // Check if we change the place of an event or is we create an event in a place
            
            $place = $repoPlace->find($placeId);
            
            if($place != null || $placeId == "-1") {
                
                if(!empty($action) && $action == "changePlace" && !empty($eventId)) {
                    
                    $event = $repoEvent->find($eventId);
                    
                    if($event != null) {
                        
                        if($placeId == "-1") {
                            $placeId = "";
                        }
                        
                        $event->setPlaceEntity($place);
                        $em->flush();
                        $request->getSession()->getFlashBag()->add('success', $translator->trans("place_changed"));
                    }
                }
                else {
                    $newEvent->setPlaceEntity($place);
                }
            }
        }
        
        // We have to do that because it's complicated to duplicate the place
        if(!empty($eventToDuplicateId)) {
            $tempEvent = $repoEvent->find($eventToDuplicateId);
            
            if($tempEvent != null) {
                $newEvent->setPlaceEntity($tempEvent->getPlaceEntity());
            }
        }
        
        $formAdd = $this->get('form.factory')->createBuilder(EventFormType::class, $newEvent)->getForm();
        
        if($user->getAssociation() != null) {
            
            if ($request->isMethod('POST') && (empty($action) || $action == "search") && empty($deleteId)) {
        	    
        		$newEvent->setAssociation($user->getAssociation());
        	
        		$formAdd->handleRequest($request);
        	
        		if ($formAdd->isValid()) {
        	
        			if ($newEvent->getStartTime() > $newEvent->getEndTime())
        				$request->getSession()->getFlashBag()->add('warning', $translator->trans("error_event_dates_order"));
        			else {
        				
        				// Erase pricing field if it's free
        				if($newEvent->getFree())
       						$newEvent->setPricing(null);
       					
        				$em->persist($newEvent);
        				$em->flush();
        				
                   		$newEvent->uploadPicture();
                   		$em->flush();
	                    
	                    if(!empty($eventToDuplicateId) && empty($newEvent->getPicture())) {
	                    	$tempEvent = $repoEvent->find($eventToDuplicateId);
	                    	
	                    	if($tempEvent != null) {
	                    		$newEvent->setPicture($tempEvent->getPicture());
	                    		$em->flush();
	                    	}
	                    }
	                    
	                    if($placeId == "-1") {
	                        $placeId = "";
	                    }
        
       					$newEvent = new Event();
       					$formAdd = $this->get('form.factory')->createBuilder(EventFormType::class, $newEvent)->getForm();
       	
       					$request->getSession()->getFlashBag()->add('success', $translator->trans("event_created"));
       				}
        		}
        	}
	        
	        // Delete request
	        if ($request->isMethod('POST') && !empty($deleteId)) {
	            
	            $event = $repoEvent->find($deleteId);
	            
	            if($event != null) {
	                $em->remove($event);
	                $em->flush();
	                $request->getSession()->getFlashBag()->add('success', $translator->trans("event_deleted"));
	            }
	        }
	        
	        $query = $em->createQuery(
	            'SELECT e
	            FROM AppBundle:Event e
	            WHERE e.endTime > :now
	        	AND e.association = :association
	            ORDER BY e.startTime ASC'
	        )->setParameter('now', new \DateTime())
	        ->setParameter('association', $user->getAssociation());
	        $events = $query->getResult();
	        
	        $date = new \DateTime();
	        $date->modify("-62 days");
	        
	        $query = $em->createQuery(
	            'SELECT e
	            FROM AppBundle:Event e
	            WHERE e.endTime >= :date
	        	AND e.endTime <= :now
	        	AND e.association = :association
	            ORDER BY e.startTime DESC'
	        )->setParameter('date', $date)
	        ->setParameter('now', new \DateTime())
	        ->setParameter('association', $user->getAssociation());
	        $passedEvents = $query->getResult();
	        
	        $allEvents = array_merge($events, $passedEvents);
        }
        
        $params = array(
            "formAdd"				=> $formAdd->createView(),
            "events"				=> $events,
        	"passedEvents"			=> $passedEvents,
        	"allEvents"				=> $allEvents,
        	"isDuplicatingEvent"	=> $isDuplicatingEvent,
            "displayAddForm"        => ($newEvent->getPlaceEntity() != null || $placeId == "-1"),
            "newPlace"              => $newEvent->getPlaceEntity()
        );
        
        if($eventToDuplicate != null) {
        	$params["eventToDuplicateId"] = $eventToDuplicate->getId();
        }

        return $this->render('AppBundle:Admin:events.html.twig', $params);
    }
    
    public function eventAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $translator = $this->get("translator");
        $repoEvent = $em->getRepository("AppBundle:Event");
        $repoPlace = $em->getRepository("AppBundle:Place");
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $form = null;
        
        $action = $request->get("action");
        $placeId = $request->get("placeId");
        $eventId = $request->get("id");
        
        $event = $repoEvent->find($eventId);
        
        // Only the events' association can be updated
        if($event != null && ($user->getAssociation() == null || $event->getAssociation()->getId() != $user->getAssociation()->getId())) {
            $event = null;
        }
        
        if($event == null) {
            $request->getSession()->getFlashBag()->add('success', $translator->trans("event_unknown"));
        }
        else {
            if ($user->getAssociation() != null && !empty($placeId)) {
                
                // Check if we change the place of an event
                
                $place = $repoPlace->find($placeId);
                
                if(($place != null || $placeId == "-1") && !empty($action) && $action == "changePlace") {
                    
                    if($placeId == "-1") {
                        $placeId = "";
                    }
                    
                    $event->setPlaceEntity($place);
                    $em->flush();
                    $request->getSession()->getFlashBag()->add('success', $translator->trans("place_changed"));
                }
            }
            
            $form = $this->get('form.factory')->createBuilder(EventFormType::class, $event)->getForm();
            
            if($user->getAssociation() != null) {
                
                if ($request->isMethod('POST')) {
                    
                    $form->handleRequest($request);// Set data into the form
                    
                    if ($form->isValid()) {
                        
                        if ($event->getStartTime() > $event->getEndTime()) {
                            $request->getSession()->getFlashBag()->add('warning', $translator->trans("error_event_dates_order"));
                        }
                        else {
                            
                            // Erase pricing field if it's free
                            if($event->getFree()) {
                                $event->setPricing(null);
                            }
                            
                            // Save the object event
                            $em->flush();
                            
                            $event->uploadPicture();
                            $em->flush();
                            
                            if($placeId == "-1") {
                                $placeId = "";
                            }
                            
                            $request->getSession()->getFlashBag()->add('success', $translator->trans("event_updated"));
                        }
                    }
                }
            }
        }
        
        $params = array(
            "form"  => ($form == null ? null : $form->createView()),
            "event" => $event
        );
        
        return $this->render('AppBundle:Admin:event.html.twig', $params);
    }
    
    public function calendarAction(Request $request)
    {
    	$em = $this->getDoctrine()->getManager();
    	
    	$query = $em->createQuery(
			'SELECT e
			FROM AppBundle:Event e
			WHERE e.endTime > :now
			ORDER BY e.startTime ASC'
		)->setParameter('now', new \DateTime());
		
		$tempEvents = $query->getResult();
		$events = array();
		
		foreach($tempEvents as $event) {
			if($event->getAssociation()->isDisplayed())
				$events[] = $event;
		}
    	
    	return $this->render('AppBundle:Admin:calendar.html.twig', array(
    		'events' => $events,
    	));
    }
    
    public function searchPlaceAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repoPlace = $em->getRepository("AppBundle:Place");
        
        $places = array();
        $search = $request->get("name");
        $action = $request->get("action");
        $eventId = $request->get("eventId");
        
        if($request->isMethod('POST') && !empty($search)) {
            
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
        }
        
        $newPlace = new Place();
        
        $formAdd = $this->get('form.factory')->createBuilder(PlaceFormType::class, $newPlace)->getForm();
        
        if($request->isMethod('POST')) {
            
            $formAdd->handleRequest($request);
            
            if ($formAdd->isValid()) {
                
                $em->persist($newPlace);
                $em->flush();
                
                $route = "";
                
                if(empty($eventId)) {
                    
                    $route = "app_admin_events";
                    
                    if($request->get("source") == "super_admin") {
                        $route = "app_superadmin_events";
                    }
                }
                else {
                    $route = "app_admin_event";
                    
                    if($request->get("source") == "super_admin") {
                        $route = "app_superadmin_event";
                    }
                }
                
                $params = array("placeId" => $newPlace->getId());
                
                if(!empty($action)) {
                    $params["action"] = $action;
                }
                if(!empty($eventId)) {
                    $params["id"] = $eventId;
                }
                
                return $this->redirectToRoute($route, $params);
            }
        }
        
        return $this->render('AppBundle:Admin:searchPlace.html.twig', array(
            'places'    => $places,
            'search'    => $search,
            'formAdd'   => $formAdd->createView(),
            'action'    => $action,
            'eventId'   => $eventId
        ));
    }
    
    public function eventDocumentAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repoDocument = $em->getRepository("AppBundle:Document");
        $repoEvent = $em->getRepository("AppBundle:Event");
        $translator = $this->get("translator");
        $formDocument = null;
        $newDocument = null;
        $deleteId = $request->get("deleteId");
        $eventId = $request->get("id");
        
        $event = $repoEvent->find($eventId);
        
        if($event != null) {
            
            // Check if the event is organized by the user association or if the user is admin
            if(!$this->getUser()->isAdmin() && $event->getAssociation()->getId() != $this->getUser()->getAssociation()->getId()) {
                
                $event = null;
            }
            else {
                $newDocument = new Document();
                $newDocument->setEvent($event);
                $formDocument = $this->get('form.factory')->createBuilder(DocumentFormType::class, $newDocument)->getForm();
                
                if ($request->isMethod('POST')) {
                    
                    // Manage document form
                    $formDocument->handleRequest($request);
                    
                    if($formDocument->isSubmitted()) {
                        if ($formDocument->isValid()) {
                            
                            // Upload the document
                            $em->persist($newDocument);
                            $res = $newDocument->upload();
                            
                            if($res) {
                                $request->getSession()->getFlashBag()->add('success', $translator->trans("document_added"));
                                
                                // Reinit the form
                                $newDocument = new Document();
                                $newDocument->setEvent($event);
                                $formDocument = $this->get('form.factory')->createBuilder(DocumentFormType::class, $newDocument)->getForm();
                            }
                            else {
                                $em->remove($newDocument);
                                $request->getSession()->getFlashBag()->add('warning', $translator->trans("unknown_error"));
                            }
                            
                            $em->flush();
                            
                            $em->refresh($event);
                        }
                        else
                            $request->getSession()->getFlashBag()->add('warning', $translator->trans("error_field"));
                    }
                    
                    
                    
                    // Delete request
                    if ($request->isMethod('POST') && !empty($deleteId)) {
                        
                        $document = $repoDocument->find($deleteId);
                        
                        if($document != null && $document->getEvent()->getId() == $event->getId()) {
                            $em->remove($document);
                            $em->flush();
                            $request->getSession()->getFlashBag()->add('success', $translator->trans("document_deleted"));
                            
                            $em->refresh($event);
                        }
                    }
                }
            }
        }
        
        return $this->render('AppBundle:Admin:eventDocument.html.twig', array(
            "event"         => $event,
            "formDocument"  => $formDocument == null ? null: $formDocument->createView()
        ));
    }
    
    public function workshopAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $translator = $this->get("translator");
        $repoDocument = $em->getRepository("AppBundle:Document");
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $form = $this->get('form.factory')->createBuilder(WorkshopFormType::class, $user->getWorkshop())->getForm();
        $formDocument = null;
        $newDocument = null;
        
        $deleteId = $request->get("deleteId");
        
        // If the user is the owner of the admin association, he can add document (other associations can't)
        if($user->getWorkshop() != null) {
            
            $newDocument = new Document();
            $newDocument->setAssociation($user->getWorkshop());
            $formDocument = $this->get('form.factory')->createBuilder(DocumentFormType::class, $newDocument)->getForm();
        }
        
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            
            // Manage association form
            if($form->isSubmitted()) {
                if ($form->isValid()) {
                    
                    $user->getWorkshop()->uploadPicture();
                    $em->flush();
                    
                    $em->refresh($user);
                    
                    $request->getSession()->getFlashBag()->add('success', $translator->trans("association_updated"));
                }
                else
                    $request->getSession()->getFlashBag()->add('warning', $translator->trans("error_field"));
            }
            
            // Manage document form
            if($formDocument != null) {
                $formDocument->handleRequest($request);
                
                if($formDocument->isSubmitted()) {
                    if ($formDocument->isValid()) {
                        
                        // Upload the document
                        $em->persist($newDocument);
                        $res = $newDocument->upload();
                        
                        if($res) {
                            $request->getSession()->getFlashBag()->add('success', $translator->trans("document_added"));
                            
                            // Reinit the form
                            $newDocument = new Document();
                            $newDocument->setAssociation($user->getWorkshop());
                            $formDocument = $this->get('form.factory')->createBuilder(DocumentFormType::class, $newDocument)->getForm();
                        }
                        else {
                            $em->remove($newDocument);
                            $request->getSession()->getFlashBag()->add('warning', $translator->trans("unknown_error"));
                        }
                        
                        $em->flush();
                        
                        $em->refresh($user);
                        $em->refresh($user->getWorkshop());
                    }
                    else
                        $request->getSession()->getFlashBag()->add('warning', $translator->trans("error_field"));
                }
            }
            
            
            
            // Delete request
            if ($request->isMethod('POST') && !empty($deleteId)) {
                
                $document = $repoDocument->find($deleteId);
                
                if($document != null && $user->getWorkshop() != null && $document->getAssociation()->getId() == $user->getWorkshop()->getId()) {
                    $em->remove($document);
                    $em->flush();
                    $request->getSession()->getFlashBag()->add('success', $translator->trans("document_deleted"));
                }
            }
        }
        
        return $this->render('AppBundle:Admin:workshop.html.twig', array(
            "form"          => $form->createView(),
            "formDocument" => $formDocument == null ? null: $formDocument->createView(),
        ));
    }
    
    public function workshopsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $translator = $this->get("translator");
        $repoAssociation = $em->getRepository("AppBundle:Association");
        $newAssociation = new Association();
        $newAssociation->setIsWorkshop(true);
        $formAdd = $this->get('form.factory')->createBuilder(WorkshopFormType::class, $newAssociation)->getForm();
        
        $associationIdToUpdate = $request->get("associationId");
        $deleteId = $request->get("deleteId");
        
        // Add form
        if ($request->isMethod('POST') && empty($associationIdToUpdate)) {
            
            $formAdd->handleRequest($request);
            
            if ($formAdd->isValid()) {
                $em->persist($newAssociation);
                $em->flush();
                
                $newAssociation->uploadPicture();
                $em->flush();
                
                $request->getSession()->getFlashBag()->add('success', $translator->trans("association_created"));
                
                // Empty the form
                $newAssociation = new Association();
                $formAdd = $this->get('form.factory')->createBuilder(WorkshopFormType::class, $newAssociation)->getForm();
            }
        }
        
        // Delete request
        if ($request->isMethod('POST') && !empty($deleteId)) {
            
            $association = $repoAssociation->find($deleteId);
            
            if($association != null) {
                
                // Remove users affected to the association
                foreach($association->getOwners() as $owner) {
                    $owner->setAssociation(null);
                }
                
                $em->remove($association);
                $em->flush();
                $request->getSession()->getFlashBag()->add('success', $translator->trans("association_deleted"));
            }
        }
        
        $associations = $repoAssociation->findBy(array("isWorkshop" => true), array("name" => "ASC"));
        
        $forms = array();
        foreach($associations as $association) {
            $formBuilder = $this->get('form.factory')->createBuilder(WorkshopFormType::class, $association);
            
            $form = $formBuilder->getForm();
            
            if ($request->isMethod('POST')) {
                
                if(!empty($associationIdToUpdate) && $associationIdToUpdate == $association->getId()) {
                    
                    $form->handleRequest($request);// Set new data into the form
                    
                    if ($form->isValid()) {
                        
                        $association->uploadPicture();
                        $em->flush();
                        
                        $request->getSession()->getFlashBag()->add('success', $translator->trans("association_updated"));
                    }
                    else
                        $request->getSession()->getFlashBag()->add('warning', $translator->trans("error_field"));
                }
            }
            
            $formView = $form->createView();
            
            $forms[] = $formView;
        }
        
        return $this->render('AppBundle:Admin:workshops.html.twig', array(
            "formAdd" => $formAdd->createView(),
            "forms" => $forms,
            "associations" => $associations,
        ));
    }
    
    public function workshopEventsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $translator = $this->get("translator");
        $repoEvent = $em->getRepository("AppBundle:Event");
        $repoPlace = $em->getRepository("AppBundle:Place");
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $events = array();
        $isDuplicatingEvent = false;
        
        $action = $request->get("action");
        $placeId = $request->get("placeId");
        $eventId = $request->get("eventId");
        $deleteId = $request->get("deleteId");
        $eventToDuplicateId = $request->get("eventToDuplicateId");
        $eventToDuplicate = null;
        
        $newEvent = new Event();
        
        if(!empty($action) && $action == "duplicate" && !empty($eventId)) {
            // Duplicate an action
            
            $eventToDuplicate = $repoEvent->find($eventId);
            
            if($eventToDuplicate != null) {
                $isDuplicatingEvent = true;
                
                $newEvent->setAssociation($eventToDuplicate->getAssociation());
                $newEvent->setDescription($eventToDuplicate->getDescription());
                $newEvent->setEndTime($eventToDuplicate->getEndTime());
                $newEvent->setFree($eventToDuplicate->getFree());
                $newEvent->setName($eventToDuplicate->getName());
                $newEvent->setPicture($eventToDuplicate->getPicture());
                $newEvent->setPlaceEntity($eventToDuplicate->getPlaceEntity());
                $newEvent->setPricing($eventToDuplicate->getPricing());
                $newEvent->setPublished($eventToDuplicate->isPublished());
                $newEvent->setSearchVolunteers($eventToDuplicate->getSearchVolunteers());
                $newEvent->setStartTime($eventToDuplicate->getStartTime());
                $newEvent->setWebsite($eventToDuplicate->getWebsite());
            }
        }
        
        // Add request
        if ($user->getWorkshop() != null && !empty($placeId)) {
            
            // Check if we change the place of an event or is we create an event in a place
            
            $place = $repoPlace->find($placeId);
            
            if($place != null || $placeId == "-1") {
                
                if(!empty($action) && $action == "changePlace" && !empty($eventId)) {
                    
                    $event = $repoEvent->find($eventId);
                    
                    if($event != null) {
                        
                        if($placeId == "-1") {
                            $placeId = "";
                        }
                        
                        $event->setPlaceEntity($place);
                        $em->flush();
                        $request->getSession()->getFlashBag()->add('success', $translator->trans("place_changed"));
                    }
                }
                else {
                    $newEvent->setPlaceEntity($place);
                }
            }
        }
        
        // We have to do that because it's complicated to duplicate the place
        if(!empty($eventToDuplicateId)) {
            $tempEvent = $repoEvent->find($eventToDuplicateId);
            
            if($tempEvent != null) {
                $newEvent->setPlaceEntity($tempEvent->getPlaceEntity());
            }
        }
        
        $formAdd = $this->get('form.factory')->createBuilder(EventFormType::class, $newEvent)->getForm();
        
        if($user->getWorkshop() != null) {
            
            if ($request->isMethod('POST') && (empty($action) || $action == "search") && empty($deleteId)) {
                
                $newEvent->setAssociation($user->getWorkshop());
                
                $formAdd->handleRequest($request);
                
                if ($formAdd->isValid()) {
                    
                    if ($newEvent->getStartTime() > $newEvent->getEndTime())
                        $request->getSession()->getFlashBag()->add('warning', $translator->trans("error_event_dates_order"));
                        else {
                            
                            // Erase pricing field if it's free
                            if($newEvent->getFree())
                                $newEvent->setPricing(null);
                                
                                $em->persist($newEvent);
                                $em->flush();
                                
                                $newEvent->uploadPicture();
                                $em->flush();
                                
                                if(!empty($eventToDuplicateId) && empty($newEvent->getPicture())) {
                                    $tempEvent = $repoEvent->find($eventToDuplicateId);
                                    
                                    if($tempEvent != null) {
                                        $newEvent->setPicture($tempEvent->getPicture());
                                        $em->flush();
                                    }
                                }
                                
                                if($placeId == "-1") {
                                    $placeId = "";
                                }
                                
                                $newEvent = new Event();
                                $formAdd = $this->get('form.factory')->createBuilder(EventFormType::class, $newEvent)->getForm();
                                
                                $request->getSession()->getFlashBag()->add('success', $translator->trans("event_created"));
                        }
                }
            }
            
            // Delete request
            if ($request->isMethod('POST') && !empty($deleteId)) {
                
                $event = $repoEvent->find($deleteId);
                
                if($event != null) {
                    $em->remove($event);
                    $em->flush();
                    $request->getSession()->getFlashBag()->add('success', $translator->trans("event_deleted"));
                }
            }
            
            $query = $em->createQuery(
                'SELECT e
	            FROM AppBundle:Event e
	            WHERE e.endTime > :now
	        	AND e.association = :association
	            ORDER BY e.startTime ASC'
                )->setParameter('now', new \DateTime())
                ->setParameter('association', $user->getWorkshop());
                $events = $query->getResult();
                
                $date = new \DateTime();
                $date->modify("-62 days");
                
                $query = $em->createQuery(
                    'SELECT e
	            FROM AppBundle:Event e
	            WHERE e.endTime >= :date
	        	AND e.endTime <= :now
	        	AND e.association = :association
	            ORDER BY e.startTime DESC'
                    )->setParameter('date', $date)
                    ->setParameter('now', new \DateTime())
                    ->setParameter('association', $user->getWorkshop());
                    $passedEvents = $query->getResult();
                    
                    $allEvents = array_merge($events, $passedEvents);
        }
        
        $params = array(
            "formAdd"				=> $formAdd->createView(),
            "events"				=> $events,
            "passedEvents"			=> $passedEvents,
            "allEvents"				=> $allEvents,
            "isDuplicatingEvent"	=> $isDuplicatingEvent,
            "displayAddForm"        => ($newEvent->getPlaceEntity() != null || $placeId == "-1"),
            "newPlace"              => $newEvent->getPlaceEntity()
        );
        
        if($eventToDuplicate != null) {
            $params["eventToDuplicateId"] = $eventToDuplicate->getId();
        }
        
        return $this->render('AppBundle:Admin:workshopEvents.html.twig', $params);
    }
}
