<?php
namespace App\Entity\Trait;

use Doctrine\ORM\Mapping as ORM;

trait UpdatedAtTrait {
    #[ORM\Column(options:["default" => "CURRENT_TIMESTAMP"])]
    private ?\DateTimeImmutable $updated_at = null;

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }
}