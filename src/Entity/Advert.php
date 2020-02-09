<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AdvertRepository")
 * 
 * on dit à Doctrine que notre entité contient des callbacks de cycle de vie
 * @ORM\HasLifecycleCallbacks() 
 */
class Advert
{
    /**
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $author;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="boolean")
     */
    private $published = true ;

    /**                                                 Advert est propriétaire
     * @ORM\OneToOne(targetEntity="App\Entity\Image", inversedBy="advert", cascade={"persist", "remove"})
     */
    private $image;

    /**                                                     Advert est pas propriétaire
     * @ORM\OneToMany(targetEntity="App\Entity\Application", mappedBy="advert", orphanRemoval=true)
     */
    private $applications;

    /**                                                     Advert est propriétaire
     * @ORM\ManyToMany(targetEntity="App\Entity\Category", inversedBy="adverts")
     */
    private $categories;

    /**                                                     Advert est pas propriétaire
     * @ORM\OneToMany(targetEntity="App\Entity\AdvertSkill", mappedBy="advert", orphanRemoval=true)
     */
    private $advertSkills;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="integer")
     */
    private $nbApplications = 0;

    /**
    * @ Gedmo\Slug(fields={"title"})
    * @ ORM\Column(name="slug", string="string", length=255, unique=true)
    */
    private $slug;


    // les listes d'objet sont des ArrayCollection()
    public function __construct()
    {
        $this->applications = new ArrayCollection();
        $this->categories   = new ArrayCollection();
        $this->advertSkills = new ArrayCollection();
        $this->date         = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getPublished(): ?bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): self
    {
        $this->published = $published;

        return $this;
    }

    public function getImage(): ?Image
    {
        return $this->image;
    }

    public function setImage(?Image $image): self
    {
        $this->image = $image;

        return $this;
    }

    
    /**
     * Get the value of slug
     */ 
    public function getSlug() : ?string
    {
        return $this->slug;
    }

    /**
     * Set the value of slug
     *
     * @return  self
     */ 
    public function setSlug(string $slug) : self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return Collection|Application[]
     */
    public function getApplications(): Collection
    {
        return $this->applications;
    }

    public function addApplication(Application $application): self
    {
        if (!$this->applications->contains($application)) {
            $this->applications[] = $application;
            $application->setAdvert($this);//on lie l'annonce à la candidature
        }

        return $this;
    }

    public function removeApplication(Application $application): self
    {
        if ($this->applications->contains($application)) {
            $this->applications->removeElement($application);
            // set the owning side to null (unless already changed)
            if ($application->getAdvert() === $this) {
                $application->setAdvert(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Category[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        if ($this->categories->contains($category)) {
            $this->categories->removeElement($category);
        }

        return $this;
    }

    /**
     * @return Collection|AdvertSkill[]
     */
    public function getAdvertSkills(): Collection
    {
        return $this->advertSkills;
    }

    public function addAdvertSkill(AdvertSkill $advertSkill): self
    {
        if (!$this->advertSkills->contains($advertSkill)) {
            $this->advertSkills[] = $advertSkill;
            $advertSkill->setAdvert($this);
        }

        return $this;
    }

    public function removeAdvertSkill(AdvertSkill $advertSkill): self
    {
        if ($this->advertSkills->contains($advertSkill)) {
            $this->advertSkills->removeElement($advertSkill);
            // set the owning side to null (unless already changed)
            if ($advertSkill->getAdvert() === $this) {
                $advertSkill->setAdvert(null);
            }
        }

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /** faire automatiquement la mise à jour de la date à chaque modification d'une annonce
     * 
     * PreUpdate nous permet de faire en sorte que cette méthode s'exécute juste avant que l'entité
     * soit modifiée dans la base de données. avant $em->persist($entity)
     * 
     * @ORM\PreUpdate
     */
    public function updateDate()
    {
        $this->setUpdatedAt(new \DateTime());
    }

    public function getNbApplications(): ?int
    {
        return $this->nbApplications;
    }

    public function setNbApplications(int $nbApplications): self
    {
        $this->nbApplications = $nbApplications;

        return $this;
    }

    /** compte le nombre de candidat qui a postulé a une annonce et
     * l'incrémente à +1
     */
    public function increaseApplication()
    {
        $this->nbApplications++;
    }

    /** compte le nombre de candidat qui a postulé a une annonce et 
     * le décrémente à -1
     */
    public function decreaseApplication()
    {
        $this->nbApplications--;
    }

}
