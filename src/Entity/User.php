<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column(length: 255)]
    private ?string $username = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    /**
     * @var Collection<int, File>
     */
    #[ORM\OneToMany(targetEntity: File::class, mappedBy: 'ownerId', orphanRemoval: true)]
    private Collection $ownedFiles;

    public function __construct()
    {
        $this->ownedFiles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return Collection<int, File>
     */
    public function getOwnedFiles(): Collection
    {
        return $this->ownedFiles;
    }

    public function addOwnedFile(File $ownedFile): static
    {
        if (!$this->ownedFiles->contains($ownedFile)) {
            $this->ownedFiles->add($ownedFile);
            $ownedFile->setOwner($this);
        }

        return $this;
    }

    public function removeOwnedFile(File $ownedFile): static
    {
        if ($this->ownedFiles->removeElement($ownedFile)) {
            // set the owning side to null (unless already changed)
            if ($ownedFile->getOwner() === $this) {
                $ownedFile->setOwner(null);
            }
        }

        return $this;
    }
}
