<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/article')]
final class ArticleController extends AbstractController
{
    #[Route(name: 'app_article_index', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository): Response
    {
        // Seuls les utilisateurs authentifiés peuvent voir la liste de leurs articles
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        // Récupère uniquement les articles de l'utilisateur courant
        $articles = $articleRepository->findByAuthor($user);

        return $this->render('article/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/new', name: 'app_article_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, ArticleRepository $articleRepository): Response
    {
        // Seuls les utilisateurs authentifiés peuvent créer un article
        $this->denyAccessUnlessGranted('ROLE_USER');

        $article = new Article();
        // définir automatiquement l'auteur et la date de création
        $article->setAuthor($this->getUser());
        $article->setCreatedAt(new \DateTimeImmutable());
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move($this->getParameter('kernel.project_dir') . '/public/images', $newFilename);
                    $article->setImagePath('images/' . $newFilename);
                } catch (FileException $e) {
                    // unable to upload image; you may want to add a flash message here
                }
            }

            // generate slug from title and ensure uniqueness
            $baseSlug = strtolower($slugger->slug($article->getTitle()));
            $slugCandidate = $baseSlug;
            $i = 1;
            while ($articleRepository->findOneBy(['slug' => $slugCandidate])) {
                $slugCandidate = $baseSlug . '-' . $i++;
            }
            $article->setSlug($slugCandidate);

            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('article/new.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{slug}', name: 'app_article_show', methods: ['GET'])]
    public function show(string $slug, ArticleRepository $articleRepository): Response
    {
        $article = $articleRepository->findOneBy(['slug' => $slug]);
        if (!$article) {
            throw $this->createNotFoundException('Article non trouvé');
        }

        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/{slug}/edit', name: 'app_article_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, string $slug, ArticleRepository $articleRepository, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        // Seuls les utilisateurs authentifiés peuvent modifier un article
        $this->denyAccessUnlessGranted('ROLE_USER');
        $article = $articleRepository->findOneBy(['slug' => $slug]);
        if (!$article) {
            throw $this->createNotFoundException('Article non trouvé');
        }

        // Un utilisateur ne peut modifier que ses propres articles
        if ($article->getAuthor() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier cet article.');
        }

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // handle uploaded image
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                // remove old image if exists
                $oldPath = $article->getImagePath();
                if ($oldPath && file_exists($this->getParameter('kernel.project_dir') . '/public/' . $oldPath)) {
                    @unlink($this->getParameter('kernel.project_dir') . '/public/' . $oldPath);
                }

                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move($this->getParameter('kernel.project_dir') . '/public/images', $newFilename);
                    $article->setImagePath('images/' . $newFilename);
                } catch (FileException $e) {
                    // ignore upload failures for now
                }
            }


            // ensure slug updated from title and unique
            $baseSlug = strtolower($slugger->slug($article->getTitle()));
            $slugCandidate = $baseSlug;
            $i = 1;
            while ($existing = $articleRepository->findOneBy(['slug' => $slugCandidate])) {
                if ($existing->getId() === $article->getId()) {
                    break;
                }
                $slugCandidate = $baseSlug . '-' . $i++;
            }
            $article->setSlug($slugCandidate);

            // mettre à jour la date de modification
            $article->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->flush();

            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('article/edit.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{slug}', name: 'app_article_delete', methods: ['POST'])]
    public function delete(Request $request, string $slug, ArticleRepository $articleRepository, EntityManagerInterface $entityManager): Response
    {
        // Seuls les utilisateurs authentifiés peuvent supprimer un article
        $this->denyAccessUnlessGranted('ROLE_USER');
        $article = $articleRepository->findOneBy(['slug' => $slug]);
        if (!$article) {
            throw $this->createNotFoundException('Article non trouvé');
        }

        // Un utilisateur ne peut supprimer que ses propres articles
        if ($article->getAuthor() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à supprimer cet article.');
        }

        // Vérification CSRF correcte (récupère le champ POST '_token')
        if ($this->isCsrfTokenValid('delete' . $article->getId(), $request->request->get('_token'))) {
            // delete image file if present
            $oldPath = $article->getImagePath();
            if ($oldPath && file_exists($this->getParameter('kernel.project_dir') . '/public/' . $oldPath)) {
                @unlink($this->getParameter('kernel.project_dir') . '/public/' . $oldPath);
            }

            $entityManager->remove($article);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
    }
}
