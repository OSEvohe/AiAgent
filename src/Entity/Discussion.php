<?php

namespace App\Entity;

use App\Repository\DiscussionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DiscussionRepository::class)]
class Discussion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $uid = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    /**
     * @var Collection<int, Context>
     */
    #[ORM\OneToMany(targetEntity: Context::class, mappedBy: 'discussion', orphanRemoval: true)]
    private Collection $contexts;

    public function __construct()
    {
        $this->contexts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUid(): ?string
    {
        return $this->uid;
    }

    public function setUid(string $uid): static
    {
        $this->uid = $uid;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return Collection<int, Context>
     */
    public function getContexts(): Collection
    {
        return $this->contexts;
    }

    public function addContext(Context $context): static
    {
        if (!$this->contexts->contains($context)) {
            $this->contexts->add($context);
            $context->setDiscussion($this);
        }

        return $this;
    }

    public function removeContext(Context $context): static
    {
        if ($this->contexts->removeElement($context)) {
            // set the owning side to null (unless already changed)
            if ($context->getDiscussion() === $this) {
                $context->setDiscussion(null);
            }
        }

        return $this;
    }

    public function setId(?int $id): Discussion
    {
        $this->id = $id;
        return $this;
    }
}
