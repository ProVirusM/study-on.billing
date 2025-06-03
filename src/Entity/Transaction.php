<?php

namespace App\Entity;

use App\Repository\TransactionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
class Transaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'transactions')]
    #[ORM\JoinColumn(nullable: false,onDelete: 'CASCADE')]
    private ?User $users = null;

    #[ORM\ManyToOne(inversedBy: 'transactions')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?Course $course = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $type_operations = null;

    #[ORM\Column]
    private ?float $amount = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_at = null;


    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $time_arend = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsers(): ?User
    {
        return $this->users;
    }

    public function setUsers(?User $users): static
    {
        $this->users = $users;

        return $this;
    }

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    public function setCourse(?Course $course): static
    {
        $this->course = $course;

        return $this;
    }

    public function getTypeOperations(): ?int
    {
        return $this->type_operations;
    }

    public function setTypeOperations(int $type_operations): static
    {
        $this->type_operations = $type_operations;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getTimeArend(): ?\DateTimeInterface
    {
        return $this->time_arend;
    }

    public function setTimeArend(?\DateTimeInterface $time_arend): static
    {
        $this->time_arend = $time_arend;

        return $this;
    }
}
