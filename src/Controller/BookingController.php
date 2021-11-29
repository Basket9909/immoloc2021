<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Entity\Booking;
use App\Entity\Comment;
use App\Form\BookingType;
use App\Form\CommentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BookingController extends AbstractController
{
    /**
     * @Route("/ads/{slug}/book", name="booking_create")
     * @IsGranted("ROLE_USER")
     * @param Ad $ad
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function book(Ad $ad, Request $request, EntityManagerInterface $manager): Response
    {
        $booking = new Booking();
        // Pour choisir les groupes de validation, on peut passer directement dans le controller
        // $form = $this->createForm(BookingType::class, $booking,[
        //     "validation_groups" => ["Default","front"]
        // ]);

        $form = $this->createForm(BookingType::class, $booking);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            // traitement
            $user = $this->getUser();
            $booking->setBooker($user)
                ->setAd($ad);

            // si les dates ne sont pas disponible, message d'erreur
            if(!$booking->isBookableDates()){
                $this->addFlash(
                    'warning',
                    'Les dates que vous avez choisie ne peuvent être réservées: elles sont déjà prises!'
                );
            }else{
                // sinon enregistrement et redirection
                $manager->persist($booking);
                $manager->flush();

                // à faire 
                return $this->redirectToRoute('booking_show',['id' => $booking->getId(), 'withAlert' => true]);
            }    
        }
        
        return $this->render('booking/book.html.twig', [
            'ad' => $ad,
            'myForm' => $form->createView()
        ]);
    }

    /**
     * Permet d'afficher la page d'une réservation
     * @Route("/booking/{id}", name="booking_show")
     * @IsGranted("ROLE_USER")
     * @param Booking $booking
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function show(Booking $booking, Request $request, EntityManagerInterface $manager)
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $comment->setAd($booking->getAd())
                    ->setAuthor($this->getUser());
            $manager->persist($comment);
            $manager->flush();
            
            $this->addFlash(
                'success',
                'Votre comentaire a bien été pris en compte'
            );
        }

        return $this->render("booking/show.html.twig",[
            'booking' => $booking,
            'myForm' => $form->createView()
        ]);
    }
}
