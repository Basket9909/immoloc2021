<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Entity\Image;
use App\Form\AnnonceType;
use App\Repository\AdRepository;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdController extends AbstractController
{
    /**
     * Permet d'afficher l'ensemble des annonces 
     * @Route("/ads/{page<\d+>?1}", name="ads_index")
     */
    public function index(PaginationService $pagination, $page): Response
    {

        $pagination->setEntityClass(Ad::class)
                ->setPage($page)
                ->setLimit(9)
                ;

        return $this->render('ad/index.html.twig', [
            'pagination' => $pagination
        ]);
    }


    /**
     * Permet de créer une annonce
     * @Route("/ads/new", name="ads_create")
     * @IsGranted("ROLE_USER")
     * @return Response
     */
    public function create(Request $request, EntityManagerInterface $manager){

        $ad = new Ad();
       
        $form = $this->createForm(AnnonceType::class, $ad);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            //gestion des images
            foreach($ad->getImages() as $image){
                $image->setAd($ad);
                $manager->persist($image);
            }

            // on ajoute l'auteur mais attention risque de bug jusqu'a security
            $ad->setAuthor($this->getUser());

            $manager->persist($ad);
            $manager->flush();

            $this->addFlash(
                'success',
                "L'annonce <strong>{$ad->getTitle()}</strong> a bien été enregistrée! "
            );
   
            return $this->redirectToRoute('ads_show',[
                'slug' => $ad->getSlug()
            ]);

        }



        return $this->render("ad/new.html.twig",[
            'myForm' => $form->createView()
        ]);
    }

    /**
     * Permet de modifier une annonce
     * @Route("/ads/{slug}/edit", name="ads_edit")
     * @Security("( is_granted('ROLE_USER') and user === ad.getAuthor() ) or is_granted('ROLE_ADMIN')", message="Cette annonce ne vous appartient pas, vous ne pouvez pas la modifier")
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param Ad $ad
     * @return Response
     */
    public function edit(Request $request, EntityManagerInterface $manager, Ad $ad)
    {
        $form = $this->createForm(AnnonceType::class, $ad);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            foreach($ad->getImages() as $image)
            {
                $image->setAd($ad);
                $manager->persist($image);
            }

            $manager->persist($ad);
            $manager->flush();

            $this->addFlash(
                'success',
                "L'annonce <strong>{$ad->getTitle()}</strong> a bien été modifiée!"
            );

        }

        return $this->render("ad/edit.html.twig",[
            "ad" => $ad,
            "myForm" => $form->createView()
        ]);

    }


    /**
     * Permet de supprimer une annonce
     * @Route("/ads/{slug}/delete", name="ads_delete")
     * @Security("( is_granted('ROLE_USER') and user === ad.getAuthor() ) or is_granted('ROLE_ADMIN')", message="Cette annonce ne vous appartient pas, vous ne pouvez pas la modifier")
     * @param Ad $ad
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function delete(Ad $ad, EntityManagerInterface $manager)
    {
        $this->addFlash(
            'success',
            "L'annonce <strong>{$ad->getTitle()}</strong> a bien été supprimée"
        );

        $manager->remove($ad);
        $manager->flush();

        return $this->redirectToRoute('ads_index');
    }


    /**
     * Permet d'afficher une seule annonce
     * @Route("/ads/{slug}", name="ads_show")
     * @return Response
     */
    public function show($slug, Ad $ad)
    {

        // $repo = $this->getDoctrine()->getRepository(Ad::class);
        // $ad = $repo->findOneBySlug($slug);

        return $this->render('ad/show.html.twig',[
            'ad' => $ad
        ]);
    }

    
}
