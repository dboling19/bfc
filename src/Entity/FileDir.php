<?php

namespace App\Entity;

use App\Repository\FileDirRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FileDirRepository::class)]
class FileDir
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToMany(targetEntity: Directory::class, inversedBy: 'fileDirs')]
    private Collection $directory_id;

    #[ORM\ManyToMany(targetEntity: File::class, inversedBy: 'dirFiles')]
    private Collection $file_id;


    public function __construct()
    {
        $this->directory_id = new ArrayCollection();
        $this->file_id = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, directory>
     */
    public function getDirectoryId(): Collection
    {
        return $this->directory_id;
    }

    public function addDirectoryId(directory $directoryId): self
    {
        if (!$this->directory_id->contains($directoryId)) {
            $this->directory_id->add($directoryId);
        }

        return $this;
    }

    public function removeDirectoryId(directory $directoryId): self
    {
        $this->directory_id->removeElement($directoryId);

        return $this;
    }

    /**
     * @return Collection<int, file>
     */
    public function getFileId(): Collection
    {
        return $this->file_id;
    }

    public function addFileId(file $fileId): self
    {
        if (!$this->file_id->contains($fileId)) {
            $this->file_id->add($fileId);
        }

        return $this;
    }

    public function removeFileId(file $fileId): self
    {
        $this->file_id->removeElement($fileId);

        return $this;
    }
    
}
