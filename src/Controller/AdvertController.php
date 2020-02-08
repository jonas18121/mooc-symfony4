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

use App\Entity\AdvertSkill;
use App\Repository\AdvertSkillRepository;
use App\Repository\SkillRepository;

use App\Purger\OCPurger;

class AdvertController extends AbstractController
{
    /** purger les annonces de plus de X jours
     * @Route("/advert/purge/{days}", name="OC_advert_purge", requirements={
     *      "days" = "[0-9]{1,}"
     * })
     */
    public function purgeAction($days, Request $request, OCPurger $purger)
    {
        //On purge les annonces
        $purger->purge($days);

        // on fait le petit message flash
        $this->addFlash('info', "Les annonces plus vieilles que {$days} jours ont été purgées.");

        //on redirige vers la page d'accueil
        return $this->redirectToRoute('OC_advert_index');
    }



     //affiché les dernières annonces
    public function menuAction($limit, AdvertRepository $repo, ApplicationRepository $ApplicationRepo)
    {
        // récupère toutes les annonces qui correspondent à une liste de catégories
        //$listAdvertsByCategory = $repo->getAdvertWithCategories(['Développement web', 'Intégration']);

        // récupère les X dernières candidatures avec leurs annonces associer
        //$limits = $ApplicationRepo->getApplicationsWithAdvert(3);

        $limits = 3;

        $listAdverts = $repo->findBy(
            [],                 //critères
            ['date' => 'desc'], //on trie par date décroissante 
            $limits,            //on limite de nombre d'annonces qu'on veut afficher
            0                   //a partir d'un premier
        );

        return $this->render('advert/_menu.html.twig',[
            'listAdverts' => $listAdverts,
            'limits' => $limits
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
    public function index($page, AdvertRepository $repo)
    {
        if($page < 1)
        {
            throw $this->createNotFoundException("page '{$page}' inexistante"); // on lance une erreur 404
        }

        $nbPerPage = 3;

        //on récupère toutes les annonces
        $listAdverts = $repo->getAdverts($page, $nbPerPage);

        //dump(ceil(count($listAdverts) / $nbPerPage));die;

        /* on calcule le nombre total de pages grace au count($listAdverts) 
        et ceil() va arroundir le chiffre décimal (float) en entier (integer),
        puis retoune le nombre total
        d'annonces */
        $nbPages = ceil(count($listAdverts) / $nbPerPage);

        //si la page n'existe pas, on retourne une erreur 404
        if($page > $nbPages)
        {
            throw $this->createNotFoundException("La page {$page} n'existe pas.");
        }
        

        return $this->render('advert/index.html.twig', [
            'name'        => 'zoubert',
            'listAdverts' => $listAdverts,
            'nbPages'     => $nbPages,
            'page'        => $page
        ]);
    }

    //requirements = pour ajouter des contraintes
    /** 
     * @Route("/advert/view/{id}", name="OC_advert_view", requirements={
     *      "id" = "[0-9]{1,}"
     * })
     */
    public function view($id, Request $request , OCAntiSpame $antiSpam, AdvertSkillRepository $repoAdvertSkill, AdvertRepository $repo, ApplicationRepository $repoApplication)
    {
        $tag = $request->query->get('tag'); //pour une URL /advert/view/{id}?tag=une_valeur
        $ok = $request->query->get('ok'); 

        //on récupère l'id de l'annonce
        $advert = $repo->find($id);

        if($antiSpam->isSpam($advert)){
            throw new \Exception('Votre messages a été détècté comme spam');
        }


        //on recupère la liste des candidature lier à cette annonce
        $listApplications = $repoApplication->findBy(['advert' => $advert]);

        // on récupère la liste de compétences lier à cette annonce
        $listAdvertSkills = $repoAdvertSkill->findBy(['advert' => $advert]);


        
        //return new Response("Affichage de l'annonce d'id : '{$id}' , avec le tag : {$tag} {$ok} ");
        return $this->render('advert/view.html.twig', [
            'id'  => $id, 
            'tag' => $tag,
            'advert' => $advert,
            'listApplications' => $listApplications,
            'listAdvertSkills' => $listAdvertSkills
        ]);
    }

    /**
     * @Route("/advert/add", name="OC_advert_add")
     */
    public function add(Request $request, EntityManagerInterface $manager, SkillRepository $repoSkill)
    {

        $advert = new Advert();
        $advert->setTitle('recherche 5 developpeur fullstack Symfony/Angular')
               ->setAuthor('Julien')
               ->setContent('recherche 5 developpeur fullstack Symfony/Angular pour un gros projet sur Nantes')
               ->setDate(new \Datetime());

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



        $listSkill = $repoSkill->findAll();

        foreach($listSkill as $skill)
        {
            $advertSkill = new AdvertSkill();

            $advertSkill->setAdvert($advert)
                        ->setSkill($skill)
                        ->setLevel('Expert');

            $manager->persist($advertSkill);
        }


        $manager->persist($advert);
        $manager->persist($application1);
        $manager->persist($application2);


        $manager->flush();
        

        if($request->isMethod('POST')) // si la requête est en POST
        {
            /* ici on traite le formulaire */ 

            $this->addFlash('notice', 'Annonce bien enregistrée');// on fait le petit message flash

            //et on fait une redirection
            return $this->redirectToRoute('OC_advert_view', ['id' => $advert->getId()]);
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
    public function edit($id, Request $request, AdvertRepository $repoAdvert, CategoryRepository $repoCategory, EntityManagerInterface $manager)
    {
        /* ici récupération de $id */ 

        //on récupère l'id de l'annonce
        $advert = $repoAdvert->find($id);

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
            return $this->redirectToRoute('OC_advert_view', ['id' => $advert->getId()]);
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