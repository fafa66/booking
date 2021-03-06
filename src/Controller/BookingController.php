<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Entity\Booking;
use App\Entity\Comment;
use App\Form\BookingType;
use App\Form\CommentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BookingController extends AbstractController
{
    /**
     * Permet d'afficher le formulaire de réservation
     * @Route("/ads/{slug}/book", name="booking_create")
     * @IsGranted("ROLE_USER")
     * @return Response
     */
    public function book(Ad $ad,Request $request,EntityManagerInterface $manager)
    {
        $booking = new Booking();
        $form = $this->createForm(BookingType::class,$booking);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $user = $this->getUser(); // Utilisateur connecté
            $booking->setBooker($user)  // On relie le loueur à l'utilisateur
                    ->setAd($ad);      // On relie l'annonce au loueur

            // Si les dates ne sont pas disponibles
            if(!$booking->isBookableDays()){
                $this->addFlash("warning","Ces dates ne sont pas disponibles, choisissez d'autres dates pour votre séjour");
            }else{
                $manager->persist($booking);
                $manager->flush();
    
                return $this->redirectToRoute('booking_show',['id' => $booking->getId(),'alert'=>true]);
            }
        }

        return $this->render('booking/book.html.twig', [
            'ad' => $ad,
            'booking' => $form->createView()
        ]);
    }

    /**
     * Affiche une réservation
     * @Route("booking/{id}", name="booking_show")
     * @return Response
     */
    public function show(Booking $booking,Request $request,EntityManagerInterface $manager)
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class,$comment);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()){
            
            $comment->setAd($booking->getAd())  // On relie le commentaire 
                    ->setAuthor($this->getUser());

            $manager->persist($comment);
            $manager->flush();

            $this->addFlash("success","Votre commentaire a bien été enregistré");
        }

        return $this->render('booking/show.html.twig',[
            'booking' => $booking,
            'comment' => $form->createView()
        ]);
    }
}
