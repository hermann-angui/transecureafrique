<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserFormType;
use App\Helper\UserHelper;
use App\Security\FormLoginAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

#[Route('/admin')]
class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'admin_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('admin/security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'admin_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }


    #[Route('/register', name: 'admin_register')]
    public function register(Request $request,
                             UserPasswordHasherInterface $userPasswordHasher,
                             UserAuthenticatorInterface $userAuthenticator,
                             FormLoginAuthenticator $authenticator,
                             EntityManagerInterface $entityManager,
                             UserHelper $userHelper): Response
    {
        $user = new User();
        $form = $this->createForm(UserFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );


            $roles[] = 'ROLE_USER';

            switch($form->get('role')->getData()){
                case 'ROLE_AGENT':
                    break;
                case 'ROLE_ADMIN':
                    $roles[] = 'ROLE_ADMIN';
                    break;
                case 'ROLE_SUPER_ADMIN':
                    $roles[] = 'ROLE_SUPER_ADMIN';
                    break;
                default:
                    break;
            }
            $user->setRoles($roles);
            $user->setCreatedAt(new \DateTime());
            $user->setModifiedAt(new \DateTime());

            $entityManager->persist($user);
            $entityManager->flush();


            $photo = $form->get('photo')->getData();
            if($photo){
                $fileName = $userHelper->uploadAsset($photo, $user->getId());
                if($fileName) $user->setPhoto($fileName);
            }
            $entityManager->persist($user);
            $entityManager->flush();

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('admin/security/register.html.twig', ['registrationForm' => $form->createView()]);
    }
}
