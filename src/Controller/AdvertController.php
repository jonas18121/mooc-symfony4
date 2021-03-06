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

use App\Entity\User;


use App\Entity\AdvertSkill;
use App\Repository\AdvertSkillRepository;
use App\Repository\SkillRepository;

use App\Form\AdvertType;
use App\Form\AdvertEditType;

use App\Purger\OCPurger;//je vais l'utiliser ce service en fesant des injection de dépendance
use Symfony\Component\Form\Extension\Core\Type\FormType as TypeFormType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

//Récupérer une instance de Validateur
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;


// limiter l'accès de certains utilisateurs grace aux Rôles
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

// accès a l'évènement MessagePostEvent
use App\Event\MessagePostEvent;

// traduire le site
use Symfony\Contracts\Translation\TranslatorInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @Route("/{_locale}", requirements={
 *      "_locale" = "en|fr"
 *  },
 *  defaults= {
 *      "_locale" = "fr"
 *  })
 */
class AdvertController extends AbstractController
{

    /** test du convertisseur que j'ai créer
     * retourne un tableau PHP , qui a été décodé depuis le JSON initial
     * exemple /test/{"a":1,"z":2,"e":3}
     * 
     * @ParamConverter("json")
     * 
     * @Route("/test/{json}")
     * 
     */
    public function ParamConverterAction($json)
    {
        return new Response(print_r($json, true));
    }


    /**
     * s'occupe de la traduction du site
     * 
     * {_locale} permet de traduire selon la langue qu'on a choisit
     * exemple : 
     *      fr :
     *          /fr/tradution/{name}, ou
     *      en :
     *          /en/tradution/{name}, ou 
     *      de :
     *          /de/tradution/{name}, ou 
     *      etc...
     * il faut créer le fichier yaml pour chaque langues
     * exemple:
     *      translations/messages.fr.yaml,
     *      translations/messages.en.yaml,
     *      translations/messages.de.yaml
     * 
     * //@/ Route("/{_locale}/traduction/{name}")
     * 
     * @Route("/traduction/{name}", name="traduction")
     */
    public function translation(TranslatorInterface $translator, $name)
    {
        $translated = $translator->trans('Symfony.is.great');

        return $this->render('translate/translate.html.twig',[
            'translated' => $translated,
            'name' => $name
        ]);
    }


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
    public function view(Advert $advert, Request $request , OCAntiSpame $antiSpam, AdvertSkillRepository $repoAdvertSkill, AdvertRepository $repo, ApplicationRepository $repoApplication)
    {
        $tag = $request->query->get('tag'); //pour une URL /advert/view/{id}?tag=une_valeur
        $ok = $request->query->get('ok'); 

        //on récupère l'id de l'annonce
        //$advert = $repo->find($id);

        //if($antiSpam->isSpam($advert)){
        //    throw new \Exception('Votre messages a été détècté comme spam');
        //}


        //on recupère la liste des candidature lier à cette annonce
        $listApplications = $repoApplication->findBy(['advert' => $advert]);

        // on récupère la liste de compétences lier à cette annonce
        $listAdvertSkills = $repoAdvertSkill->findBy(['advert' => $advert]);


        
        //return new Response("Affichage de l'annonce d'id : '{$id}' , avec le tag : {$tag} {$ok} ");
        return $this->render('advert/view.html.twig', [
            //'id'  => $advert->getId(), 
            'tag' => $tag,
            'advert' => $advert,
            'listApplications' => $listApplications,
            'listAdvertSkills' => $listAdvertSkills
        ]);
    }

