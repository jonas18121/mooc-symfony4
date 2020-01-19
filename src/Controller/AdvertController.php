<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

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
    public function index($page)
    {
        if($page < 1)
        {
            throw $this->createNotFoundException("page '{$page}' inexistante"); // on lance une erreur 404
        }
        

        return $this->render('advert/index.html.twig', [
            'name' => 'zoubert'
        ]);
    }

    //requirements = pour ajouter des contraintes
    /** 
     * @Route("/advert/view/{id}", name="OC_advert_view", requirements={
     *      "id" = "[0-9]{1,}"
     * })
     */
    public function view($id, Request $request /*, SessionInterface $session*/)
    {
        $tag = $request->query->get('tag'); //pour une URL /advert/view/{id}?tag=une_valeur
        $ok = $request->query->get('ok'); 
        
        //return new Response("Affichage de l'annonce d'id : '{$id}' , avec le tag : {$tag} {$ok} ");
        return $this->render('Advert/view.html.twig', [
            'id'  => $id, 
            'tag' => $tag
        ]);

        /*return $this->redirectToRoute("OC_advert_index");*/

        /*$userId = $session->get('userId');
        var_dump($userId);
        $session->set('userId',91);
        return new Response("okokokokokok");*/

    }

    /**
     * @Route("/advert/add", name="OC_advert_add")
     */
    public function add(Request $request)
    {
        if($request->isMethod('POST')) // si la requête est en POST
        {
            /* ici on traite le formulaire */ 

            $this->addFlash('notice', 'Annonce bien enregistrée');// on fait le petit message flash

            //et on fait une redirection
            return $this->redirectToRoute('OC_advert_view', ['id' => 5]);
        }

        //sinon on affiche le formulaire
        return $this->render('advert/add.html.twig');
    }

    /**
     * @Route("/advert/edit/{id}", requirements={
     *      "id" = "[0-9]+"
     * })
     */
    public function edit($id, Request $request)
    {
        /* ici récupération de $id */ 

        if($request->isMethod('POST')) // si la requête est en POST
        {
            $this->addFlash('notice', 'Annonce bien modifiée');// on fait le petit message flash

            //et on fait une redirection
            return $this->redirectToRoute('OC_advert_view', ['id' => 5]);
        }

        //sinon on affiche le formulaire
        return $this->render('advert/edit.html.twig');
    }

    /**
     * @Route("/advert/delete/{id}", requirements={
     *      "id" = "[0-9]+"
     * })
     */
    public function delete($id)
    {
        return $this->render('advert/delete.html.twig');
    }
}