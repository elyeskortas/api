<?php

namespace App\Entity;

use App\Repository\PageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PageRepository::class)]
class Page
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\ManyToOne(inversedBy: 'pages')]
    private ?Application $application = null;

    #[ORM\OneToMany(mappedBy: 'page', targetEntity: Menu::class)]
    private Collection $menus;

    #[ORM\OneToMany(mappedBy: 'page', targetEntity: ItemPage::class)]
    private Collection $itemPages;


    public function __construct()
    {
        $this->menus = new ArrayCollection();
        $this->itemPages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getApplication(): ?Application
    {
        return $this->application;
    }

    public function setApplication(?Application $application): static
    {
        $this->application = $application;

        return $this;
    }

    /**
     * @return Collection<int, Menu>
     */
    public function getMenus(): Collection
    {
        return $this->menus;
    }

    public function addMenu(Menu $menu): static
    {
        if (!$this->menus->contains($menu)) {
            $this->menus->add($menu);
            $menu->setPage($this);
        }

        return $this;
    }

    public function removeMenu(Menu $menu): static
    {
        if ($this->menus->removeElement($menu)) {
            // set the owning side to null (unless already changed)
            if ($menu->getPage() === $this) {
                $menu->setPage(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ItemPage>
     */
    public function getItemPages(): Collection
    {
        return $this->itemPages;
    }

    public function addItemPage(ItemPage $itemPage): static
    {
        if (!$this->itemPages->contains($itemPage)) {
            $this->itemPages->add($itemPage);
            $itemPage->setPage($this);
        }

        return $this;
    }

    public function removeItemPage(ItemPage $itemPage): static
    {
        if ($this->itemPages->removeElement($itemPage)) {
            // set the owning side to null (unless already changed)
            if ($itemPage->getPage() === $this) {
                $itemPage->setPage(null);
            }
        }

        return $this;
    }

  
}
