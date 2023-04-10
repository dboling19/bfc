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

    #[ORM\Column]
    private ?string $path = null;

    #[ORM\Column]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_trashed = null;
    
    #[ORM\OneToMany(mappedBy: 'directory', targetEntity: Doc::class, orphanRemoval: true)]
    private Collection $file;

    #[ORM\ManyToOne(inversedBy: 'subdirectories')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Directory $parent = null;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: Directory::class)]
    private Collection $subdirectories;

    #[ORM\ManyToMany(targetEntity: Tag::class, mappedBy: 'directories')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Collection $tags = null;

    public function __construct()
    {
        $this->file = new ArrayCollection();
        $this->subdirectories = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection<int, Directory>
     */
    public function getSubdirectories(): Collection
    {
        return $this->subdirectories;
    }

    public function addSubdirectory(Directory $subdirectory): self
    {
        if (!$this->subdirectories->contains($subdirectory)) {
            $this->subdirectories->add($subdirectory);
            $subdirectory->setParent($this);
        }

        return $this;
    }

    public function removeSubdirectory(Directory $subdirectory): self
    {
        if ($this->subdirectories->removeElement($subdirectory)) {
            // set the owning side to null (unless already changed)
            if ($subdirectory->getParent() === $this) {
                $subdirectory->setParent(null);
            }
        }

        return $this;
    }

    public function getDateTrashed(): ?\DateTimeInterface
    {
        return $this->date_trashed;
    }

    public function setDateTrashed(?\DateTimeInterface $date_trashed): self
    {
        $this->date_trashed = $date_trashed;

        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
            $tag->addDirectory($this);
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        if ($this->tags->removeElement($tag)) {
            $tag->removeDirectory($this);
        }

        return $this;
    }


}
