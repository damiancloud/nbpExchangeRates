<?php
namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\Response;

class TokenSystemListener
{
    public function __construct(private string $requiredToken) {
    }

    /**
     * @param RequestEvent $event
     */
    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        if (strpos($request->getPathInfo(), '/api/doc') === false) {
            $token = $request->headers->get('X-TOKEN-SYSTEM');

            if ($token !== $this->requiredToken) {
                $response = new Response('Invalid X-TOKEN-SYSTEM header', Response::HTTP_UNAUTHORIZED);
                $event->setResponse($response);
            }
        }
    }
}