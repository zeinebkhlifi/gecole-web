<?php
/**
 * Created by PhpStorm.
 * User: arafe
 * Date: 16/02/2020
 * Time: 12:21
 */

namespace EventBundle\Controller;


use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use EventBundle\Entity\Club;
use EventBundle\Entity\Event;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("")
 */
class EvenementController extends Controller
{

    /**
     * @Route("/event/", name="event_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $all = $this->getDoctrine()->getManager()->getRepository('EventBundle:Event')->findAll();
        $events = new ArrayCollection();
        $villes = new ArrayCollection();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        foreach ($all as $e) {
            if (($e->getDate() > new  \DateTime('now')) // Date mazelet mat3adetch
                && ( ($e->getParticipants()->count() <= $e->getNbrPlaces()) // mazel famma blassa
                    || $e->getParticipants()->contains($user) )) { // walla l user connecté ykoun mel participant, bech ynajem y annuliiis
                $events->add($e);
                $villes->add($e->getLieu());
            }
        }

        return $this->render('@Event/Event/index.html.twig', array(
            'events' => $events,
            'villes' => $villes,
        ));
    }

    /**
     * @Route("/event/add", name="event_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $club = new Event();
        $formClub = $this->createForm('EventBundle\Form\EventType', $club);
        $formClub->handleRequest($request);

        $club->setCreatedBy($user);

        if ($formClub->isSubmitted() && $formClub->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($club);
            $em->flush();
            return $this->redirectToRoute('event_index');
        }
        return $this->render('@Event/Event/new.html.twig', array(
            'form' => $formClub->createView(),
            'user' => $user
        ));

    }

    /**
     *
     * @Route("/dashboard/event/", name="event_dashboard_index")
     * @Method("GET")
     */
    public function indexDashboardAction()
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            $events = $this->getDoctrine()->getManager()->getRepository('EventBundle:Event')->findAll();
        } else if ($this->get('security.authorization_checker')->isGranted('ROLE_RESPONSABLE')) {
            $events = $this->getDoctrine()->getManager()->getRepository('EventBundle:Event')->findBy(['responsable' => $user]);
        } else {
            throw new AccessDeniedException("Vous n'êtes pas autorisés à accéder à cette page!", Response::HTTP_FORBIDDEN);
        }
        return $this->render('@Event/Event/dashboard/index.html.twig', array(
            'events' => $events,
        ));
    }

    /**
     * @Route("/dashboard/event/{id}", name="event_dashboard_show")
     * @Method("GET")
     */
    public function showDashboardAction(Event $event)
    {
        $club = $this->getDoctrine()->getManager()->getRepository('EventBundle:Club')->findOneBy([
            'createdBy' => $event->getCreatedBy()
        ]);
        return $this->render('@Event/Event/dashboard/eventShow.html.twig', array(
            'club' => $club,
            'event' => $event,
        ));
    }

    /**
     * @Route("/event/{id}", name="event_show")
     * @Method("GET")
     */
    public function showAction(Event $event)
    {
        $club = $this->getDoctrine()->getManager()->getRepository('EventBundle:Club')->findOneBy([
            'createdBy' => $event->getCreatedBy()
        ]);
        return $this->render('@Event/Event/Show.html.twig', array(
            'club' => $club,
            'event' => $event,
        ));
    }

    /**
     * @Route("/event/delete/{id}", name="event_delete")
     * @Method({"GET", "DELETE"})
     */
    public function deleteEventAction(Request $request, $id)
    {
        $ev = $this->getDoctrine()->getRepository('EventBundle:Event')->find($id);
        $em = $this->getDoctrine()->getManager();

        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')
            || $this->get('security.authorization_checker')->isGranted('ROLE_RESPONSABLE')) {
            $em->remove($ev);
            $em->flush();
            return $this->redirectToRoute('club_dashboard_index');
        } else {
            throw new AccessDeniedException("Vous n'êtes pas autorisés à supprimer cet événement!", Response::HTTP_FORBIDDEN);
        }
    }


    /**
     * @Route("/evenement/oui/ajax", name="front_event_participer_ajax")
     * @Method({"GET", "POST"})
     */
    public function participerAjaxAction(Request $request)
    {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $evenement = $em->getRepository('EventBundle:Event')->find($id);
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $user->addEventsParticipes($evenement);
        $evenement->addParticipant($user);
        $evenement->setNbrParticipants($evenement->getNbrParticipants() + 1);
        $em->persist($evenement);
        $em->persist($user);
        $em->flush();
        $events = new ArrayCollection();
        $all = $em->getRepository('EventBundle:Event')->findAll();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        foreach ($all as $e) {
            if (($e->getDate() > new  \DateTime('now')) // Date mazelet mat3adetch
                && ( ($e->getParticipants()->count() <= $e->getNbrPlaces()) // mazel famma blassa
                    || $e->getParticipants()->contains($user) )) { // walla l user connecté ykoun mel participant, bech ynajem y annuliiis
                $events->add($e);
            }
        }

        $template = $this->render(
            '@Event/Event/allEvents.html.twig',
            [
                'events' => $events,
            ]
        )->getContent();

        $json = json_encode($template);
        $response = new Response($json, 200);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }


    /**
     * @Route("/evenement/no/ajax/", name="front_event_no_participer_ajax")
     * @Method("GET")
     */
    public function noParticiperAjaxAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $evenement = $em->getRepository('EventBundle:Event')->find($id);
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $evenement->removeParticipant($user);
        $evenement->setNbrParticipants($evenement->getNbrParticipants() - 1);
        $em->persist($evenement);
        $em->persist($user);
        $em->flush();

        $all = $em->getRepository('EventBundle:Event')->findAll();
        $events = new ArrayCollection();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        foreach ($all as $e) {
            if (($e->getDate() > new  \DateTime('now')) // Date mazelet mat3adetch
                && ( ($e->getParticipants()->count() <= $e->getNbrPlaces()) // mazel famma blassa
                    || $e->getParticipants()->contains($user) )) { // walla l user connecté ykoun mel participant, bech ynajem y annuliiis
                $events->add($e);
            }
        }
        $template = $this->render(
            '@Event/Event/allEvents.html.twig',
            [
                'events' => $events,
            ]
        )->getContent();

        $json = json_encode($template);
        $response = new Response($json, 200);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Displays a form to edit an existing user entity.
     *
     * @Route("/dashboard/event/{id}/edit", name="event_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Event $event)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if ((!$user->hasRole('ROLE_ADMIN')) || ($user != $event->getCreatedBy())) {
            throw new AccessDeniedException("Vous n'êtes pas autorisés à accéder à cette page!", Response::HTTP_FORBIDDEN);
        }
        $editForm = $this->createForm('EventBundle\Form\EventType', $event);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('club_dashboard_index'); // club_index : dashboard
        }

        return $this->render('@Event/Event/dashboard/edit.html.twig', array(
            'event' => $event,
            'edit_form' => $editForm->createView(),
        ));
    }

    /**
     *
     * @Route("/rechercheEventByCriteria", name="event_all_recherche")
     * @Method({"GET", "POST"})
     */
    public function rechercheByAllCriteriaAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $ville = $request->get('ville');
        $type = $request->get('type');
        $keyword = $request->get('keyword');
        $events = $em->getRepository('EventBundle:Event')->findAll();

        if ($type == null && $ville) {
            $events = $em->getRepository('EventBundle:Event')->findEventByVille($ville, $keyword);

        } else if ($ville== null && $type){
            $events = $em->getRepository('EventBundle:Event')->findEventByType($type, $keyword);

        } else if ($ville == null && $type== null){
            $events = $em->getRepository('EventBundle:Event')->findEvent($keyword);
        } else {
            $events = $em->getRepository('EventBundle:Event')->findEventByTypeAndVille($type,$ville, $keyword);
        }


        $user = $this->get('security.token_storage')->getToken()->getUser();
        $evts = new ArrayCollection();
        foreach ($events as $e) {
            if  ($e->getParticipants()->count() <= $e->getNbrPlaces()
                    || $e->getParticipants()->contains($user) ) {
                $evts->add($e);
            }
        }

        $template = $this->render(
            '@Event/Event/allEvents.html.twig',
            [
                'events' => $evts,
            ]
        )->getContent();


        $json = json_encode($template);
        $response = new Response($json, 200);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }



}