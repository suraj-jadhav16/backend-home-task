<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ScanStatus
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    private $ciUploadId;

    #[ORM\Column(type: 'boolean')]
    private $status;  // tinyint as boolean

    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    #[ORM\Column(type: 'datetime')]
    private $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime(); // automatically sets the current timestamp
        $this->updatedAt = new \DateTime();
    }

    // Getters and setters
    public function getCiUploadId()
    {
        return $this->ciUploadId;
    }

    public function setCiUploadId(int $ciUploadId)
    {
        $this->ciUploadId = $ciUploadId;
        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus(bool $status)
    {
        $this->status = $status;
        return $this;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(): self
    {
        $this->updatedAt = new \DateTime();
        return $this;
    }
}
