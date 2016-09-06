<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Association;
use AppBundle\Entity\Event;
use AppBundle\Form\AssociationFormType;
use AppBundle\Form\SuperAdminEventFormType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SuperAdminController extends Controller
{

    public function eventsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $translator = $this->get("translator");
        $repoEvent = $em->getRepository("AppBundle:Event");
        $newEvent = new Event();
        $formAdd = $this->get('form.factory')->createBuilder(SuperAdminEventFormType::class, $newEvent)->getForm();

        $eventIdToUpdate = $request->get("eventId");
        $deleteId = $request->get("deleteId");

        // Add form
        if ($request->isMethod('POST') && empty($eventIdToUpdate)) {

            $formAdd->handleRequest($request);

            if ($formAdd->isValid()) {

                if ($newEvent->getStartTime() > $newEvent->getEndTime())
                    $request->getSession()->getFlashBag()->add('warning', $translator->trans("error_event_dates_order"));
                else {
                    $em->persist($newEvent);
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
        
        $events = $repoEvent->findBy(array(), array("id" => "DESC"));

        $forms = array();
        foreach($events as $event) {
            $formBuilder = $this->get('form.factory')->createBuilder(SuperAdminEventFormType::class, $event);

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

        return $this->render('AppBundle:SuperAdmin:events.html.twig', array(
            "formAdd" => $formAdd->createView(),
            "forms" => $forms,
            "events" => $events,
        ));
    }

    public function usersAction()
    {
        $em = $this->getDoctrine()->getManager();
        $repoUser = $em->getRepository("AppBundle:User");

        $users = $repoUser->findBy(array(), array("username" => "ASC"));

        return $this->render('AppBundle:SuperAdmin:users.html.twig', array(
            "users" => $users,
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

                if ($formAdd->isValid()) {
                    $em->persist($newAssociation);
                    $em->flush();
                    
                    $newAssociation->uploadPicture();

                    $request->getSession()->getFlashBag()->add('success', $translator->trans("association_created"));

                    // Empty the form
                    $newAssociation = new Association();
                    $formAdd = $this->get('form.factory')->createBuilder(AssociationFormType::class, $newAssociation)->getForm();
                }
                else
                    $request->getSession()->getFlashBag()->add('warning', $translator->trans("error_field"));
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

                        if ($form->isValid()) {
                            
                            $association->uploadPicture();
                            $em->flush();

                            $request->getSession()->getFlashBag()->add('success', $translator->trans("association_updated"));
                        }
                        else
                            $request->getSession()->getFlashBag()->add('warning', $translator->trans("error_field"));
                    }
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
}
