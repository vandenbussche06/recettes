<?php

namespace App\Controller;

use App\Entity\Recettes;
use App\Entity\Ingredients;
use App\Entity\UniteMesure;
use App\Form\IngredientType;
use App\Form\RecettesType;
use App\Form\UniteMesureType;
use App\Repository\CategoriesRepository;
use App\Repository\IngredientsRepository;
use App\Repository\RecettesRepository;
use App\Repository\UniteMesureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;


class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="accueil")
     */
    public function index(): Response
    {
        return $this->render('default/index.html.twig');
    }

    /**
     * @Route("/liste", name="liste_recettes")
     */
    public function listeRecettes(RecettesRepository $recettesRepository, PaginatorInterface $paginator, Request $request): Response
    {

        $recettes = $paginator->paginate(
            $recettesRepository->findAll(),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('default/listeRecettes.html.twig', [
            'recettes' => $recettes
        ]);
    }

    /**
     * @Route("/liste_categories", name="liste_categories")
     */
    public function listeCategories(CategoriesRepository $categoriesRepository, PaginatorInterface $paginator, Request $request): Response
    {

        $categories = $paginator->paginate(
            $categoriesRepository->findAll(),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('default/listeCategories.html.twig', [
            'categories' => $categories
        ]);
    }

    /**
     * @Route("/liste_ingredients", name="liste_ingredients")
     */
    public function listeIngredients(IngredientsRepository $IngredientsRepository, PaginatorInterface $paginator, Request $request): Response
    {

        $ingredients = $paginator->paginate(
            $IngredientsRepository->findAll(),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('default/listeIngredients.html.twig', [
            'ingredients' => $ingredients
        ]);
    }

    /**
     * @Route("/liste_unite_mesure", name="liste_unite_mesure")
     */
    public function listeUniteMesure(UniteMesureRepository $UniteMesureRepository, PaginatorInterface $paginator, Request $request): Response
    {

        $unites_mesure = $paginator->paginate(
            $UniteMesureRepository->findAll(),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('default/listeUniteMesure.html.twig', [
            'unites_mesure' => $unites_mesure
        ]);
    }

    /**
     * @Route("recettes/ajouter", name="ajouter_recette", methods={"GET","POST"})
     * @Route("recettes/{id}/edition", name="edition_recette", methods={"GET"})
     */
    public function ajouterRecette(Recettes $recettes = null, Request $request, EntityManagerInterface $manager, LoggerInterface $logger): Response
    {

        $logger->info("Début ajouterRecette");

        if ($recettes === null) {
            $recettes = new Recettes();
            $titre = "Ajouter une recette";
            $îmage = null;
        } else {
            $titre = "Modifier une recette";
            $îmage = $recettes->getImage();
        }

        $logger->info("Valeur de titre : " . $titre);

        $form = $this->createForm(RecettesType::class, $recettes, [
            'method' => "POST",
            'csrf_protection' => false
        ]);


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $logger->info("Formulaire soumis et validés");

            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form['imageFile']->getData();

            $button = $form->get("brouillon");

            /** @var ClickableInterface $button  */


            $logger->info("Valeur de button : " . $button->isClicked());

            if ($button->isClicked()) {
                $recettes->setEtat('Brouillon');
            } else {
                $recettes->setEtat('Publie');
            }

            $logger->info("Valeur de uploadedFile : " . $uploadedFile);

            if ($uploadedFile != null) {
                $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);

                $destination = $this->getParameter('kernel.project_dir') . '/public/uploads';
                $newFilename = $originalFilename . '-' . uniqid() . '.' . $uploadedFile->guessExtension();

                $uploadedFile->move(
                    $destination,
                    $newFilename
                );

                $recettes->setImage($newFilename);
            } else {
                $recettes->setImage(null);
            }

            if ($recettes->getId() === null) {
                $manager->persist($recettes);
            }

            $manager->flush();

            $this->addFlash('success', 'Recette enregistrée !');

            return $this->redirectToRoute('liste_recettes');
        }

        return $this->render('default/ajouterRecettes.html.twig', [
            'form' => $form->createView(),
            'titre' => $titre,
            'image' => $îmage
        ]);
    }

    /**
     * @Route("ingredrient/ajouter", name="ajouter_ingredient", methods={"GET","POST"})
     * @Route("ingredrient/{id}/edition", name="edition_ingredient", methods={"GET"})
     */
    public function ajouterIngredient(Ingredients $ingredient = null, Request $request, EntityManagerInterface $manager): Response
    {

        if ($ingredient === null) {
            $ingredient = new Ingredients();
            $titre = "Ajouter un ingrédient";
        } else {
            $titre = "Modifier une ingrédient";
        }


        $form = $this->createForm(IngredientType::class, $ingredient, [
            'method' => "POST",
            'csrf_protection' => false
        ]);



        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($ingredient->getId() === null) {
                $manager->persist($ingredient);
            }

            $manager->flush();

            $this->addFlash('success', 'Ingrédient enregistrée !');

            return $this->redirectToRoute('liste_ingredients');
        }

        return $this->render('default/ajouterIngredients.html.twig', [
            'form' => $form->createView(),
            'titre' => $titre
        ]);
    }

    /**
     * @Route("unite_mesure/ajouter", name="ajouter_unite_mesure", methods={"GET","POST"})
     * @Route("unite_mesure/{id}/edition", name="edition_unite_mesure", methods={"GET","POST"})
     */
    public function ajouterUniteMesure(UniteMesure $unite_mesure = null, Request $request, EntityManagerInterface $manager): Response
    {

        if ($unite_mesure === null) {
            $unite_mesure = new UniteMesure();
            $titre = "Ajouter une unité de mesure";
        } else {
            $titre = "Modifier une unité de mesure";
        }


        $form = $this->createForm(UniteMesureType::class, $unite_mesure, [
            'method' => "GET",
            'csrf_protection' => false
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($unite_mesure->getId() === null) {
                $manager->persist($unite_mesure);
            }

            $manager->flush();

            $this->addFlash('success', 'Unité de mesure enregistrée !');

            return $this->redirectToRoute('liste_unite_mesure');
        }

        return $this->render('default/ajouterUniteMesure.html.twig', [
            'form' => $form->createView(),
            'titre' => $titre
        ]);
    }

    /**
     * @Route("recettes/{id}/supression", name="suppression_recette")
     */
    public function supprimerRecette(Recettes $recettes, EntityManagerInterface $manager): Response
    {
        $manager->remove($recettes);
        $manager->flush();
        return $this->redirectToRoute('liste_recettes');
    }

    /**
     * @Route("ingredient/{id}/supression", name="suppression_ingredient")
     */
    public function supprimerIngredient(Ingredients $ingredient, EntityManagerInterface $manager): Response
    {
        $manager->remove($ingredient);
        $manager->flush();
        return $this->redirectToRoute('liste_ingredients');
    }

    /**
     * @Route("unite_mesure/{id}/supression", name="suppression_unite_mesure")
     */
    public function supprimerUnite_Mesure(UniteMesure $uniteMesure, EntityManagerInterface $manager): Response
    {
        $manager->remove($uniteMesure);
        $manager->flush();
        return $this->redirectToRoute('liste_unite_mesure');
    }

    /**
     * @Route("/mail", name="email")
     */
    public function sendMail(MailerInterface $mailer): Response
    {
        // ...

        $email = (new Email())
            ->from('admin@mailtrap.com')
            ->to('lvdbpaca@gmail.com')
            ->cc('severine.vandenbussche@gmail.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Confirmation de la transaction.')
            ->text('Prélévement de 7900 euros!')
            ->html('<p>Nous confirmons la transaction effectuée ce jour par vos soins pour un montant de 7 900 euros. Merci pour votre confiance.</p>');

        $mailer->send($email);

        return $this->render('default/index.html.twig');
    }
}
