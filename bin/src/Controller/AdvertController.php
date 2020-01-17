<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

use Symfony\Component\HttpFoundation\JsonResponse;

class AdvertController extends AbstractController
{
    
    /**
     * @Route("/{page}", name="OC_advert_index",
     *  requirements={
     *      "page" = "[0-9]+"
     *  },
     *  defaults= {
     *      "page" = 1
     *  }
     * )
     */
    public function index(Environment $twig)
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
    public function view($id, Request $request)
    {
        $tag = $request->query->get('tag'); 
        $ok = $request->query->get('ok'); 
        
        //return new Response("Affichage de l'annonce d'id : '{$id}' , avec le tag : {$tag} {$ok} ");
        return $this->render('Advert/view.html.twig', [
            'id'  => $id, 
            'tag' => $tag
        ]);
    }

    /**
     * @Route("/advert/add", name="OC_advert_add")
     */
    public function add()
    {

    }

    /**
     * @Route("/advert/edit/{id}", requirements={
     *      "id" = "[0-9]+"
     * })
     */
    public function edit($id)
    {

    }

    /**
     * @Route("/advert/delete/{id}", requirements={
     *      "id" = "[0-9]+"
     * })
     */
    public function delete($id)
    {

    }
}