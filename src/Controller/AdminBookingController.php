<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Service\Pagination;
use App\Form\AdminBookingType;
use App\Repository\BookingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminBookingController extends AbstractController
{
    /**
     * @Route("/admin/bookings/{page<\d+>?1}", name="admin_bookings_list")
     */
    public function index(BookingRepository $repo,$page,Pagination $paginationService)
    {
        $paginationService->setEntityClass(Booking::class)
                          ->setPage($page);
                          //->setRoute('admin_comments_list')

        return $this->render('admin/booking/index.html.twig', [
            'pagination' => $paginationService
        ]);
    }

    /**
     * Edition d'une réservation
     * @Route("/admin/booking/{id}/edit", name="admin_bookings_edit")
     * @param Booking $booking
     * @param Request $request
     * @param EntityManagerInterface $manager
     */
    public function edit(Booking $booking,Request $request,EntityManagerInterface $manager)
    {
        $form = $this->createForm(AdminBookingType::class,$booking);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            //$booking->setAmount($booking->getAd()->getPrice() * $booking->getDuration());
            $booking->setAmount(0);
            $manager->persist($booking);
            $manager->flush();

            $this->addFlash("success","La réservation a bien été modifiée");
        }

        return $this->render('admin/booking/edit.html.twig',[
            'booking' => $booking,
            'adminBooking' => $form->createView()
        ]);
    }

    /**
     * Suppression d'une réservation
     * @Route("/admin/bookings/{id}/delete", name="admin_bookings_delete")
     * @param Booking $booking
     * @param EntityManagerInterface $manager
     */
    public function delete(Booking $booking,EntityManagerInterface $manager)
    {
        $manager->remove($booking);
        $manager->flush();
        $this->addFlash('success',"La réservation n°{$booking->getId()} a bien été supprimée !");

        return $this->redirectToRoute('admin_bookings_list');
    }
}
