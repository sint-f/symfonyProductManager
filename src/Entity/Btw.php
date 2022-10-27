<?php

namespace App\Entity;

use App\Repository\BtwRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BtwRepository::class)]
class Btw
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $percent = null;

    #[ORM\OneToMany(mappedBy: 'btw', targetEntity: Product::class)]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $products;

    public function __construct()
    {
        $this->precent = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPercent(): ?int
    {
        return $this->percent;
    }

    public function setPercent(string $percent): self
    {
        $this->percent = $percent;

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProducts(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->setBtw($this);
        }

        return $this;
    }

    public function removeProducts(Product $product): self
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getBtw() === $this) {
                $product->setBtw(null);
            }
        }

        return $this;
    }

}
