<?php

namespace App\Entity;

use App\Repository\DirectoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DirectoryRepository::class)]
class Directory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\ManyToMany(targetEntity: FileDir::class, mappedBy: 'directory_id')]
    private Collection $fileDirs;

    public function __construct()
    {
        $this->fileDirs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * @return Collection<int, FileDir>
     */
    public function getFileDirs(): Collection
    {
        return $this->fileDirs;
    }

    public function addFileDir(FileDir $fileDir): self
    {
        if (!$this->fileDirs->contains($fileDir)) {
            $this->fileDirs->add($fileDir);
            $fileDir->addDirectoryId($this);
        }

        return $this;
    }

    public function removeFileDir(FileDir $fileDir): self
    {
        if ($this->fileDirs->removeElement($fileDir)) {
            $fileDir->removeDirectoryId($this);
        }

        return $this;
    }

}
