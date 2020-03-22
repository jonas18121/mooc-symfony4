<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use App\Validator\Antiflood;//sur $author

// composer require symfony/validator
use Symfony\Component\Validator\Constraints as Assert;

/* permet d'utiliser des contraintes callback , afin de faire des contraites personnalisable */
use Symfony\Component\Validator\Context\ExecutionContextInterface; 

/* permet de valider que la valeur d'un attribut est unique */
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Gedmo\Mapping\Annotation as Gedmo;

use App\Entity\Category;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AdvertRepository")
 * 
 * // on dit à Doctrine que notre entité contient des callbacks de cycle de vie
 * @ORM\HasLifecycleCallbacks()
 * 
 * // l'attribut title doit avoir que des valeur unique
 * @UniqueEntity(fields="title", message="Une annonce existe déjà avec ce titre") 
 */
class Advert
{
    /**
     * @var int - $id
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string - $title
     * @ORM\Column(type="string", length=255)
     * 
     * // le titre doit faire au moins 10 caractères de long
     * @Assert\Length(min=10, max=255, minMessage="Le champ titre a besion de minimum 10 caractères")
     */
    private $title;

    /**
     * @var string - $author
     * @ORM\Column(type="string", length=255)
     * 
     * //son argument value correspond à la valeur de l'attribut sur laquelle on a défini 
     * l'annotation ici $author
     * @Antiflood()
     * 
     * //Le nom de l'auteur doit faire au moins 2 caractère de long
     * @Assert\Length(min=2, max=255, minMessage="Le champ auteur a besoin de minimum 2 caractères")
     */
    private $author;

    /**
     * @var string - $content
     * @ORM\Column(type="text")
     * 
     *  // le contenu ne doit pas être vide
     * @Assert\NotBlank(message="le contenu ne doit pas être vide")
     * @Assert\Length(min=10, minMessage="Le champ du contenu a besoin de minimum 10 caractères")
     */
    private $content;

    /**
     * @var \DateTime - $date
     * @ORM\Column(type="datetime")
     * 
     *  //La date doit être une date valide
     * @Assert\DateTime()
     */
    private $date;

    /**
     * @var bool - $published
     * @ORM\Column(type="boolean")
     */
    private $published = true ;

    /**
     * @var Image - $image 
     *                                                 Advert est propriétaire
     * @ORM\OneToOne(targetEntity="App\Entity\Image", inversedBy="advert", cascade={"persist", "remove"})
     * 
     *  // L'image doit être valide selon les règles attachées à l'objet Image
     * @Assert\Valid()
     */
    private $image;

    /**                                                     Advert est pas propriétaire
     * @ORM\OneToMany(targetEntity="App\Entity\Application", mappedBy="advert", orphanRemoval=true)
     */
    private $applications;

    /**                                                     Advert est propriétaire
     * @ORM\ManyToMany(targetEntity="App\Entity\Category", inversedBy="adverts", cascade={"persist", "remove"})
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

    /**
     * mettre en place une règle qui va rendre invalide le contenu 
     * s'il contient les mots (démotivation et abandon)
     * 
     * @Assert\Callback
     */
    public function isContentValid(ExecutionContextInterface $context)
    {
        $forBiddenWords = ['démotivation', 'abandon'];// mot à bannir

        //vérifie que le contenu ne contient pas un de ces mots
        if(preg_match('#' . implode('|', $forBiddenWords) . '#', $this->getContent()))
        {
            //la règle est violée, on définit l'erreur
            $context
                ->buildViolation('Contenu invalide car il contient un mot interdit.')//message
                ->atPath('content')//c'est l'attribut de l'objet qui est violé
                ->addViolation()//déclenche l'erreur
            ;
        } 
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
