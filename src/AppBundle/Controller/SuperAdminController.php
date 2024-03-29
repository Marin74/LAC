<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Association;
use AppBundle\Entity\Event;
use AppBundle\Entity\Newsletter;
use AppBundle\Entity\NewsletterEvent;
use AppBundle\Entity\User;
use AppBundle\Form\SuperAdminAssociationFormType;
use AppBundle\Form\AssociationTypeFormType;
use AppBundle\Form\NewsletterFormType;
use AppBundle\Form\SuperAdminEventFormType;
use AppBundle\Form\UserFormType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\QuizScore;
use AppBundle\Form\QuizScoreFormType;
use AppBundle\Form\ProfileFormType;
use AppBundle\Form\ChangePasswordFormType;
use AppBundle\Entity\Document;
use AppBundle\Form\DocumentFormType;
use AppBundle\Form\PlaceFormType;
use Doctrine\ORM\Query\Expr\Join;
use AppBundle\Entity\AssociationType;

class SuperAdminController extends Controller
{
    public function eventsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $translator = $this->get("translator");
        $repoEvent = $em->getRepository("AppBundle:Event");
        $repoPlace = $em->getRepository("AppBundle:Place");
        $action = $request->get("action");
        $placeId = $request->get("placeId");
        $eventId = $request->get("eventId");
        $deleteId = $request->get("deleteId");
        $eventToDuplicateId = $request->get("eventToDuplicateId");
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $isDuplicatingEvent = false;
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
        		$newEvent->setWebsiteTitle($eventToDuplicate->getWebsiteTitle());
        		$newEvent->setEmail($eventToDuplicate->getEmail());
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
        
        $formAdd = $this->get('form.factory')->createBuilder(SuperAdminEventFormType::class, $newEvent)->getForm();

