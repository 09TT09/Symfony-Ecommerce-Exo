<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;

/**
 * @Route("/admin")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/", name="admin")
     */
    public function index()
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }
	
	/**
     * @Route("/editRole/{id}", name="editRole")
     */
	public function editRole(User $user = null){
		if($user == null){
			return $this->redirectToRoute('accueil');
		}
		
		if($user->hasRole('ROLE_SUPER_ADMIN')){
			$user->setRoles(['ROLE_USER']);
		}
		else if($user->hasRole('ROLE_ADMIN')){
			$user->setRoles(['ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN']);
		}
		else{
			$user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
		}
		
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
		
        $this->addFlash("success","message.succes");
        return $this->redirectToRoute('users');
	}
	
    /**
     * @Route("/users", name="users")
     */
    public function users(){
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository(User::class)->findBy(array(), array('id' => 'desc')); // trouver par id décroissant

        return $this->render('admin/user.html.twig', [
            'users' => $users
        ]);
    }
}
