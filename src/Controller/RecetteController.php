<?php

namespace App\Controller;

use App\Repository\RecetteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RecetteController extends AbstractController
{
    #[Route('/', name: 'recette_accueil')]
    public function accueil(): Response
    {
        return $this->render('recette/accueil.html.twig');
    }

    #[Route('/recettes', name: 'recette_liste')]
    public function recettes(
        RecetteRepository $recetteRepository
    ): Response
    {
        //dump(isset($_COOKIE['tri']));
        if (isset($_COOKIE['tri'])) {
            $cookieTri = $_COOKIE['tri'];
            //dump($cookieTri);
            switch ($cookieTri) {
                case 'fav':
                    //return $this->redirectToRoute('recette_liste_fav');
                    return $this->redirectToRoute('recette_liste_tri', [ 'param' => $cookieTri ]);
                    break;
                case 'nom':
                    //return $this->redirectToRoute('recette_liste_nom');
                    return $this->redirectToRoute('recette_liste_tri', [ 'param' => $cookieTri ]);
                    break;
                default:
                    return $this->redirectToRoute('recette_accueil');
                    break;
            }
        } else {
            $recettes = $recetteRepository->findAll();
            return $this->render('recette/liste.html.twig',
                compact('recettes')
            );
        }
    }

    #[Route('/recettes/tri/{param}', name: 'recette_liste_tri')]
    public function recettesTri(
        string            $param,
        RecetteRepository $recetteRepository
    ): Response
    {
        switch ($param) {
            case 'nom':
                $recettes = $recetteRepository->findBy([], ["nom" => "ASC"]);
                break;
            case 'fav':
                $recettes = $recetteRepository->findBy([], ["estFavori" => "DESC"]);
                break;
        }

        //setcookie('tri','nom', time() + (24 * 60 * 60));
        $cookie = new Cookie('tri', $param, time() + (24 * 60 * 60));
        $response = new Response();
        $response->headers->setCookie($cookie);

        return $response->setContent($this->renderView('recette/liste.html.twig',
            compact('recettes')));
        /*return $this->render('recette/liste.html.twig',
            compact('recettes')
        );*/
    }

    #[Route('/recettes/tri/nom', name: 'recette_liste_nom')]
    public function recettesTriNom(
        RecetteRepository $recetteRepository
    ): Response
    {
        $recettes = $recetteRepository->findBy([], ["nom" => "ASC"], null, 0);
        //setcookie('tri','nom', time() + (24 * 60 * 60));
        $cookie = new Cookie('tri', 'nom', time() + (24 * 60 * 60));
        $response = new Response();
        $response->headers->setCookie($cookie);

        return $response->setContent($this->renderView('recette/liste.html.twig',
            compact('recettes')));
        /*return $this->render('recette/liste.html.twig',
            compact('recettes')
        );*/
    }

    #[Route('/recettes/tri/fav', name: 'recette_liste_fav')]
    public function recettesTriFavoris(
        RecetteRepository $recetteRepository
    ): Response
    {
        $recettes = $recetteRepository->findBy([], ["estFavori" => "DESC"], null, 0);
        //setcookie('tri','fav', time() + (24 * 60 * 60));

        $cookie = new Cookie('tri', 'fav', time() + (24 * 60 * 60));
        $response = new Response();
        $response->headers->setCookie($cookie);

        return $response->setContent($this->renderView('recette/liste.html.twig',
            compact('recettes')));

        /*return $this->render('recette/liste.html.twig',
            compact('recettes')
        );*/
    }

    #[Route('/recettes/detail/{id}', name: 'recette_detail')]
    public function recette(
        int               $id,
        RecetteRepository $recetteRepository
    ): Response
    {
        $recette = $recetteRepository->findOneBy(["id" => $id]);
        return $this->render('recette/detail.html.twig',
            compact('recette')
        );
    }

    #[Route('/recettes/favori/{id}', name: 'recette_changer_favori')]
    public function recetteChangerFavori(
        int                    $id,
        RecetteRepository      $recetteRepository,
        EntityManagerInterface $em
    ): Response
    {
        //récupération de l'objet
        $recette = $recetteRepository->findOneBy(["id" => $id]);
        $favori = $recette->isEstFavori();
        //mise à jour de l'objet retourné
        $recette->setEstFavori(!$favori);
        //update de l'objet (car il existe en base)
        $em->persist($recette);
        $em->flush();

        //rediriger vers la twig afficher la liste des recettes
        return $this->redirectToRoute('recette_liste');
    }
}
