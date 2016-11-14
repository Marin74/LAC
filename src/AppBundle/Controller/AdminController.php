<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Event;
use AppBundle\Form\AssociationFormType;
use AppBundle\Form\EventFormType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\DocumentFormType;
use AppBundle\Entity\Document;

class AdminController extends Controller
{
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $translator = $this->get("translator");
        $repoDocument = $em->getRepository("AppBundle:Document");
        $appName = $this->container->getParameter("app_name");
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $form = $this->get('form.factory')->createBuilder(AssociationFormType::class, $user->getAssociation())->getForm();
        $formDocument = null;
        $newDocument = null;
        
        $deleteId = $request->get("deleteId");
        
        // If the user is the owner of the admin association, he can add document (other associations can't)
        if($user->getAssociation() != null
        		&& !empty($appName)
        		&& $user->getAssociation()->getName() == $appName) {
        	
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
            "form" => $form->createView(),
        	"formDocument" => $formDocument == null ? null: $formDocument->createView(),
        ));
    }

    public function eventsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $translator = $this->get("translator");
        $repoEvent = $em->getRepository("AppBundle:Event");
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $newEvent = new Event();
        $formAdd = $this->get('form.factory')->createBuilder(EventFormType::class, $newEvent)->getForm();
        $events = array();
        $forms = array();

        $eventIdToUpdate = $request->get("eventId");
        $deleteId = $request->get("deleteId");
        
        if($user->getAssociation() != null) {
        	
        	if ($request->isMethod('POST') && empty($eventIdToUpdate)) {
        	
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
        	
        	$events = $user->getAssociation()->getEvents();
        	
        	foreach($events as $event) {
        		$formBuilder = $this->get('form.factory')->createBuilder(EventFormType::class, $event);
        	
        		$form = $formBuilder->getForm();
        	
        	
        		if ($request->isMethod('POST')) {
        	
        			if(!empty($eventIdToUpdate) && $eventIdToUpdate == $event->getId()) {
        	
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

        							$formBuilder = $this->get('form.factory')->createBuilder(EventFormType::class, $event);
        							$form = $formBuilder->getForm();
        	
        							$request->getSession()->getFlashBag()->add('success', $translator->trans("event_updated"));
        						}
        				}
        			}
        		}
        	
        		$formView = $form->createView();
        	
        		$forms[] = $formView;
        	}
        }

        return $this->render('AppBundle:Admin:events.html.twig', array(
            "formAdd" => $formAdd->createView(),
            "forms" => $forms,
            "events" => $events,
        ));
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
}