    /**
     * @Security("is_granted('ROLE_AUTEUR')", statusCode=403, message="Vous n'avez pas les droit pour accéder à cette page, dégagez de là , ;)")
     * 
     * @Route("/advert/add", name="OC_advert_add")
     */
    public function add(Request $request, 
                            EntityManagerInterface $manager, 
                            SkillRepository $repoSkill, 
                            ValidatorInterface $validator, 
                            OCAntiSpame $antiSpam
                        )
    {

        //On crée un objet Advert
        $advert = new Advert();

        // pour pré-remplire un formulaire
        $advert->setDate(new \DateTime());
        

        //On crée le formulaire
        $form = $this->createForm(AdvertType::class, $advert);
        
        // si la requête est en POST
        if($request->isMethod('POST')) 
        {
            /* ici on traite le formulaire */ 

            //déplace l'image là où on veut les stockées
            //$advert->getImage()->upload();

            //$validator = $this->get('validator');
            // récupérer une instance de validateur
            //$validator = Validation::createValidator();
            
            $advert->setUser($this->getUser());

            /* handleRequest($request) dit au formulaire :
                - Voici la requête d'entrée (nos variable sont de type post)
                - Lis cette requête, 
                - Récupère les valeurs qui t'intéressent,
                - Hydrate l'objet  
            */
            $form->handleRequest($request);


            /*if($antiSpam->isSpam($advert)){
                throw new \Exception('Votre messages a été détècté comme spam');
            }*/

        
            /* /            S E R V I C E   V A L I D A T O R

            récupérer les éventuelles erreurs qui provient de l'entité Advert , fait manuellement
            //$listErrors = $validator->validate($advert);
            
            //si des erreurs sont présentes on retourne les erreurs
            if(count($listErrors) > 0){
                return new Response((string) $listErrors);
            }
            //sinon , on peut envoyer en base de donnée
            else{*/

                /*
                isValid() va compter le nombre d'erreur et retourne false s'il
                y a au moins une erreur (donc pas besoin du service validator)
                */
                //On vérifie que les valeurs entrées sont correctes
                if($form->isValid()){

                    $manager->persist($advert);
                    $manager->flush();

                    $this->addFlash('notice', 'Annonce bien enregistrée');// on fait le petit message flash
                    
                    //et on fait une redirection
                    return $this->redirectToRoute('OC_advert_view', ['id' => $advert->getId()]);
                }

            /*}*/
        }

        //sinon si ce n'est pas en POST, on affiche le formulaire
        return $this->render('advert/add.html.twig',[
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/advert/add", name="OC_advert_add")
     */
    /*
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
            /* ici on traite le formulaire */ /*

            $this->addFlash('notice', 'Annonce bien enregistrée');// on fait le petit message flash

            //et on fait une redirection
            return $this->redirectToRoute('OC_advert_view', ['id' => $advert->getId()]);
        }

        //sinon on affiche le formulaire
        return $this->render('advert/add.html.twig',[
            'advert' => $advert
        ]);
    }
    */

    /**
     * @Route("/advert/edit/{id}", name="OC_advert_edit", requirements={
     *      "id" = "[0-9]+"
     * })
     */
    public function edit(Advert $advert, Request $request, AdvertRepository $repoAdvert, CategoryRepository $repoCategory, EntityManagerInterface $manager)
    {
        /* ici récupération de $id */ 

        //on récupère l'id de l'annonce
        //$advert = $repoAdvert->find($id);

        /*
        if($advert === null){
            throw new \Exception("L'annonce qui à cette id : {$id} n'existe pas.");
        }*/ 

        // appel le AccessVoter pour controler que l'annonce appartient a l'user courrant
        $this->denyAccessUnlessGranted('edit', $advert);

        //on récupère toutes les category
        $listCategories = $repoCategory->findAll();

        foreach($listCategories as $category)
        {
            $advert->addCategory($category);
        }

        /* inutile de faire 
            $manager->persist()
        car doctrine connait déjà notre annonce
         */

        $manager->flush();


        //On crée le formulaire pour faire des modification
        $form = $this->createForm(AdvertEditType::class, $advert);

        if($request->isMethod('POST')) // si la requête est en POST
        {
            /* handleRequest($request) dit au formulaire :
                - Voici la requête d'entrée (nos variable sont de type post)
                - Lis cette requête, 
                - Récupère les valeurs qui t'intéressent,
                - Hydrate l'objet  
            */
            $form->handleRequest($request);

            //On vérifie que les valeurs entrées sont correctes
            if($form->isValid()){

                //$manager->persist($advert);
                $manager->flush();
            }
            
            $this->addFlash('notice', 'Annonce bien modifiée');// on fait le petit message flash

            //et on fait une redirection
            return $this->redirectToRoute('OC_advert_view', ['id' => $advert->getId()]);
        }

        //sinon on affiche le formulaire
        return $this->render('advert/edit.html.twig',[
            'advert' => $advert,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/advert/delete/{id}", name="OC_advert_delete", requirements={
     *      "id" = "[0-9]+"
     * })
     */
    public function delete(Advert $advert, AdvertRepository $repoArticle, CategoryRepository $repoCategory, EntityManagerInterface $manager, Request $request)
    {
        //on récupère l'id de l'annonce
        //$advert = $repoArticle->find($id);

        /*
        if($advert === null){
            throw new \Exception("L'annonce qui à cette id : {$id} n'existe pas.");
        }*/

        // appel le AccessVoter pour controler que l'annonce appartient a l'user courrant
        $this->denyAccessUnlessGranted('delete', $advert);

    
        //on crée un formulaire vide, qui contiendra que le champ CSRF
        //$form = $this->createForm(AdvertEditType::class, $advert);
        $form = $this->get('form.factory')->create();

        if($request->isMethod('POST')){

            $form->handleRequest($request);
            if($form->isValid()){

                //on supprime toutes les category
                foreach($advert->getCategories() as $category)
                {
                    $advert->removeCategory($category);
                }

                $manager->remove($advert);
                $manager->flush();
            }

            $this->addFlash('infon', 'l\'Annonce a bien été supprimée');// on fait le petit message flash

            //et on fait une redirection
            return $this->redirectToRoute('OC_advert_index');
        }
        return $this->render('advert/delete.html.twig', [
            'advert' => $advert,
            'form'   => $form->createView(),
        ]);
    }
}

/* 

public function add(Request $request, EntityManagerInterface $manager, SkillRepository $repoSkill)
    {

        //On crée un objet Advert
        $advert = new Advert();

        // pour pré-remplire un formulaire
        $advert->setDate(new \DateTime());

        //On crée le formBuilder grace au service form factory 
        $createFormBuilder = $this->createFormBuilder($advert);
        //$formBuilder = $this->get('form.factory')->createBuilder(FormType::class, $advert);

        // On ajoute les champs de l'entité que l'on veut à notre formulaire
        $createFormBuilder
            ->add('title',      TextType::class)
            ->add('date',       DateType::class)
            ->add('content',    TextareaType::class)
            ->add('author',     TextType::class)
            ->add('published',  CheckboxType::class, [ 'required' => false ])//'required' => false pour que le champs soit facultatif
            ->add('save',       SubmitType::class)
        ;  

        //A partir du createFormBuilder, on génère le formulaire
        $form = $createFormBuilder->getForm();
        //$form = $form->createView();
        
        
        
        // si la requête est en POST
        if($request->isMethod('POST')) 
        {
            /* ici on traite le formulaire */ 


            /* handleRequest($request) dit au formulaire :
                - Voici la requête d'entrée (nos variable sont de type post)
                - Lis cette requête, 
                - Récupère les valeurs qui t'intéressent,
                - Hydrate l'objet  
            *//*
            $form->handleRequest($request);

            //On vérifie que les valeurs entrées sont correctes
            if($form->isValid()){

                $manager->persist($advert);
                $manager->flush();
            }

            $this->addFlash('notice', 'Annonce bien enregistrée');// on fait le petit message flash

            //et on fait une redirection
            return $this->redirectToRoute('OC_advert_view', ['id' => $advert->getId()]);
        }

        //sinon on affiche le formulaire
        return $this->render('advert/add.html.twig',[
            'form' => $form->createView()
        ]);
    }

*/