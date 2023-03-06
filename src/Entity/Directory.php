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

    #[ORM\OneToMany(mappedBy: 'directory', targetEntity: Doc::class, orphanRemoval: true)]
    private Collection $file;

    public function __construct()
    {
        $this->file = new ArrayCollection();
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
     * @return Collection<int, Doc>
     */
    public function getFile(): Collection
    {
        return $this->file;
    }

    public function addFile(Doc $file): self
    {
        if (!$this->file->contains($file)) {
            $this->file->add($file);
            $file->setDirectory($this);
        }

        return $this;
    }

    public function removeFile(Doc $file): self
    {
        if ($this->file->removeElement($file)) {
            // set the owning side to null (unless already changed)
            if ($file->getDirectory() === $this) {
                $file->setDirectory(null);
            }
        }

        return $this;
    }

}
