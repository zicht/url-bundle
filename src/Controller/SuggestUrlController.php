<?php

namespace Zicht\Bundle\UrlBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Zicht\Bundle\UrlBundle\Url\Provider as UrlProvider;

/** Mounted on the admin path for security. */
#[Route('/admin')]
final class SuggestUrlController extends AbstractController
{
    public static function getSubscribedServices(): array
    {
        return ['zicht_url.provider' => UrlProvider::class] + parent::getSubscribedServices();
    }

    /** Controller used for url suggestions by the url provider. */
    #[Route('/url/suggest')]
    public function suggestUrlAction(Request $request): JsonResponse
    {
        return new JsonResponse(
            [
                'suggestions' => $this->get('zicht_url.provider')->suggest($request->get('pattern')),
            ]
        );
    }

    /** Lists all links available in the url provider. */
    #[Route('/url/suggest/editor')]
    public function linkListAction(Request $request): JsonResponse
    {
        return new JsonResponse(
            $this->get('zicht_url.provider')->all($this->get('security.authorization_checker'))
        );
    }
}
