<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ImageRepository")
 * @ORM\HasLifecycleCallbacks
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

    //on ajoute cet attribut pour y stocker le nom du fichier temporairement
    private $tempFilename;

    private $webPath;


    /** A faire
     * A arrangé car lorsqu'un utilisateur va envoyer une image,
     * ça ne va pas affiché l'image
     */
    public function getWebPath()
    {
        return $this->webPath = $this->getUploadDir() . '/' . $this->getId() . '.' . $this->getUrl();
    } 

    /** 
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        //s'il n'y a pas de fichier, on ne fait rien
        if(null === $this->file){
            return;
        }

        /* Le nom du fichier est son id, on doit juste stocker également son extension */
        $this->url = $this->file->guessExtension();

        /* On génère le nom de l'image dans l'attribut alt de la balise <img>,
         c'est la valeur du nom du fichier sur le PC de l'internaute. exemple logo.jpg */
        $this->alt = $this->file->getClientOriginalName();
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        //s'il n'y a pas de fichier, on ne fait rien
        if(null === $this->file){
            return;
        }

        //si on avait un ancien fichier, on le supprime
        if(null !== $this->tempFilename){
            $oldFile = $this->getUploadRootDir() . '/' . $this->id . '.' . $this->tempFilename;

            if(file_exists($oldFile)){
                unlink($oldFile);
            }
        }

        //On déplace le fichier envoyé dans le répertoire de notre choix
        $this->file->move(

            // Le répertoire de destination
            $this->getUploadRootDir(),

            // Le nom du fichier à créer, ici "id.extension"
            $this->id . '.' . $this->url
        );
    }

    /**
     * @ORM\PreRemove()
     */
    public function preRemoveUpload()
    {
        // On sauvegarde temporairement le nom du fichier, car il dépend de l'id
        $this->tempFilename = $this->getUploadRootDir() . '/' . $this->id . '.' . $this->url;
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        /* En PostRemove, on n'a pas accès à l'id,
        on utilise notre nom sauvegardé */
        if(file_exists($this->tempFilename)){

            //On supprime le fichier
            unlink($this->tempFilename);
        } 
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
        return dirname(__DIR__) . "/../public/{$this->getUploadDir()}";
    }

    public function getFile(): ?string
    {
        return $this->file;
    }

    /**
     * Prend en compte l'upload d'un fichier lorsqu'il en existe déjà un autre
     */
    public function setFile(?UploadedFile $file): self
    {
        $this->file = $file;

        //On vérifie si on avait déjà un fichier pour cette entité
        if(null !== $this->url){

            //On sauvegarde l'extension du fichier pour le supprimer plus tard
            $this->tempFilename = $this->url;

            // On réinitialise les valeurs des attributs url et alt
            $this->url = null;
            $this->alt = null;
        }
        return $this;
    }

    /**
     * Set the value of webPath
     *
     * @return  self
     */ 
    public function setWebPath($webPath)
    {
        $this->webPath = $webPath;

        return $this;
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


/**
     * Manipulé le fichier envoyer
     */
    /*
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
    */
}