<?php

namespace App\Entity;

use App\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TagRepository::class)]
class Tag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * Many tags have many files
     * @var Collection<int, Group>
     */
    #[ORM\ManyToMany(targetEntity: Doc::class, inversedBy: 'tags')]
    #[ORM\JoinTable(name: 'doc_tags')]
    private Collection $docs;

    /**
     * Many tags have many directories
     * @var Collection<int, Group>
     */
    #[ORM\ManyToMany(targetEntity: Directory::class, inversedBy: 'tags')]
    #[ORM\JoinTable(name: 'dir_tags')]
    private Collection $directories;

    public function __construct()
    {
        $this->directories = new ArrayCollection();
        $this->docs = new ArrayCollection();
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

    /**
     * @return Collection<int, Directory>
     */
    public function getDirectories(): Collection
    {
        return $this->directories;
    }

    public function addDirectory(Directory $directory): self
    {
        if (!$this->directories->contains($directory)) {
            $this->directories->add($directory);
        }

        return $this;
    }

    public function removeDirectory(Directory $directory): self
    {
        $this->directories->removeElement($directory);

        return $this;
    }

    /**
     * @return Collection<int, Doc>
     */
    public function getDocs(): Collection
    {
        return $this->docs;
    }

    public function addDoc(Doc $doc): self
    {
        if (!$this->docs->contains($doc)) {
            $this->docs->add($doc);
        }

        return $this;
    }

    public function removeDoc(Doc $doc): self
    {
        $this->docs->removeElement($doc);

        return $this;
    }

}
