<?php

namespace App\Entity\Trait;

use Doctrine\ORM\Mapping as ORM;

trait TimestampableTrait
{
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt;

    public function initializeTimestamps()
    {
        // Setze Timestamps beim Erstellen des Objekts
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    #[ORM\PrePersist]
    public function setTimestampsOnCreate(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTimeImmutable();
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    #[ORM\PreUpdate]
    public function setTimestampsOnUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
