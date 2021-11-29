<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Form\AnnonceType;
use App\Repository\AdRepository;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminAdController extends AbstractController
{
    /**
     * Permet d'afficher l'ensemble des annonces dans l'administration
     * @Route("/admin/ads/{page<\d+>?1}", name="admin_ads_index")
     * @param PaginationService $pagination
     * @param int $page
     * @return Response
     */
    public function index(PaginationService $pagination, $page): Response
    {
        $pagination->setEntityClass(Ad::class)
                ->setPage($page)
                ->setLimit(10)
                ->setRoute('admin_ads_index');
   
        return $this->render('admin/ad/index.html.twig', [
            'pagination' => $pagination
        ]);
    }

    /**
     * Permet d'afficher le formulaire d'édition
     * @Route("/admin/ads/{id}/edit", name="admin_ads_edit")
     * @param Ad $ad
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function edit(Ad $ad, Request $request, EntityManagerInterface $manager)
    {
        $form = $this->createForm(AnnonceType::class, $ad);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($ad);
            $manager->flush();

            $this->addFlash(
                'success',
                "L'annonce <strong>{$ad->getTitle()}</strong> a bien été modifée"
            );
        }

        return $this->render("admin/ad/edit.html.twig",[
            'ad' => $ad,
            'myForm' => $form->createView()
        ]);


    }

    /**
     * Permet de supprimer une annonce
     * @Route("/admin/ads/{id}/delete", name="admin_ads_delete")
     * @param Ad $ad
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function delete(Ad $ad, EntityManagerInterface $manager)
    {
        // on ne peut pas supprimer une annonce qui possède des réservations
        if(count($ad->getBookings()) > 0){
            $this->addFlash(
                'warning',
                "Vous ne pouvez pas supprimer l'annonce <strong>{$ad->getTitle()}</strong> car elle possède des réservations"
            );
        }else{
            $this->addFlash(
                'success',
                "L'annonce <strong>{$ad->getTitle()}</strong> a bien été supprimée"
            );
            $manager->remove($ad);
            $manager->flush();
        }

        return $this->redirectToRoute('admin_ads_index');

    }

}
