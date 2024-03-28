<?php

namespace Zicht\Bundle\UrlBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Zicht\Bundle\UrlBundle\Url\Provider as UrlProvider;

/** Utility controller to reference a static ref from outside the system. */
final class StaticReferenceController extends AbstractController
{
    public static function getSubscribedServices(): array
    {
        return ['zicht_url.provider' => UrlProvider::class] + parent::getSubscribedServices();
    }

    /** Redirects to the url provided by the main url provider service. */
    #[Route('/_static-ref/{name}')]
    public function redirectAction(Request $request, string $name, int $code = 301): RedirectResponse
    {
        return new RedirectResponse(
            $this->get('zicht_url.provider')->url($name),
            $code
        );
    }
}
