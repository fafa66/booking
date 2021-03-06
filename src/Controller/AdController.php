<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Form\AnnounceType;
use App\Repository\AdRepository;
use App\Service\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdController extends AbstractController
{
    /**
     * Permet d'afficher une liste d'annonces
     * @Route("/ads/{page<\d+>?1}", name="ads_list")
     */
    public function index(AdRepository $repo,$page,Pagination $paginationService)
    {
        $paginationService->setEntityClass(Ad::class)
                          ->setPage($page);
        
        return $this->render('ad/index.html.twig', [
            'pagination' => $paginationService
        ]);
    }

    /**
     * Permet de créer une annonce
     * @Route("/ads/new", name="ads_create")
     * @IsGranted("ROLE_USER")
     * @return Response
     */
    public function create(Request $request,EntityManagerInterface $manager)
    {
        $ad = new Ad();

        $form = $this->createForm(AnnounceType::class,$ad); // Création
        $form->handleRequest($request);                    // Récupération des données

        if($form->isSubmitted() && $form->isValid()){

            // Pour chaque image supplémentaire ajoutée
            foreach($ad->getImages() as $image){

                // on relie l'image à l'annonce et on modifie l'annonce
                $image->setAd($ad);

                // on sauvegarde les images
                $manager->persist($image);
            }
            $ad->setAuthor($this->getUser());  
            $manager->persist($ad);
            $manager->flush();

            $this->addFlash('success',"Annonce <strong>{$ad->getTitle()}</strong> créée avec succès");

            return $this->redirectToRoute('ads_single',[
                'slug'=>$ad->getSlug()
                ]);
        }            
        return $this->render('ad/new.html.twig',[
            'form' => $form->createView()
        ]);
    }

    /**
     * Permet d'afficher une seule annonce
     * @Route("/ads/{slug}", name="ads_single")
     * @return Response
     */
    public function show(Ad $ad)
    {
        // via $repo on va chercher une annonce par son slug
        // $ad = $repo->findOneBySlug($slug);
        // ↑↑↑ plus besoin de Adrepossitory, $repo, $slug si on met dans la function la class(Ad) et la variable($ad)
        // ParamConverter fait la relation si attr de requête {slug} avec la classe Ad et est converti,stocké dans la variable $ad  

        return $this->render('ad/show.html.twig',[
            'ad' => $ad
        ]);
    }

    /**
     * Permet d'éditer et de modifier une annonce
     * @Route("/ads/{slug}/edit", name="ads_edit")
     * @Security("is_granted('ROLE_USER') and user === ad.getAuthor()",message=                                                       "Cette annonce ne vous appartient pas vous ne pouvez pas la modifier")
     * @return Response
     */
    public function edit(Ad $ad,Request $request,EntityManagerInterface $manager)
    {
        $form = $this->createForm(AnnounceType::class,$ad);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            foreach($ad->getImages() as $image){
                $image->setAd($ad);
                $manager->persist($image);
            }
            $manager->persist($ad);
            $manager->flush();

            $this->addFlash('success',"Les modifications ont été faîtes !");
            return $this->redirectToRoute('ads_single',[
                'slug' => $ad->getSlug()]);
        }

        return $this->render('ad/edit.html.twig',[
            'form' => $form->createView(),
            'ad' => $ad,
            'data_class' => null
        ]);
    }

    /**
     * Permet de supprimer une annonce
     * @Route("/ads/{slug}/delete", name="ads_delete")
     *
     * @param Ad $ad
     * @param EntityManagerInterface $manager
     * @return void
     */
    public function delete(Ad $ad,EntityManagerInterface $manager)
    {
        $manager->remove($ad);
        $manager->flush();
        $this->addFlash("success","L'annonce <em>{$ad->getTitle()}</em> a bien été supprimée");

        return $this->redirectToRoute('ads_list');
    }

}
