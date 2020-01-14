<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class AdvertController extends AbstractController
{
    
    /**
     * @Route("/", name="OC_advert_index")
     */
    public function index(Environment $twig )
    {
        /*
        /**
        * @Route("/default", name="default")
        *//*
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/DefaultController.php',
        ]);
        */

        /*$content = " Bienvenue sur mon site";
        return new Response($content);*/

        $content = $twig->render('advert/index.html.twig', [
            'name' => 'zoubert'
        ]);
        return new Response($content);
    }

    //requirements = pour ajouter des contraintes
    /** 
     * @Route("/advert/view/{id}", name="OC_advert_view", requirements={
     *      "id" = "[0-9]{1,}"
     * })
     */
    public function view($id)
    {
        return new Response("Affichage de l'annonce d'id : {$id} ");
    }
}