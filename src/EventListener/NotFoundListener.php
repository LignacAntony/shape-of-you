<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Psr\Log\LoggerInterface;

class NotFoundListener
{
    private $urlGenerator;
    private $logger;

    public function __construct(UrlGeneratorInterface $urlGenerator, LoggerInterface $logger)
    {
        $this->urlGenerator = $urlGenerator;
        $this->logger = $logger;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if (!$exception instanceof NotFoundHttpException) {
            return;
        }

        $this->logger->warning('Page non trouvée : {url}', [
            'url' => $event->getRequest()->getUri(),
            'referer' => $event->getRequest()->headers->get('referer')
        ]);

    }
} 