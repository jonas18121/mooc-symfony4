<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ImageRepository")
 */
class Image
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $alt;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Advert", mappedBy="image", cascade={"persist", "remove"})
     */
    private $advert;

    
    private $file;



    /**
     * Manipulé le fichier envoyer
     */
    public function upload()
    {
        //s'il n'y a pas de fichier, on ne fait rien
        if(null === $this->file){
            return;
        }

        //On récupère le nom originale du fichier de l'internaute
        $name = $this->file->getClientOriginalName();

        //On déclare le fichier envoyé dans le répertoire de notre choix
        $this->file->move($this->getUploadRootDir(), $name);

        // On sauvegarde le nom de fichier dans notre attribut $url
        $this->url = $name;

        //On crée également le future attribut alt de notre balise <img>
        $this->alt = $name; 
    }

    /**
     * On retourne le chemin relatif vers l'image pour un navigateur
     */
    public function getUploadDir()
    {
        return 'image';
    }

    /**
     * On retourne le chemin relatif vers l'image pour notre code PHP
     */
    public function getUploadRootDir()
    {
        return __DIR__ . "/../../../public/{$this->getUploadDir()}";
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getAlt(): ?string
    {
        return $this->alt;
    }

    public function setAlt(string $alt): self
    {
        $this->alt = $alt;

        return $this;
    }

    public function getAdvert(): ?Advert
    {
        return $this->advert;
    }

    public function setAdvert(?Advert $advert): self
    {
        $this->advert = $advert;

        // set (or unset) the owning side of the relation if necessary
        $newImage = null === $advert ? null : $this;
        if ($advert->getImage() !== $newImage) {
            $advert->setImage($newImage);
        }

        return $this;
    }

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function setFile(?string $file): self
    {
        $this->file = $file;

        return $this;
    }
}
