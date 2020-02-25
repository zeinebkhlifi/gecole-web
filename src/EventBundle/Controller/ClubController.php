<?php
/**
 * Created by PhpStorm.
 * User: arafe
 * Date: 15/02/2020
 * Time: 15:05
 */

namespace EventBundle\Controller;


use EventBundle\Entity\Club;
use EventBundle\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("")
 */
class ClubController extends Controller
{
    /**
     * @Route("/club/", name="club_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $clubs = $this->getDoctrine()->getManager()->getRepository('EventBundle:Club')->findBy([
            'etat'=> "confirme"
        ]);
        return $this->render('@Event/Club/index.html.twig', array(
            'clubs' => $clubs,
        ));
    }

    /**
     * @Route("/club/addClub", name="club_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $club     = new Club();
        $formClub = $this->createForm('EventBundle\Form\ClubType', $club);
        $formClub->handleRequest($request);

        $club->setCreatedBy($user);
        $club->setEtat('EnAttente');

        if ($formClub->isSubmitted() && $formClub->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($club);
            $em->flush();
            return $this->redirectToRoute('club_index');
        }
        return $this->render('@Event/Club/new.html.twig', array(
            'form' => $formClub->createView(),
        ));

    }


    /**
     * @Route("/club/{id}", name="club_show")
     * @Method("GET")
     */
    public function showAction(Club $club)
    {
        $club = $this->getDoctrine()->getManager()->getRepository('EventBundle:Club')->findOneBy([
            'createdBy'=> $club->getCreatedBy()
        ]);
        $eventsCreated = $this->getDoctrine()->getManager()->getRepository('EventBundle:Event')->findBy([
            'createdBy'=> $club->getCreatedBy()
        ]);
        return $this->render('@Event/Club/show.html.twig', array(
            'club' => $club,
            'eventsClub' => $eventsCreated,
        ));
    }

    /**
     *
     * @Route("/dashboard/club/", name="club_dashboard_index")
     * @Method("GET")
     */
    public function indexDashboardAction()
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            $clubs = $this->getDoctrine()->getManager()->getRepository('EventBundle:Club')->findAll();
            $events = $this->getDoctrine()->getManager()->getRepository('EventBundle:Event')->findAll();
        } else if ($this->get('security.authorization_checker')->isGranted('ROLE_RESPONSABLE')) {
            $clubs = $this->getDoctrine()->getManager()->getRepository('EventBundle:Club')->findBy(['responsable' => $user]);
            $events = $this->getDoctrine()->getManager()->getRepository('EventBundle:Event')->findBy(['responsable' => $user]);
        } else {
            throw new AccessDeniedException("Vous n'êtes pas autorisés à accéder à cette page!", Response::HTTP_FORBIDDEN);
        }
        return $this->render('@Event/Club/dashboard/index.html.twig', array(
            'clubs' => $clubs,
            'events' => $events,
        ));
    }

    /**
     * @Route("/confirmerClub/{id}", name="club_confirme")
     */
    public function confirmerClubAction(Request $request, $id)
    {
        $club = $this->getDoctrine()->getRepository('EventBundle:Club')->find($id);
        $em = $this->getDoctrine()->getManager();
        $club->setEtat("confirme");
        $club->setResponsable($club->getCreatedBy());

        $userResponsable = $club->getResponsable();
        $userResponsable->addRole('ROLE_RESPONSABLE');
        $em->flush();
        return $this->redirectToRoute('club_dashboard_index');
    }


    /**
     * @Route("/club/delete/{id}", name="club_delete")
     * @Method({"GET", "DELETE"})
     */
    public function deleteClubAction(Request $request, $id)
    {
        $c = $this->getDoctrine()->getRepository('EventBundle:Club')->find($id);
        $em = $this->getDoctrine()->getManager();

        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')
            || $this->get('security.authorization_checker')->isGranted('ROLE_RESPONSABLE') ) {
            $em->remove($c);
            $em->flush();
            return $this->redirectToRoute('club_dashboard_index');
        } else {
            throw new AccessDeniedException("Vous n'êtes pas autorisés à supprimer ce Club!", Response::HTTP_FORBIDDEN);
        }
    }

    /**
     * Displays a form to edit an existing user entity.
     *
     * @Route("/club/{id}/edit", name="club_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Club $club)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') || $user != $club->getCreatedBy()) {
            throw new AccessDeniedException("Vous n'êtes pas autorisés à accéder à cette page!", Response::HTTP_FORBIDDEN);
        }
        $editForm = $this->createForm('EventBundle\Form\ClubType', $club);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('club_dashboard_index'); // club_index : dashboard
        }

        return $this->render('@Event/Club/dashboard/edit.html.twig', array(
            'club' => $club,
            'edit_form' => $editForm->createView(),
        ));
    }

    /**
     *
     * @Route("/rechercheClubKeyword", name="club_keyword_recherche")
     * @Method({"GET", "POST"})
     */
    public function rechercheAction(Request $request)
    {
        $em      = $this->getDoctrine()->getManager();
        $keyWord = $request->get('keyWord');
        $clubs = $em->getRepository('EventBundle:Club')->findClub($keyWord);

        $template = $this->render(
            '@Event/Club/allClub.html.twig',
            [
                'clubs' => $clubs,
            ]
        )->getContent();

        $json     = json_encode($template);
        $response = new Response($json, 200);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     *
     * @Route("/rechercheClubByTypeKeyword", name="club_type_recherche")
     * @Method({"GET", "POST"})
     */
    public function rechercheParTypeAction(Request $request)
    {
        $em      = $this->getDoctrine()->getManager();
        $type = $request->get('type');
        $clubs = $em->getRepository('EventBundle:Club')->findClubByType($type);

        $template = $this->render(
            '@Event/Club/allClub.html.twig',
            [
                'clubs' => $clubs,
            ]
        )->getContent();

        $json     = json_encode($template);
        $response = new Response($json, 200);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

}