        // Add form
        if ($request->isMethod('POST') && (empty($action) || $action == "search") && empty($deleteId)) {

            $formAdd->handleRequest($request);
            
            if ($formAdd->isValid()) {

                if ($newEvent->getStartTime() > $newEvent->getEndTime())
                    $request->getSession()->getFlashBag()->add('warning', $translator->trans("error_event_dates_order"));
                else {
        					
   					// Erase pricing field if it's free
                    if($newEvent->getFree()) {
        				$newEvent->setPricing(null);
                    }
                    // Erase the website title field if there is no website
                    if(empty($newEvent->getWebsite()) && !empty($newEvent->getWebsiteTitle())) {
                        $newEvent->setWebsiteTitle(null);
                    }
        			
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
                    $formAdd = $this->get('form.factory')->createBuilder(SuperAdminEventFormType::class, $newEvent)->getForm();

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
            ORDER BY e.startTime ASC'
        )->setParameter('now', new \DateTime());
        $events = $query->getResult();
        
        $date = new \DateTime();
        $date->modify("-62 days");
        
        $query = $em->createQuery(
            'SELECT e
            FROM AppBundle:Event e
            WHERE e.endTime >= :date
        	AND e.endTime <= :now
            ORDER BY e.startTime DESC'
        )->setParameter('date', $date)
        ->setParameter('now', new \DateTime());
        $passedEvents = $query->getResult();
        
        $allEvents = array_merge($events, $passedEvents);
        
        $params =  array(
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

        return $this->render('AppBundle:SuperAdmin:events.html.twig', $params);
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
            
            $form = $this->get('form.factory')->createBuilder(SuperAdminEventFormType::class, $event)->getForm();
            
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
                            // Erase the website title field if there is no website
                            if(empty($event->getWebsite()) && !empty($event->getWebsiteTitle())) {
                                $event->setWebsiteTitle(null);
                            }
                            
                            // Save the object event
                            $em->flush();
                            
                            $event->uploadPicture();
                            $em->flush();
                            
                            if($placeId == "-1") {
                                $placeId = "";
                            }
                            
                            $request->getSession()->getFlashBag()->add('success', $translator->trans("event_updated"));
                            
                            // Refresh the form (because the event object can be modified after the creation of the form)
                            $form = $this->get('form.factory')->createBuilder(SuperAdminEventFormType::class, $event)->getForm();
                        }
                    }
                }
            }
        }
        
        $params = array(
            "form"  => ($form == null ? null : $form->createView()),
            "event" => $event
        );
        
        return $this->render('AppBundle:SuperAdmin:event.html.twig', $params);
    }

    public function usersAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $translator = $this->get("translator");
        $repoUser = $em->getRepository("AppBundle:User");
        $newUser = new User();
        $formAdd = $this->get('form.factory')->createBuilder(UserFormType::class, $newUser)->getForm();

        $userIdToUpdate = $request->get("userId");
        $userIdToUpdatePassword = $request->get("userIdPassword");
        $deleteId = $request->get("deleteId");

        // Add form
        if ($request->isMethod('POST') && empty($userIdToUpdate)) {

            $formAdd->handleRequest($request);

            if ($formAdd->isValid()) {
            	// Activate the account
            	$newUser->setEnabled(true);
            	
                $em->persist($newUser);
                $em->flush();

                $request->getSession()->getFlashBag()->add('success', $translator->trans("user_created"));

                // Empty the form
                $newUser = new User();
                $formAdd = $this->get('form.factory')->createBuilder(UserFormType::class, $newUser)->getForm();
            }
        }

        // Delete request
        if ($request->isMethod('POST') && !empty($deleteId)) {

            $user = $repoUser->find($deleteId);

            if($user != null) {

                $em->remove($user);
                $em->flush();
                $request->getSession()->getFlashBag()->add('success', $translator->trans("user_deleted"));
            }
        }

        $users = $repoUser->findBy(array(), array("username" => "ASC"));

        $forms = array();
        $formsPassword = array();
        foreach($users as $user) {
        	// Update profile
            $formBuilder = $this->get('form.factory')->createBuilder(ProfileFormType::class, $user);

            $form = $formBuilder->getForm();

            if ($request->isMethod('POST')) {

                if(!empty($userIdToUpdate) && $userIdToUpdate == $user->getId()) {

                    $form->handleRequest($request);// Set new data into the form

                    if ($form->isValid()) {

                        $em->flush();

                        $request->getSession()->getFlashBag()->add('success', $translator->trans("user_updated"));
                    }
                    else
                        $request->getSession()->getFlashBag()->add('warning', $translator->trans("error_field"));
                }
            }

            $formView = $form->createView();

            $forms[] = $formView;
            
            // Update password
            $formBuilder = $this->get('form.factory')->createBuilder(ChangePasswordFormType::class, $user);

            $form = $formBuilder->getForm();

            if ($request->isMethod('POST')) {

                if(!empty($userIdToUpdatePassword) && $userIdToUpdatePassword == $user->getId()) {

                    $form->handleRequest($request);// Set new data into the form

                    if ($form->isValid()) {

                    	$userManager = $this->container->get('fos_user.user_manager');
                    	$userManager->updateUser($user);
                        $em->flush();

                        $request->getSession()->getFlashBag()->add('success', $translator->trans("password_updated"));
                    }
                    else
                        $request->getSession()->getFlashBag()->add('warning', $translator->trans("error_field"));
                }
            }

            $formView = $form->createView();

            $formsPassword[] = $formView;
        }

        return $this->render('AppBundle:SuperAdmin:users.html.twig', array(
            "users"			=> $users,
            "formAdd"		=> $formAdd->createView(),
            "forms"			=> $forms,
        	"formsPassword"	=> $formsPassword
        ));
    }

    public function associationsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $translator = $this->get("translator");
        $repoAssociation = $em->getRepository("AppBundle:Association");
        $newAssociation = new Association();
        $formAdd = $this->get('form.factory')->createBuilder(SuperAdminAssociationFormType::class, $newAssociation)->getForm();

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
                $formAdd = $this->get('form.factory')->createBuilder(SuperAdminAssociationFormType::class, $newAssociation)->getForm();
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

        $associations = $repoAssociation->findBy(array("isWorkshop" => false), array("name" => "ASC"));

        $forms = array();
        foreach($associations as $association) {
            $formBuilder = $this->get('form.factory')->createBuilder(SuperAdminAssociationFormType::class, $association);

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

        return $this->render('AppBundle:SuperAdmin:associations.html.twig', array(
            "formAdd" => $formAdd->createView(),
            "forms" => $forms,
            "associations" => $associations,
        ));
    }
    
    public function associationTypesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $translator = $this->get("translator");
        $repoAssociationType = $em->getRepository("AppBundle:AssociationType");
        $newAssociationType = new AssociationType();
        $formAdd = $this->get('form.factory')->createBuilder(AssociationTypeFormType::class, $newAssociationType)->getForm();
        
        $associationTypeIdToUpdate = $request->get("associationTypeId");
        $deleteId = $request->get("deleteId");
        
        // Add form
        if ($request->isMethod('POST') && empty($associationTypeIdToUpdate)) {
            
            $formAdd->handleRequest($request);
            
            if ($formAdd->isValid()) {
                $em->persist($newAssociationType);
                $em->flush();
                
                $request->getSession()->getFlashBag()->add('success', $translator->trans("association_type_created"));
                
                // Empty the form
                $newAssociationType = new AssociationType();
                $formAdd = $this->get('form.factory')->createBuilder(AssociationTypeFormType::class, $newAssociationType)->getForm();
            }
        }
        
        // Delete request
        if ($request->isMethod('POST') && !empty($deleteId)) {
            
            $associationType = $repoAssociationType->find($deleteId);
            
            if($associationType != null) {
                
                $em->remove($associationType);
                $em->flush();
                $request->getSession()->getFlashBag()->add('success', $translator->trans("association_type_deleted"));
            }
        }
        
        $associationTypes = $repoAssociationType->findBy(array(), array("name" => "ASC"));
        
        $forms = array();
        foreach($associationTypes as $associationType) {
            $formBuilder = $this->get('form.factory')->createBuilder(AssociationTypeFormType::class, $associationType);
            
            $form = $formBuilder->getForm();
            
            if ($request->isMethod('POST')) {
                
                if(!empty($associationTypeIdToUpdate) && $associationTypeIdToUpdate == $associationType->getId()) {
                    
                    $form->handleRequest($request);// Set new data into the form
                    
                    if ($form->isValid()) {
                        $em->flush();
                        
                        $request->getSession()->getFlashBag()->add('success', $translator->trans("association_type_updated"));
                    }
                    else
                        $request->getSession()->getFlashBag()->add('warning', $translator->trans("error_field"));
                }
            }
            
            $formView = $form->createView();
            
            $forms[] = $formView;
        }
        
        return $this->render('AppBundle:SuperAdmin:association_types.html.twig', array(
            "formAdd" => $formAdd->createView(),
            "forms" => $forms,
            "associationTypes" => $associationTypes,
        ));
    }

    public function quizScoresAction(Request $request)
    {
    	$em = $this->getDoctrine()->getManager();
    	$translator = $this->get("translator");
    	$repoQuizScore = $em->getRepository("AppBundle:QuizScore");
    	$newQuizScore = new QuizScore();
    	$formAdd = $this->get('form.factory')->createBuilder(QuizScoreFormType::class, $newQuizScore)->getForm();
    
    	$quizScoreIdToUpdate = $request->get("quizScoreId");
    	$deleteId = $request->get("deleteId");
    
    	// Add form
    	if ($request->isMethod('POST') && empty($quizScoreIdToUpdate)) {
    
    		$formAdd->handleRequest($request);
    
    		if ($formAdd->isValid()) {
    			
    			// Check if the association doesn't have already a score for the associated quiz category
    			$doubloon = $repoQuizScore->findOneBy(array("association" => $newQuizScore->getAssociation(), "quizCategory" => $newQuizScore->getQuizCategory()));
    			
    			if($doubloon != null)
    				$request->getSession()->getFlashBag()->add('warning', $translator->trans("quiz_score_already_exists"));
    			else {
    				$em->persist($newQuizScore);
    				$em->flush();
    				
    				$request->getSession()->getFlashBag()->add('success', $translator->trans("quiz_score_created"));
    				
    				// Empty the form
    				$newQuizScore = new QuizScore();
    				$formAdd = $this->get('form.factory')->createBuilder(QuizScoreFormType::class, $newQuizScore)->getForm();
    			}
    		}
    	}
    
    	// Delete request
    	if ($request->isMethod('POST') && !empty($deleteId)) {
    
    		$quizScore = $repoQuizScore->find($deleteId);
    
    		if($quizScore != null) {
    
    			$em->remove($quizScore);
    			$em->flush();
    			$request->getSession()->getFlashBag()->add('success', $translator->trans("quiz_score_deleted"));
    		}
    	}
    
    	$quizScores = $repoQuizScore->findBy(array(), array("association" => "ASC", "quizCategory" => "ASC"));
    
    	$forms = array();
    	foreach($quizScores as $quizScore) {
    		$formBuilder = $this->get('form.factory')->createBuilder(QuizScoreFormType::class, $quizScore);
    
    		$form = $formBuilder->getForm();
    
    		if ($request->isMethod('POST')) {
    
    			if(!empty($quizScoreIdToUpdate) && $quizScoreIdToUpdate == $quizScore->getId()) {
    
    				$form->handleRequest($request);// Set new data into the form
    
    				if ($form->isValid()) {
    					// Check if the association doesn't have already a score for the associated quiz category
    					$doubloon = $repoQuizScore->findOneBy(array("association" => $quizScore->getAssociation(), "quizCategory" => $quizScore->getQuizCategory()));
    					 
    					if($doubloon != null && $doubloon->getId() != $quizScore->getId()) {

    						$request->getSession()->getFlashBag()->add('warning', $translator->trans("quiz_score_already_exists"));
    						$em->refresh($quizScore);
    					}
    					else {
    						$em->flush();
    						
    						$request->getSession()->getFlashBag()->add('success', $translator->trans("quiz_score_updated"));
    					}
    				}
    				else
    					$request->getSession()->getFlashBag()->add('warning', $translator->trans("error_field"));
    			}
    		}
    
    		$formView = $form->createView();
    
    		$forms[] = $formView;
    	}
    
    	return $this->render('AppBundle:SuperAdmin:quiz_scores.html.twig', array(
			"formAdd" => $formAdd->createView(),
			"forms" => $forms,
			"quizScores" => $quizScores,
    	));
    }
    
    public function associationDocumentAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repoDocument = $em->getRepository("AppBundle:Document");
        $repoAssociation = $em->getRepository("AppBundle:Association");
        $translator = $this->get("translator");
        $formDocument = null;
        $newDocument = null;
        $deleteId = $request->get("deleteId");
        $associationId = $request->get("id");
        
        $association = $repoAssociation->find($associationId);
        
        if($association != null) {
            
            $newDocument = new Document();
            $newDocument->setAssociation($association);
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
                            $newDocument->setAssociation($association);
                            $formDocument = $this->get('form.factory')->createBuilder(DocumentFormType::class, $newDocument)->getForm();
                        }
                        else {
                            $em->remove($newDocument);
                            $request->getSession()->getFlashBag()->add('warning', $translator->trans("unknown_error"));
                        }
                        
                        $em->flush();
                        
                        $em->refresh($association);
                    }
                    else
                        $request->getSession()->getFlashBag()->add('warning', $translator->trans("error_field"));
                }
                
                
                
                // Delete request
                if ($request->isMethod('POST') && !empty($deleteId)) {
                    
                    $document = $repoDocument->find($deleteId);
                    
                    if($document != null && $document->getAssociation()->getId() == $association->getId()) {
                        $em->remove($document);
                        $em->flush();
                        $request->getSession()->getFlashBag()->add('success', $translator->trans("document_deleted"));
                        
                        $em->refresh($association);
                    }
                }
            }
        }
        
        return $this->render('AppBundle:SuperAdmin:associationDocument.html.twig', array(
            "association"   => $association,
            "formDocument"  => $formDocument == null ? null: $formDocument->createView()
        ));
    }
    
    public function placesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repoPlace = $em->getRepository("AppBundle:Place");
        
        $places = array();
        $search = $request->get("name");
        
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
        
        return $this->render('AppBundle:SuperAdmin:places.html.twig', array(
            'places'    => $places,
            'search'    => $search
        ));
    }
    
    public function placeAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $translator = $this->get("translator");
        $repoEvent = $em->getRepository("AppBundle:Event");
        $repoPlace = $em->getRepository("AppBundle:Place");
        $form = null;
        
        $action = $request->get("action");
        $placeId = $request->get("id");
        
        $place = $repoPlace->find($placeId);
        $lastEvents = array();
        
        if($place == null) {
            $request->getSession()->getFlashBag()->add('success', $translator->trans("place_unknown"));
        }
        else {
            
            $qb = $repoEvent->createQueryBuilder('e');
            $qb->where(
                $qb->expr()->andX(
                    $qb->expr()->eq("e.placeEntity", ":place")
                )
            )
            ->setParameter("place", $place)
            ->orderBy("e.startTime", "DESC")
            ->setMaxResults(10)
            ->getQuery();
            
            $lastEvents = $qb->getQuery()->getResult();
            
            
            if($action == "delete") {
                $place->setDeleted(true);
                
                // Save the object
                $em->flush();
                
                $request->getSession()->getFlashBag()->add('success', $translator->trans("place_deleted"));
            }
            elseif($action == "undelete") {
                $place->setDeleted(false);
                
                // Save the object
                $em->flush();
                
                $request->getSession()->getFlashBag()->add('success', $translator->trans("place_undeleted"));
            }
            
            $form = $this->get('form.factory')->createBuilder(PlaceFormType::class, $place)->getForm();
            
            $form->handleRequest($request);// Set data into the form
            
            if ($form->isValid() && $form->isSubmitted()) {
                
                // Save the object
                $em->flush();
                
                $request->getSession()->getFlashBag()->add('success', $translator->trans("place_updated"));
            }
        }
        
        $params = array(
            "form"          => ($form == null ? null : $form->createView()),
            "place"         => $place,
            "lastEvents"    => $lastEvents
        );
        
        return $this->render('AppBundle:SuperAdmin:place.html.twig', $params);
    }
    
    public function newslettersAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $translator = $this->get("translator");
        $repoNewsletter = $em->getRepository("AppBundle:Newsletter");
        
        $deleteId = trim($request->get("deleteId"));
        
        if(!empty($deleteId)) {
            $newsletter = $repoNewsletter->find($deleteId);
            
            if($newsletter != null) {
                $em->remove($newsletter);
                $em->flush();
                
                $request->getSession()->getFlashBag()->add('success', $translator->trans("newsletter_deleted"));
            }
        }
        
        $newNewsletter = new Newsletter();
        $formAdd = $this->get('form.factory')->createBuilder(NewsletterFormType::class, $newNewsletter)->getForm();
        
        // Add form
        if ($request->isMethod('POST')) {
            
            $formAdd->handleRequest($request);
            
            if ($formAdd->isValid()) {
                
                $tz = new \DateTimeZone($this->getParameter("time_zone"));
                $tzUTC = new \DateTimeZone("UTC");
                
                $start = clone $newNewsletter->getStartTime();
                
                $start->setTimezone($tz);
                $start->setTime(0, 0, 0);
                $start->setTimezone($tzUTC);
                $newNewsletter->setStartTime($start);
                
                $end = clone $newNewsletter->getEndTime();
                
                $end->setTimezone($tz);
                $end->setTime(23, 59, 59);
                $end->setTimezone($tzUTC);
                
                $newNewsletter->setEndTime($end);
                
                
                if ($newNewsletter->getStartTime() > $newNewsletter->getEndTime()) {
                    $request->getSession()->getFlashBag()->add('warning', $translator->trans("error_event_dates_order"));
                }
                else {
                    
                    
                    $em->persist($newNewsletter);
                    $em->flush();
                    
                    $newNewsletter = new Newsletter();
                    $formAdd = $this->get('form.factory')->createBuilder(NewsletterFormType::class, $newNewsletter)->getForm();
                    
                    $request->getSession()->getFlashBag()->add('success', $translator->trans("newsletter_created"));
                }
            }
        }
        
        return $this->render('AppBundle:SuperAdmin:newsletters.html.twig', array(
            'newsletters'   => $repoNewsletter->findBy(array(), array("id" => "DESC"), 20),
            'formAdd'       => $formAdd->createView()
        ));
    }
    
    public function highlightInNewsletterAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $translator = $this->get("translator");
        $repoEvent = $em->getRepository("AppBundle:Event");
        $repoNewsletter = $em->getRepository("AppBundle:Newsletter");
        $repoNewsletterEvent = $em->getRepository("AppBundle:NewsletterEvent");
        
        $newsletter = $repoNewsletter->find($request->get("id"));
        $search = trim($request->get("search"));
        $addId = trim($request->get("addId"));
        $removeId = trim($request->get("removeId"));
        $upId = trim($request->get("upId"));
        $downId = trim($request->get("downId"));
        
        if(!empty($addId)) {
            // Add a highlight event
            $event = $repoEvent->find($addId);
            
            if($event != null) {
                
                $nbHighlights = count($newsletter->getNewsletterEvents());
                
                $highlightAlreadyExists = false;
                
                foreach($newsletter->getNewsletterEvents() as $newsletterEvent) {
                    if($newsletterEvent->getEvent()->getId() == $event->getId()) {
                        $highlightAlreadyExists = true;
                    }
                }
                
                if(!$highlightAlreadyExists) {
                    
                    $lastPosition = 0;
                    
                    if($nbHighlights > 0) {
                        $lastPosition = $newsletter->getNewsletterEvents()[$nbHighlights-1]->getPosition();
                    }
                    
                    $newsletterEvent = new NewsletterEvent();
                    $newsletterEvent->setPosition($lastPosition+1);
                    $newsletterEvent->setNewsletter($newsletter);
                    $newsletterEvent->setEvent($event);
                    $em->persist($newsletterEvent);
                    $em->flush();
                    
                    $request->getSession()->getFlashBag()->add('success', $translator->trans("event_added"));
                    $em->refresh($newsletter);
                }
            }
        }
        
        if(!empty($removeId)) {
            // Remove a highlight event
            
            $event = $repoEvent->find($removeId);
            
            if($event != null) {
                
                $newsletterEvents = $repoNewsletterEvent->findBy(array("newsletter" => $newsletter, "event" => $event));
                
                foreach($newsletterEvents as $newsletterEvent) {
                    
                    $em->remove($newsletterEvent);
                }
                
                if(count($newsletterEvents) > 0) {
                    $em->flush();
                    $em->refresh($newsletter);
                    
                    // Reset positions
                    $position = 1;
                    foreach($newsletter->getNewsletterEvents() as $newsletterEvent) {
                        $newsletterEvent->setPosition($position);
                        $position = $position + 1;
                    }
                    $em->flush();
                    $em->refresh($newsletter);
                    
                    $request->getSession()->getFlashBag()->add('success', $translator->trans("event_removed"));
                }
            }
        }
        
        if(!empty($upId)) {
            
            $event = $repoEvent->find($upId);
            
            if($event != null) {
                
                $currrentNewsletterEvent = $repoNewsletterEvent->findOneBy(array("newsletter" => $newsletter, "event" => $event));
                
                $newsletterEvent = $repoNewsletterEvent->findOneBy(array("newsletter" => $newsletter, "position" => $currrentNewsletterEvent->getPosition()-1));
                
                if($newsletterEvent != null) {
                    $newsletterEvent->setPosition($newsletterEvent->getPosition() + 1);
                }
                
                $currrentNewsletterEvent->setPosition($newsletterEvent->getPosition() - 1);
                
                $em->flush();
            }
        }
        
        if(!empty($downId)) {
            
            $event = $repoEvent->find($downId);
            
            if($event != null) {
                
                $currrentNewsletterEvent = $repoNewsletterEvent->findOneBy(array("newsletter" => $newsletter, "event" => $event));
                
                $newsletterEvent = $repoNewsletterEvent->findOneBy(array("newsletter" => $newsletter, "position" => $currrentNewsletterEvent->getPosition()+1));
                
                if($newsletterEvent != null) {
                    $newsletterEvent->setPosition($newsletterEvent->getPosition() - 1);
                }
                
                $currrentNewsletterEvent->setPosition($newsletterEvent->getPosition() + 1);
                
                $em->flush();
            }
        }
        
        $results = array();
        
        if(!empty($search)) {
            
            $ids = array();
            foreach($newsletter->getNewsletterEvents() as $newsletterEvent) {
                $ids[] = $newsletterEvent->getEvent()->getId();
            }
            
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
            ->andWhere($qb->expr()->gte("e.endTime", ":start"))
            ->andWhere($qb->expr()->like("e.name", $qb->expr()->literal("%".$search."%")))
            ->setParameter("published", true)
            ->setParameter("displayed", true)
            ->setParameter("start", $newsletter->getStartTime())
            ->orderBy("e.startTime", "ASC")
            ;
            
            if(count($ids) > 0) {
                $qb->andWhere($qb->expr()->notIn("e.id", ":ids"))
                ->setParameter("ids", $ids);
            }
            
            $results = $qb->getQuery()->getResult();
        }
        
        return $this->render('AppBundle:SuperAdmin:highlight_in_newsletter.html.twig', array(
            'newsletter'    => $newsletter,
            'results'       => $results
        ));
    }
}
