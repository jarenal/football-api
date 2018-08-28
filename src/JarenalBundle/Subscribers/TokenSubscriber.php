<?php

namespace JarenalBundle\Subscribers;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use JarenalBundle\Controller\JWTController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class TokenSubscriber implements EventSubscriberInterface
{
    private $token_secret;

    public function __construct($token_secret)
    {
        $this->token_secret = $token_secret;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();

        if (!is_array($controller)) {
            return;
        }

        if ($controller[0] instanceof JWTController) {
            $header = $event->getRequest()->headers->get('Authorization');

            if (!$header) {
                throw new AccessDeniedHttpException('No token received');
            }

            list(, $token) = explode(" ", $header);

            $valid = $this->verifyToken($token);

            if (!$valid) {
                throw new AccessDeniedHttpException('No token received');
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::CONTROLLER => 'onKernelController'];
    }

    private function verifyToken($token)
    {
        list($headerEncoded, $payloadEncoded, $signatureEncoded) = explode('.', $token);

        $dataEncoded = "$headerEncoded.$payloadEncoded";

        $signature = $this->base64UrlDecode($signatureEncoded);

        $rawSignature = hash_hmac('sha256', $dataEncoded, $this->token_secret, true);

        return hash_equals($rawSignature, $signature);
    }

    private function base64UrlDecode(string $data)
    {
        $urlUnsafeData = strtr($data, '-_', '+/');

        $paddedData = str_pad($urlUnsafeData, strlen($data) % 4, '=', STR_PAD_RIGHT);

        return base64_decode($paddedData);
    }
}
