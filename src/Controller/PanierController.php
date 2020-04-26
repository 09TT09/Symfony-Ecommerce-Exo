<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Produit;
use App\Entity\Panier;
use App\Entity\ContenuPanier;
use App\Form\PanierType;
use Symfony\Component\HttpFoundation\Request;

class PanierController extends AbstractController
{
    /**
     * @Route("/panier", name="panier")
     */
    public function index(Request $request)
    {
        $em = $this->getDoctrine()->getManager(); 
        $panier = new Panier();
        $form = $this->createForm(PanierType::class, $panier);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em->persist($panier);
            $em->flush();
        }

        $contenupaniers = $em->getRepository(ContenuPanier::class)->findAll();
        $paniers = $em->getRepository(Panier::class)->findAll();
        return $this->render('panier/index.html.twig', [
            'paniers' => $paniers,
            'contenupaniers' => $contenupaniers,
            'form_ajout' => $form->createView()
        ]);	
    }
	
    /**
    * @Route("/panier/delete/{id}", name="panier_delete")
    */
    public function delete(ContenuPanier $contenupanier=null){
        if ($contenupanier != null){
            $em = $this->getDoctrine()->getManager();
            $em->remove($contenupanier);
            $em->flush();
            $this->addFlash("success","message.succes");
        }
    return $this->redirectToRoute('panier');
    }
}
