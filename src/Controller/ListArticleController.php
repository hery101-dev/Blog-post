<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


    final class ListArticleController extends AbstractController
{
    #[Route('/list/article', name: 'app_list_article')]
    public function index(ArticleRepository $articleRepository): Response
    {
        // Show all articles ordered by newest
        $articles = $articleRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('list_article/index.html.twig', [
            'articles' => $articles,
        ]);
    }

}
