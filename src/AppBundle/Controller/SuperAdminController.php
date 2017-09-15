<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Association;
use AppBundle\Entity\Event;
use AppBundle\Entity\User;
use AppBundle\Form\AssociationFormType;
use AppBundle\Form\SuperAdminEventFormType;
use AppBundle\Form\UserFormType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\QuizScore;
use AppBundle\Form\QuizScoreFormType;
use AppBundle\Form\ProfileFormType;
use AppBundle\Form\ChangePasswordFormType;

class SuperAdminController extends Controller
{

    public function eventsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $translator = $this->get("translator");
        $repoEvent = $em->getRepository("AppBundle:Event");
        $action = $request->get("action");
        $eventId = $request->get("eventId");
        $deleteId = $request->get("deleteId");
        $isDuplicatingEvent = false;
        
        $newEvent = new Event();
        
        if(!empty($action) && $action == "duplicate" && !empty($eventId)) {
        	// Duplicate an action
        	
        	$eventToDuplicate = $repoEvent->find($eventId);
        	
        	if($eventToDuplicate != null) {
        		$isDuplicatingEvent = true;
        		
        		$newEvent->setAssociation($eventToDuplicate->getAssociation());
        		$newEvent->setCity($eventToDuplicate->getCity());
        		$newEvent->setDescription($eventToDuplicate->getDescription());
        		$newEvent->setEndTime($eventToDuplicate->getEndTime());
        		$newEvent->setFile($eventToDuplicate->getFile());
        		$newEvent->setFree($eventToDuplicate->getFree());
        		$newEvent->setLatitude($eventToDuplicate->getLatitude());
        		$newEvent->setLongitude($eventToDuplicate->getLongitude());
        		$newEvent->setName($eventToDuplicate->getName());
        		$newEvent->setPicture($eventToDuplicate->getPicture());
        		$newEvent->setPlace($eventToDuplicate->getPlace());
        		$newEvent->setPricing($eventToDuplicate->getPricing());
        		$newEvent->setPublished($eventToDuplicate->isPublished());
        		$newEvent->setSearchVolunteers($eventToDuplicate->getSearchVolunteers());
        		$newEvent->setStartTime($eventToDuplicate->getStartTime());
        		$newEvent->setStreet($eventToDuplicate->getStreet());
        		$newEvent->setWebsite($eventToDuplicate->getWebsite());
        		$newEvent->setZipCode($eventToDuplicate->getZipCode());
        	}
        }
        
        $formAdd = $this->get('form.factory')->createBuilder(SuperAdminEventFormType::class, $newEvent)->getForm();

        // Add form
        if ($request->isMethod('POST') && empty($action) && empty($deleteId)) {

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

        $forms = array();
        foreach($allEvents as $event) {
            $formBuilder = $this->get('form.factory')->createBuilder(SuperAdminEventFormType::class, $event);

            $form = $formBuilder->getForm();

            if ($request->isMethod('POST')) {

                if(!empty($action) && $action == "update" && !empty($eventId) && $eventId == $event->getId()) {

                    $form->handleRequest($request);// Set new data into the form

                    if ($form->isValid()) {

                        if ($event->getStartTime() > $event->getEndTime())
                            $request->getSession()->getFlashBag()->add('warning', $translator->trans("error_event_dates_order"));
                        else {
        					
       						// Erase pricing field if it's free
        					if($event->getFree())
        						$event->setPricing(null);
        					
                            $event->uploadPicture();
                            
                            // Save the object event
                            $em->flush();
                            

                            $formBuilder = $this->get('form.factory')->createBuilder(SuperAdminEventFormType::class, $event);
                            $form = $formBuilder->getForm();

                            $request->getSession()->getFlashBag()->add('success', $translator->trans("event_updated"));
                        }
                    }
                }
            }

            $formView = $form->createView();
            
            $forms[] = $formView;
        }

        return $this->render('AppBundle:SuperAdmin:events.html.twig', array(
            "formAdd"				=> $formAdd->createView(),
            "forms"					=> $forms,
            "events"				=> $events,
        	"passedEvents"			=> $passedEvents,
        	"allEvents"				=> $allEvents,
        	"isDuplicatingEvent"	=> $isDuplicatingEvent
        ));
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
        $formAdd = $this->get('form.factory')->createBuilder(AssociationFormType::class, $newAssociation)->getForm();

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
                $formAdd = $this->get('form.factory')->createBuilder(AssociationFormType::class, $newAssociation)->getForm();
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

        $associations = $repoAssociation->findBy(array(), array("name" => "ASC"));

        $forms = array();
        foreach($associations as $association) {
            $formBuilder = $this->get('form.factory')->createBuilder(AssociationFormType::class, $association);

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
}
