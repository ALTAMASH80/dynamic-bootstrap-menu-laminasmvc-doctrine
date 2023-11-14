<?php
declare(strict_types=1);
namespace LRPHPT\MenuTree\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @Gedmo\Loggable
 * @Gedmo\Tree(type="nested")
 * @ORM\Table(name="menu")
 * @ORM\Entity(repositoryClass=\LRPHPT\MenuTree\Repository\MenuRepository::class)
 */
#[Gedmo\Tree(type: 'nested')]
#[ORM\Table(name: 'menu')]
#[ORM\Entity(repositoryClass: \LRPHPT\MenuTree\Repository\MenuRepository::class)]
class Menu
{
    /**
     * @var int|null
     *
     * @ORM\Column(name="id", type="integer", options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ["unsigned" => true])]
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="label", type="string", length=255)
     */
    #[ORM\Column(name: 'label', type: Types::STRING, length: 255)]
    private $label;

    /**
     * @var string|null
     *
     * @ORM\Column(name="route", type="string", length=255, nullable=true)
     */
    #[ORM\Column(name: 'route', type: Types::STRING, length: 255, nullable:true)]
    private $route;

    /**
     * @var string|null
     *
     * @ORM\Column(name="resource", type="string", length=255, nullable=true)
     */
    #[ORM\Column(name: 'resource', type: Types::STRING, length: 255, nullable:true)]
    private $resource;

    /**
     * @var string|null
     *
     * @ORM\Column(name="uri", type="string", length=255, nullable=true)
     */
    #[ORM\Column(name: 'uri', type: Types::STRING, length: 255, nullable:true)]
    private $uri;

    /**
     * @var int|null
     *
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     */
    #[Gedmo\TreeLeft]
    #[ORM\Column(name: 'lft', type: Types::INTEGER)]
    private $lft;

    /**
     * @var int|null
     *
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     */
    #[Gedmo\TreeLevel]
    #[ORM\Column(name: 'lvl', type: Types::INTEGER)]
    private $lvl;

    /**
     * @var int|null
     *
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     */
    #[Gedmo\TreeRight]
    #[ORM\Column(name: 'rgt', type: Types::INTEGER)]
    private $rgt;

    /**
     * @var string
     *
     * @Gedmo\Versioned
     * @Gedmo\Slug(fields={"label"}, updatable=true)
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updated_at;

    /**
     * @var self|null
     *
     * @Gedmo\TreeRoot
     * @ORM\ManyToOne(targetEntity="Menu")
     * @ORM\JoinColumn(name="tree_root", referencedColumnName="id", onDelete="CASCADE")
     */
    #[Gedmo\TreeRoot]
    #[ORM\ManyToOne(targetEntity: Menu::class)]
    #[ORM\JoinColumn(name: 'tree_root', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private $root;

    /**
     * @var self|null
     *
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Menu", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    #[Gedmo\TreeParent]
    #[ORM\ManyToOne(targetEntity: Menu::class, inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private $parent;

    /**
     * @var Collection<int, Menu>
     *
     * @ORM\OneToMany(targetEntity="Menu", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    #[ORM\OneToMany(targetEntity: Menu::class, mappedBy: 'parent')]
    #[ORM\OrderBy(['lft' => 'ASC'])]
    private $children;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setRoute(?string $label): void
    {
        $this->route = $label;
    }

    public function getRoute(): ?string
    {
        return $this->route;
    }

    public function setResource(?string $label): void
    {
        $this->resource = $label;
    }

    public function getResource(): ?string
    {
        return $this->resource;
    }

    public function setUri(?string $label): void
    {
        $this->uri = $label;
    }

    public function getUri(): ?string
    {
        return $this->uri;
    }

    public function getRoot(): ?self
    {
        return $this->root;
    }

    public function setParent(self $parent = null): void
    {
        $this->parent = $parent;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function getSlug():?string{
        return $this->slug;
    }

    public function setSlug(?string $slug):void{
        $this->slug = $slug;
    }
}