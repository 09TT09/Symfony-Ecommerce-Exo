<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Form\RegistrationFormType;
use Symfony\Component\HttpFoundation\Request;
//use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use App\Security\LoginFormAuthenticator;

class UserController extends AbstractController
{
    /**
     * @Route("/compte/{id}", name="compte")
     */
    public function index(User $user = null, Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $authenticator)
    {
        if($user == null){
			return $this->redirectToRoute('accueil');
		}
		
		$form = $this->createForm(RegistrationFormType::class, $user);
		$form->handleRequest($request);
	
		if($form->isSubmitted() && $form->isValid()) {
			
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
			
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
			
            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main' // firewall name in security.yaml
            );
		}
		
        return $this->render('user/index.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }
}
