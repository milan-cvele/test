<?php
declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof MethodNotAllowedHttpException) {
            $response = new JsonResponse([
                'error' => 'The requested method is not supported for this endpoint'
            ], Response::HTTP_METHOD_NOT_ALLOWED);
        } else {
            $response = new JsonResponse([
                'error' => 'An unexpected error has occurred'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        $event->setResponse($response);
    }
}