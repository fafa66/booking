<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Form\AnnounceType;
use App\Repository\AdRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminAdController extends AbstractController
{
    /**
     * @Route("/admin/ads", name="admin_ads_list")
     */
    public function index(AdRepository $repo)
    {
        return $this->render('admin/ad/index.html.twig', [
            'ads' => $repo->findAll()
        ]);
    }

    /**
     * Permet de modifier une annonce dans la partie admin
     * @Route("/admin/ads/{id}/edit", name="admin_ads_edit")
     * @param Ad $ad
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function edit(Ad $ad,Request $request,EntityManagerInterface $manager)
    {
        $form = $this->createForm(AnnounceType::class,$ad);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $manager->persist($ad);
            $manager->flush();

            $this->addFlash("success","L'annonce a bien été modifiée");
        }

        return $this->render('admin/ad/edit.html.twig',[
            'ad' => $ad,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Suppression d'un annonce
     * @Route("/admin/ads/{id}/delete", name="admin_ads_delete")
     * @param Ad $ad
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function delete(Ad $ad,EntityManagerInterface $manager)
    {
        if(count($ad->getBookings()) > 0){

            $this->addFlash("warning","Vous ne pouvez pas supprimer une annonce qui posséde des réservations");

        } else {
            $manager->remove($ad);
            $manager->flush();
            $this->addFlash("success","L'annonce <strong>{$ad->getTitle()}</strong> a bien été supprimée !");    
        }
        return $this->redirectToRoute("admin_ads_list");
    }
}