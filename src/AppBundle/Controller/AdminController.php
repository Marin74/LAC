<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Event;
use AppBundle\Form\AssociationFormType;
use AppBundle\Form\EventFormType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends Controller
{
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $translator = $this->get("translator");
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $form = $this->get('form.factory')->createBuilder(AssociationFormType::class, $user->getAssociation())->getForm();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {

                $user->getAssociation()->uploadPicture();
                $em->flush();

                $request->getSession()->getFlashBag()->add('success', $translator->trans("association_updated"));
            }
            else
                $request->getSession()->getFlashBag()->add('warning', $translator->trans("error_field"));
        }

        return $this->render('AppBundle:Admin:index.html.twig', array(
            "form" => $form->createView(),
        ));
    }

    public function eventsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $translator = $this->get("translator");
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $newEvent = new Event();
        $formAdd = $this->get('form.factory')->createBuilder(EventFormType::class, $newEvent)->getForm();
        $events = array();
        $forms = array();

        $eventIdToUpdate = $request->get("eventId");
        
        if($user->getAssociation() != null) {
        	
        	if ($request->isMethod('POST') && empty($eventIdToUpdate)) {
        	
        		$newEvent->setAssociation($user->getAssociation());
        	
        		$formAdd->handleRequest($request);
        	
        		if ($formAdd->isValid()) {
        	
        			if ($newEvent->getStartTime() > $newEvent->getEndTime())
        				$request->getSession()->getFlashBag()->add('warning', $translator->trans("error_event_dates_order"));
        				else {
        					$em->persist($newEvent);
        					$em->flush();
        					
                    		$newEvent->uploadPicture();
                    		$em->flush();
        	
        					$newEvent->uploadPicture();
        	
        					$newEvent = new Event();
        					$formAdd = $this->get('form.factory')->createBuilder(EventFormType::class, $newEvent)->getForm();
        	
        					$request->getSession()->getFlashBag()->add('success', $translator->trans("event_created"));
        				}
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
        							$event->uploadPicture();
        	
        							// Save the object event
        							$em->flush();
        	
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
}
