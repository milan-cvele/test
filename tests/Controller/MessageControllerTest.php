<?php
declare(strict_types=1);

namespace Controller;

use App\Entity\Message;
use App\Message\SendMessage;
use App\Repository\MessageRepository;
use PHPUnit\Framework\MockObject\Rule\InvokedCount;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

class MessageControllerTest extends WebTestCase
{
    use InteractsWithMessenger;

    public function statusProvider(): \Generator
    {
        yield 'Request without status parameter' => [null, $this->once(), false, Response::HTTP_OK, '{"messages":[{"uuid":"1eef4c9b-4cfa-6798-9222-ef217c24ee34","text":"Test message.","status":"read"}]}'];
        yield 'Request with status \'sent\'' => ['sent', $this->once(), false, Response::HTTP_OK, '{"messages":[{"uuid":"1eef4c9b-4cfa-6798-9223-ef217c24ee34","text":"Test sent message.","status":"sent"}]}'];
        yield 'Request with status \'read\'' => ['read', $this->once(), false, Response::HTTP_OK, '{"messages":[{"uuid":"1eef4c9b-4cfa-6798-9224-ef217c24ee34","text":"Test read message.","status":"read"}]}'];
        yield 'Request with invalid status' => ['invalid', $this->never(), false, Response::HTTP_BAD_REQUEST, '{"error":"Invalid status"}'];
        yield 'Repository method throws exception' => [null, $this->once(), true, Response::HTTP_INTERNAL_SERVER_ERROR, '{"error":"An unexpected error has occurred"}'];
    }

    /** @dataProvider statusProvider */
    function test_list
    (
        ?string      $status,
        InvokedCount $invokedCount,
        bool         $trowException,
        int          $responseCode,
        string     $responseMessage,
    ): void
    {
        $client = static::createClient();

        $messageRepositoryMock = $this->createMock(MessageRepository::class);

        $methodFindByStatus = $messageRepositoryMock->expects($invokedCount)->method('findByStatus');
        if($responseCode === Response::HTTP_OK) {
            $responseArray = json_decode($responseMessage, true);
            $message = new Message();
            $message->setStatus($responseArray['messages'][0]['status']);
            $message->setText($responseArray['messages'][0]['text']);
            $message->setUuid($responseArray['messages'][0]['uuid']);
            $methodFindByStatus->willReturn([$message]);
        }
        if ($trowException) {
            $methodFindByStatus->willThrowException(new \Exception('Test exception'));
        }

        $client->getContainer()->set(MessageRepository::class, $messageRepositoryMock);

        $client->request('GET', '/messages', $status ? ['status' => $status] : []);

        $this->assertResponseStatusCodeSame($responseCode);

        $this->assertInstanceOf(JsonResponse::class, $client->getResponse());

        $responseData = $client->getResponse()->getContent();

        $this->assertEquals($responseMessage, $responseData);

    }
    
    function test_that_it_sends_a_message(): void
    {
        $client = static::createClient();
        $client->request('GET', '/messages/send', [
            'text' => 'Hello World',
        ]);

        $this->assertResponseIsSuccessful();
        // This is using https://packagist.org/packages/zenstruck/messenger-test
        $this->transport('sync')
            ->queue()
            ->assertContains(SendMessage::class, 1);
    }
}