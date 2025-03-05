<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Psr\Log\LoggerInterface;

class NotFoundListener
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly LoggerInterface $logger
    ) {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if (!$exception instanceof NotFoundHttpException) {
            return;
        }

        // Log l'erreur 404 pour analyse
        $this->logger->warning('Page non trouvée : {url}', [
            'url' => $event->getRequest()->getUri(),
            'referer' => $event->getRequest()->headers->get('referer')
        ]);

        // Rediriger vers la page 404 personnalisée
        $event->setResponse(new RedirectResponse(
            $this->urlGenerator->generate('app_home')
        ));
    }
} 