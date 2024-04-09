<?php
declare(strict_types=1);

namespace App\Tests\Message;

use App\Entity\Message;
use App\Message\SendMessage;
use App\Message\SendMessageHandler;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class SendMessageHandlerTest extends TestCase
{
    public function testHandleSendMessage(): void
    {

        $entityManager = $this->createMock(EntityManagerInterface::class);

        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function ($message) {

                $this->assertInstanceOf(Message::class, $message);

                $this->assertNotNull($message->getUuid());
                $this->assertEquals('sent', $message->getStatus());
                $this->assertSame('Test message text', $message->getText());
                $this->assertInstanceOf(\DateTime::class, $message->getCreatedAt());

                return true;
            }));

        $entityManager->expects($this->once())->method('flush');

        $handler = new SendMessageHandler($entityManager);

        $sendMessage = new SendMessage('Test message text');

        $handler($sendMessage);
    }
}