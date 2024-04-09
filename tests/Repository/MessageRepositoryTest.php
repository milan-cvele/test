<?php
declare(strict_types=1);

namespace Repository;

use App\Repository\MessageRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MessageRepositoryTest extends KernelTestCase
{
    public function test_it_has_connection(): void
    {
        self::bootKernel();

        /**
         * @var MessageRepository $messages
         */
        $messages = self::getContainer()->get(MessageRepository::class);
        
        $this->assertSame([], $messages->findAll());
    }
}