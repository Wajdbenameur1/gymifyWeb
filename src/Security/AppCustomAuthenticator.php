<?php
namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class AppCustomAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(private UrlGeneratorInterface $urlGenerator) {}

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email', '');
        $password = $request->request->get('password', '');
        $csrfToken = $request->request->get('_csrf_token', '');

        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($password),
            [
                new CsrfTokenBadge('authenticate', $csrfToken),
                new RememberMeBadge(),
            ]
        );
    }
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Affiche les rôles de l'utilisateur pour debug
        $roles = $token->getUser()->getRoles();
        ($roles);  // Cette ligne arrêtera l'exécution et affichera les rôles
    
        $targetPath = $this->getTargetPath($request->getSession(), $firewallName);
    
        if ($targetPath) {
            return new RedirectResponse($targetPath);
        }
    
        // Redirection selon les rôles
        if (in_array('ROLE_ADMIN', $roles, true)) {
            return new RedirectResponse($this->urlGenerator->generate('dashboard_admin'));
        }
        if (in_array('ROLE_SPORTIF', $roles, true)) {
            return new RedirectResponse($this->urlGenerator->generate('home'));
        }
        if (in_array('ROLE_ENTRAINEUR', $roles, true)) {
            return new RedirectResponse($this->urlGenerator->generate('dashboard_entraineur'));
        }
        if (in_array('ROLE_RESPONSABLE_SALLE', $roles, true)) {
            return new RedirectResponse($this->urlGenerator->generate('dashboard_responsable_salle'));
        }
    
        return new RedirectResponse($this->urlGenerator->generate('app_home'));
    }
    
  protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
