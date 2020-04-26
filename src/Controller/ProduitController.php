<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Produit;
use App\Form\ProduitType;
use App\Entity\ContenuPanier;
use App\Form\ContenuPanierType;
use App\Entity\User;
use App\Entity\Panier;
use Symfony\Component\HttpFoundation\Request;

class ProduitController extends AbstractController
{
    /**
     * @Route("/", name="accueil")
     */
    public function index(Request $request){
        $em = $this->getDoctrine()->getManager(); 
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $fichier = $form->get('photo')->getData();
            if($fichier){
                $nomfichier = uniqid() .'.'. $fichier->guessExtension();
                try{
                    $fichier->move(
                        $this->getParameter('photo_dir'),
                        $nomfichier
                    );
                }
                catch(FileException $e){
                    return $this->redirectToRoute('accueil');
                }
                $produit->SetPhoto($nomfichier);
            }
        $em->persist($produit);
        $em->flush();
        $this->addFlash("success","message.succes");
        }
        
        $produits = $em->getRepository(Produit::class)->findAll();
        return $this->render('produit/index.html.twig', [
            'produits' => $produits,
            'form_ajout' => $form->createView()
        ]);
    }

    /**
     * @Route("/produit/{id}", name="produit_view")
     */
    public function produit(Produit $produit=null, User $user=null, Panier $panier=null, Request $request){
		
        $em = $this->getDoctrine()->getManager(); // récupère la bdd
		
        if($produit != null){ // si variable produit est différent de null faire...
			
			$contenupanier = new ContenuPanier(); // Création d'un nouveau contenupanier
			$panier = new Panier(); // Création d'un nouveau panier
			$user = $this->getUser(); //récupère l'utilisateur connecté

			$user->addPanier($panier); // ajoute l'id de l'utilisateur connecté dans panier.user_id

			$form2 = $this->createForm(ContenuPanierType::class, $contenupanier);
			$form2->handleRequest($request);
            if($form2->isSubmitted() && $form2->isValid()){
				$produit->addcontenupanier($contenupanier);
				$panier->addcontenupanier($contenupanier);
				$em->persist($contenupanier);
				$em->persist($panier);
				//$em->persist($produit);
                $em->flush();
            }

            $form = $this->createForm(ProduitType::class, $produit);
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                $fichier = $form->get('photo')->getData();
                if($fichier){
					if ($produit->getPhoto() != null){
						unlink($this->getParameter('photo_dir').$produit->getPhoto());
					}
                    $nomfichier = uniqid() .'.'. $fichier->guessExtension();
                    try{
                        $fichier->move(
                            $this->getParameter('photo_dir'),
                            $nomfichier
                        );
                    }
                    catch(FileException $e){
                        return $this->redirectToRoute('accueil');
                    }
                    $produit->SetPhoto($nomfichier);
                }
                $em = $this->getDoctrine()->getManager();
                $em->persist($produit);
                $em->flush();
                $this->addFlash("success","message.succes");
            }

            return $this->render('produit/produit_view.html.twig', [
                'produit' => $produit,
                'form_edit' => $form->createView(),
				'form_contenupanier' => $form2->createView()
            ]);
        }
        else{
            return $this->redirectToRoute('accueil');
        }
    }
	
    /**
    * @Route("/produit/delete/{id}", name="produit_delete")
    */
    public function delete(Produit $produit=null){
        if ($produit != null){
            $em = $this->getDoctrine()->getManager();
            $em->remove($produit);
            $em->flush();
            if ($produit->getPhoto() != null){
                unlink($this->getParameter('photo_dir').$produit->getPhoto());
            }
            $this->addFlash("success","message.succes");
        }
    return $this->redirectToRoute('accueil');
    }

}
