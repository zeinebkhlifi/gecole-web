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

        foreach($all as $e) {
            if ($e->getDate()> new  \DateTime('now')) {
                $events->add($e);
            }
            $villes->add($e->getLieu());

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

        $club     = new Event();
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
        $id        = $request->get('id');
        $em        = $this->getDoctrine()->getManager();
        $evenement = $em->getRepository('EventBundle:Event')->find($id);
        $user      = $this->get('security.token_storage')->getToken()->getUser();
        $user->addEventsParticipes($evenement);
        $evenement->addParticipant($user);
        $evenement->setNbrParticipants($evenement->getNbrParticipants() + 1);
        $em->persist($evenement);
        $em->persist($user);
        $em->flush();

        $events = $em->getRepository('EventBundle:Event')->findAll();
        $template = $this->render(
            '@Event/Event/allEvents.html.twig',
            [
                'events' => $events,
            ]
        )->getContent()
        ;

        $json     = json_encode($template);
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
        $evenement =  $em->getRepository('EventBundle:Event')->find($id);
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $evenement->removeParticipant($user);
        $evenement->setNbrParticipants($evenement->getNbrParticipants() - 1);
        $em->persist($evenement);
        $em->persist($user);
        $em->flush();

        $events = $em->getRepository('EventBundle:Event')->findAll();

        $template = $this->render(
            '@Event/Event/allEvents.html.twig',
            [
                'events' => $events,
            ]
        )->getContent();

        $json     = json_encode($template);
        $response = new Response($json, 200);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Displays a form to edit an existing user entity.
     *
     * @Route("/event/{id}/edit", name="event_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Event $event)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') || $user != $event->getCreatedBy()) {
            throw new AccessDeniedException("Vous n'êtes pas autorisés à accéder à cette page!", Response::HTTP_FORBIDDEN);
        }
        $editForm = $this->createForm('EventBundle\Form\EventType', $event);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('club_dashboard_index'); // club_index : dashboard
        }

        return $this->render('@Event/Event/edit.html.twig', array(
            'event' => $event,
            'edit_form' => $editForm->createView(),
        ));
    }


    /**
     *
     * @Route("/rechercheEventKeyword", name="event_keyword_recherche")
     * @Method({"GET", "POST"})
     */
    public function rechercheAction(Request $request)
    {
        $em      = $this->getDoctrine()->getManager();
        $keyWord = $request->get('keyWord');
        $events = $em->getRepository('EventBundle:Event')->findEvent($keyWord);

        $template = $this->render(
            '@Event/Event/allEvents.html.twig',
            [
                'events' => $events,
            ]
        )->getContent();

        $json     = json_encode($template);
        $response = new Response($json, 200);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}