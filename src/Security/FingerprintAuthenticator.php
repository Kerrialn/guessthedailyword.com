<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Service\GuestNameGenerator;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use App\Repository\UserRepository;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class FingerprintAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    use TargetPathTrait;

    public function __construct(
        private UserRepository                 $userRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly GuestNameGenerator    $guestNameGenerator,
        private readonly Security              $security
    )
    {
    }

    /**
     * Only supports fingerprint if no email is submitted in the request
     */
    public function supports(Request $request): bool
    {
        if ($request->request->get('email')) {
            return false;
        }

        return $request->getSession()->has('fingerprint');
    }

    public function authenticate(Request $request): Passport
    {
        $fingerprint = $request->getSession()->get('fingerprint');

        if (!$fingerprint) {
            throw new AuthenticationException('No fingerprint provided.');
        }

        $user = $this->userRepository->findOneBy(['fingerprint' => $fingerprint]);

        if (!$user) {
            throw new UserNotFoundException('User not found for provided fingerprint.');
        }

        return new SelfValidatingPassport(new UserBadge($user->getFingerprint()));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): null|RedirectResponse
    {
        $targetPath = $this->getTargetPath($request->getSession(), $firewallName);
        if ($targetPath) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('landing'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return null;
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        $fingerprint = $request->getSession()->get('fingerprint');
        $user = $this->userRepository->findOneBy(['fingerprint' => $fingerprint]);

        if (!$user instanceof User) {
            $user = new User(
                name: $this->guestNameGenerator->generateFullName(),
                fingerprint: $fingerprint
            );
            $user->setRoles(['ROLE_GUEST']);
            $this->userRepository->save(entity: $user, flush: true);
        }

       return $this->security->login(user: $user, authenticatorName: 'security.authenticator.form_login.main');
    }
}
