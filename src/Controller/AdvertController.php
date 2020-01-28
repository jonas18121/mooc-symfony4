<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;
use App\AntiSpam\OCAntiSpame;//je vais l'utiliser ce service en fesant des injection de dépendance

use Symfony\Component\HttpFoundation\Session\SessionInterface;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Advert;
use App\Repository\AdvertRepository;

use App\Entity\Image;
use App\Entity\Application;
use App\Repository\ApplicationRepository;

use App\Entity\Category;
use App\Repository\CategoryRepository;

class AdvertController extends AbstractController
{
     //affiché les dernières annonces
    public function menuAction($limit)
    {
        $listAdverts = [
            ['id' => 2, 'title' => 'Recherche développeur Symfony'],
            ['id' => 3, 'title' => 'mission développeur Angular/Symfony'],
            ['id' => 4, 'title' => 'stage pour développeur php'],
            ['id' => 5, 'title' => 'stage pour développeur python']
        ];

        return $this->render('advert/_menu.html.twig',[
            'listAdverts' => $listAdverts,
            'limit' => $limit
        ]);
    }

    /**
     * @Route("/annonces", name="OC_advert_annonces")
     */
    public function annonces()
    {
        return $this->render('advert/annonces.html.twig');
    }


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

        $listAdverts = [
            [
                'id' => 2, 
                'title' => 'Recherche développeur Symfony',
                'author' => 'Alexandre',
                'content' => 'Nous recherchons un developpeur Symfony sur Nantes',
                'date' => new \Datetime()
            ],
            [
                'id' => 3, 
                'title' => 'Mission développeur Angular/Symfony',
                'author' => 'Kassandra',
                'content' => 'Nous avons une misson pour un developpeur Angular/Symfony sur Nantes',
                'date' => new \Datetime()
            ],
            [
                'id' => 4, 
                'title' => 'Stage pour développeur php',
                'author' => 'Olvier',
                'content' => 'Nous recherchons un developpeur pour un stage magique sur Nantes',
                'date' => new \Datetime()
            ],
            [
                'id' => 5, 
                'title' => 'Stage pour développeur python',
                'author' => 'Pierre',
                'content' => 'Super stage pour un developpeur python sur Nantes',
                'date' => new \Datetime()
            ]
        ];
        

        return $this->render('advert/index.html.twig', [
            'name' => 'zoubert',
            'listAdverts' => $listAdverts
        ]);
    }

    //requirements = pour ajouter des contraintes
    /** 
     * @Route("/advert/view/{id}", name="OC_advert_view", requirements={
     *      "id" = "[0-9]{1,}"
     * })
     */
    public function view($id, Request $request , OCAntiSpame $antiSpam , AdvertRepository $repo, ApplicationRepository $repoApplication)
    {
        $tag = $request->query->get('tag'); //pour une URL /advert/view/{id}?tag=une_valeur
        $ok = $request->query->get('ok'); 

        /*$advert = [
            'id' => 2, 
            'title' => 'Recherche développeur Symfony',
            'author' => 'Alexandre',
            'content' => 'Nous recherchons un developpeur Symfony sur Nantes',
            'date' => new \Datetime()
        ];*/

        //on récupère l'id de l'annonce
        $advert = $repo->find($id);
        //var_dump($advert);die;

        
        if($antiSpam->isSpam($advert)){
            throw new \Exception('Votre messages a été détècté comme spam');
        }


        //on recupère la liste des candidature de cette annonce
        $listApplications = $repoApplication->findBy(['advert' => $advert]);


        
        //return new Response("Affichage de l'annonce d'id : '{$id}' , avec le tag : {$tag} {$ok} ");
        return $this->render('advert/view.html.twig', [
            'id'  => $id, 
            'tag' => $tag,
            'advert' => $advert,
            'listApplications' => $listApplications
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
    public function add(Request $request, EntityManagerInterface $manager)
    {
        /*$advert = [
            'id' => 2, 
            'title' => 'Recherche développeur Symfony',
            'author' => 'Alexandre',
            'content' => 'Nous recherchons un developpeur Symfony sur Nantes',
            'date' => new \Datetime()
        ];*/

        $advert = new Advert();
        $advert->setTitle('Formation en apprentissage en tant que developpeur fullstack')
               ->setAuthor('mougli')
               ->setContent('Nous proposons un contrat d\'apprentissage en tant que developpeur sur Nantes')
               ->setDate(new \Datetime('now'));

        $image = new Image();
        $image->setUrl('bm.jpg')
              ->setAlt('my good job');
              
        $advert->setImage($image);    
        
        $application1 = new Application();
        $application1->setAuthor('Marine')
                     ->setContent('J\'ai toutes les qualité requises.');

        $application2 = new Application();
        $application2->setAuthor('Jean')
                     ->setContent('Je suis le candidat qu\'il vous faut.');

        
        $application1->setAdvert($advert);
        $application2->setAdvert($advert);

        $manager->persist($advert);
        $manager->persist($application1);
        $manager->persist($application2);


        $manager->flush();
        

        if($request->isMethod('POST')) // si la requête est en POST
        {
            /* ici on traite le formulaire */ 

            $this->addFlash('notice', 'Annonce bien enregistrée');// on fait le petit message flash

            //et on fait une redirection
            return $this->redirectToRoute('OC_advert_view', ['id' => 5]);
        }

        //sinon on affiche le formulaire
        return $this->render('advert/add.html.twig',[
            'advert' => $advert
        ]);
    }

    /**
     * @Route("/advert/edit/{id}", name="OC_advert_edit", requirements={
     *      "id" = "[0-9]+"
     * })
     */
    public function edit($id, Request $request, AdvertRepository $repoArticle, CategoryRepository $repoCategory, EntityManagerInterface $manager)
    {
        /* ici récupération de $id */ 
        /*$advert = [
            'id' => 2, 
            'title' => 'Recherche développeur Symfony',
            'author' => 'Alexandre',
            'content' => 'Nous recherchons un developpeur Symfony sur Nantes',
            'date' => new \Datetime()
        ];*/

        //on récupère l'id de l'annonce
        $advert = $repoArticle->find($id);

        if($advert === null){
            throw new \Exception("L'annonce qui à cette id : {$id} n'existe pas.");
        }

        //on récupère toutes les category
        $listCategories = $repoCategory->findAll();

        foreach($listCategories as $category)
        {
            $advert->addCategory($category);
        }

        $manager->flush();




        if($request->isMethod('POST')) // si la requête est en POST
        {
            $this->addFlash('notice', 'Annonce bien modifiée');// on fait le petit message flash

            //et on fait une redirection
            return $this->redirectToRoute('OC_advert_view', ['id' => 5]);
        }

        //sinon on affiche le formulaire
        return $this->render('advert/edit.html.twig',[
            'advert' => $advert
        ]);
    }

    /**
     * @Route("/advert/delete/{id}", name="OC_advert_delete", requirements={
     *      "id" = "[0-9]+"
     * })
     */
    public function delete($id, AdvertRepository $repoArticle, CategoryRepository $repoCategory, EntityManagerInterface $manager)
    {
        //on récupère l'id de l'annonce
        $advert = $repoArticle->find($id);

        if($advert === null){
            throw new \Exception("L'annonce qui à cette id : {$id} n'existe pas.");
        }

        //on supprime toutes les category
        foreach($advert->getCategories() as $category)
        {
            $advert->removeCategory($category);
        }

        $manager->flush();

        return $this->render('advert/delete.html.twig');
    }
}