<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
/**
 * Constructor should be added for setting initial property values and accepting string argument for 'text' property,
 *  in order to avoid having logically invalid data state of Message instance. For example upon instantiation,
 *  Message object will have 'text' and 'uuid' properties set to null, which should be avoided. Also, good practice would
 *  be to make properties like 'uuid' and 'createdAt' immutable.
 */
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Parameter 'uuid' represents unique identifier of the message, so it should be assured that the database can not
     * hold two or more identical values for 'uuid' field. This can be done by adding unique index (unique: true).
     *
     * Validation constraints should be added to ensure that value is a valid UUID string and not empty.
     * #[Assert\Uuid]
     * #[Assert\NotBlank]
     */
    #[ORM\Column(type: Types::GUID)]
    private ?string $uuid = null;

    /**
     * Validation constraints should be added for message length in order to avoid data loss in case that database config
     * allows for silent truncation.
     * #[Assert\Length(max: 255)]
     */
    #[ORM\Column(length: 255)]
    private ?string $text = null;

    /**
     * Validation should be added to ensure that only allowed options can be saved as status
     * #[Assert\ExpressionSyntax(
     *      allowedVariables: ['sent', 'read'],
     * )]
     *
     * Length parameter should be adjusted to accommodate the data that it holds without wasting space.
     *
     * Enumeration should be implemented in order to avoid hardcoding of the values.
     *
     * Furthermore, using integer type should be considered if improving of query performance is needed.
     *
     */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $status = null;

    /**
     * Using DateTimeImmutable type for properties that represent timestamps is considered best practice. It ensures that
     * once set, value remains consistent and cannot be accidentally modified elsewhere in the code
     */
    #[ORM\Column(type: 'datetime')]
    private DateTime $createdAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;
        
        return $this;
    }
}
