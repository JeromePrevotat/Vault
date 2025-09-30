<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, File>
     */
    #[ORM\ManyToMany(targetEntity: File::class, mappedBy: 'category')]
    private Collection $relatedFiles;

    public function __construct()
    {
        $this->relatedFiles = new ArrayCollection();
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, File>
     */
    public function getRelatedFiles(): Collection
    {
        return $this->relatedFiles;
    }

    public function addRelatedFile(File $relatedFile): static
    {
        if (!$this->relatedFiles->contains($relatedFile)) {
            $this->relatedFiles->add($relatedFile);
            $relatedFile->addCategory($this);
        }

        return $this;
    }

    public function removeRelatedFile(File $relatedFile): static
    {
        if ($this->relatedFiles->removeElement($relatedFile)) {
            $relatedFile->removeCategory($this);
        }

        return $this;
    }

    public function __toString(): string
    {

        return $this->name ? $this->name : '';
    }
}
