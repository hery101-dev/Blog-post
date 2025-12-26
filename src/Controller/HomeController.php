<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ArticleRepository $articleRepository): Response
    {
        // Afficher les 3 articles les plus récents sur la page d'accueil (du plus récent au plus ancien)
        $articles = $articleRepository->findBy([], ['createdAt' => 'DESC'], 3);

        return $this->render('home/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/liste_article', name: 'app_article_list')]
    public function listArticles(ArticleRepository $articleRepository, \Symfony\Component\HttpFoundation\Request $request): Response
    {
        // Rechercher par titre via ?q=... (public)
        $q = $request->query->get('q');
        if ($q) {
            $articles = $articleRepository->searchByTitle($q);
        } else {
            // Afficher tous les articles triés du plus récent au plus ancien
            $articles = $articleRepository->findBy([], ['createdAt' => 'DESC']);
        }

        return $this->render('home/index.html.twig', [
            'articles' => $articles,
            'q' => $q,
        ]);
    }

    #[Route('/ajax/search', name: 'app_search_ajax', methods: ['GET'])]
    public function ajaxSearch(ArticleRepository $articleRepository, Request $request): JsonResponse
    {
        $q = $request->query->get('q', '');
        if (! $q || trim($q) === '') {
            return $this->json([]);
        }

        $limit = (int) $request->query->get('limit', 7);
        $articles = $articleRepository->searchByTitleLimited($q, $limit);

        $data = array_map(function ($a) {
            return [
                'title' => $a->getTitle(),
                'slug' => $a->getSlug(),
            ];
        }, $articles);

        return $this->json($data);
    }
}
