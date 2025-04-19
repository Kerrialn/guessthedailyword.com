<?php

declare(strict_types=1);

namespace App\Controller\Controller;

use App\Entity\User;
use App\Form\Form\RegistrationFormType;
use App\Repository\UserRepository;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly TranslatorInterface $translator,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly Security $security
    )
    {
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser() instanceof \Symfony\Component\Security\Core\User\UserInterface) {
            return $this->redirectToRoute('landing');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/complete-registration', name: 'complete_registration')]
    public function finishRegistration(#[CurrentUser] UserInterface $currentUser, Request $request): Response
    {
        $form = $this->createForm(RegistrationFormType::class, $currentUser);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            if(!$currentUser instanceof PasswordAuthenticatedUserInterface && !$currentUser instanceof User){
                return $this->redirectToRoute('app_login');
            }

            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            $currentUser->setPassword($this->userPasswordHasher->hashPassword($currentUser, $plainPassword));
            $currentUser->setRoles(['ROLE_USER']);

            $this->userRepository->save(entity: $currentUser, flush: true);
            $this->addFlash('message', $this->translator->trans('registration-complete'));
            $this->security->login(user: $currentUser, authenticatorName: 'security.authenticator.form_login.main');

            return $this->redirectToRoute('landing');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
