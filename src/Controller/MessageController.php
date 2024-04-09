<?php
declare(strict_types=1);

namespace App\Controller;

use App\Message\SendMessage;
use App\Repository\MessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class MessageController extends AbstractController
{
    /**
     * TODO: cover this method with tests
     */
    /**
     * Route should be limited to GET method, only GET method should be used to retrieve data (RESTful standard) and
     * all other methods should return code 405 for this endpoint.
     *
     * Every request exception on this route should be handled gracefully and should return response of application/json
     * content type. This should be reflected in openapi.yaml specification.
     *
     * MessageRepository parameter name should be changed in order to avoid variable shadowing with $messages variable
     * declared in method body
     *
     * Return type of method should be changed to JsonResponse in order to avoid manually constructing a JSON response
     *
     * Instead of Request, status should be passed to the repository method. Repository should not be concerned with
     * parsing requests, its responsibility is retrieving data.
     *
     * Check should be implemented, when status parameter is present, if it is one of the valid statuses and if not,
     * appropriate bad request response should be returned. This should also be documented in openapi.yaml file.
     *
     * Serializer should be utilized in order to centralize the serialization process and avoid hardcoding of response
     * data keys
     */
    #[Route('/messages', methods: ['GET'])]
    public function list
    (
        MessageRepository $messageRepository,
        SerializerInterface $serializer,
        #[MapQueryParameter] ?string $status = null
    ): JsonResponse
    {
        if ($status !== null && !in_array($status, ['sent', 'read'])) {
            return new JsonResponse(['error' => 'Invalid status'], Response::HTTP_BAD_REQUEST);
        }

        $messages = $messageRepository->findByStatus($status);

        $context = (new ObjectNormalizerContextBuilder())->withGroups('list')->toArray();
        /**
         * @var Serializer $serializer
         */
        $data = $serializer->normalize($messages, null, $context);

        return new JsonResponse(['messages' => $data]);
    }

    /**
     * POST request method should be used for this route, in order to adhere to RESTful standards, since it adds new
     * record in the database. This should also be reflected in openapi.yaml specification.
     *
     * Bad Request response should be documented in openapi.yaml specification. And it should return content-type of
     * application/json for all responses. It is not consistent to have one content-type for some request method and other for
     * other request methods.For sake of consistency, this method should also have return type of JsonResponse
     *
     * Since SendMessage constructor expects string type variable, and 'get' method of request query returns mixed types,
     * 'get' method should be replaced with 'getString' method
     *
     * Instead of hardcoding response codes, it is better to use Response class constants that represent them to improve
     * code readability.
     *
     * 204 is No Content code, here 201 Created would be more suitable
     */
    #[Route('/messages/send', methods: ['GET'])]
    public function send(Request $request, MessageBusInterface $bus): Response
    {
        $text = $request->query->get('text');
        
        if (!$text) {
            return new Response('Text is required', 400);
        }

        $bus->dispatch(new SendMessage($text));
        
        return new Response('Successfully sent', 204);
    }
}