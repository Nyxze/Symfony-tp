<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Author;
use App\Entity\Category;
use App\Entity\Comment;
use App\Form\ArticleType;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use App\Repository\AuthorRepository;
use App\Repository\CategoryRepository;
use App\Service\PhotoUploader;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/blog')]
class BlogController extends AbstractController
{
    private ArticleRepository $repository;

    /**
     * @param ArticleRepository $repository
     */
    public function __construct(ArticleRepository $repository)
    {
        $this->repository = $repository;
    }

    private function getTwigParametersForSideBar(): array{
        return [
            'authorList' => $this->repository->getAuthorList(7),
            'categoryList' => $this->repository->getCategoryList(),
            'yearList' => $this->repository->getArticlesGroupedByYears()
        ];
    }


    #[Route('/', name: 'blog_home')]
    public function index(): Response
    {
        $articleList = $this->repository->findBy([],['createdAt' => 'DESC'], 4);

        return $this->render('blog/index.html.twig', [
            'articleList' => $articleList
        ]);
    }

    #[Route('/details/{id<\d+>}', name: 'blog_details')]
    public function details(
        EntityManagerInterface $manager,
        Request $request,
        Article $article): Response{

        $numberOfComments = $article->getComments()->count();

        $formView = null;

        if($numberOfComments < 4) {
            // Création du formulaire pour les commentaires
            $comment = new Comment();
            $comment->setCreatedAt(new DateTime())
                ->setArticle($article);

            $form = $this->createForm(CommentType::class, $comment);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $manager->persist($comment);
                $manager->flush();

                return $this->redirectToRoute('blog_details', ['id' => $article->getId()]);
            }

            $formView = $form->createView();
        }

        $params = array_merge(
            $this->getTwigParametersForSideBar(),
            [
                'article' => $article,
                'commentForm' => $formView
            ]
        );

        return $this->render('blog/details.html.twig', $params);
    }

    #[Route('/list', name: 'blog_list')]
    public function list(): Response {
        $articleList = $this->repository->findBy([], ['createdAt' => 'DESC']);

        $params = array_merge(
            $this->getTwigParametersForSideBar(),
            [
                'articleList' => $articleList,
                'title' => 'Liste des articles',
            ]
        );

        return $this->render(
            'blog/list.html.twig',
            $params
        );
    }

    #[Route('/by-author/{authorId<\d+>}', name: 'blog_by_author')]
    #[ParamConverter('author', options: ['id' => 'authorId'])]
    public function articleByAuthor(
        AuthorRepository $authorRepository,
        Author $author): Response {

        //$author = $authorRepository->findOneById($authorId);

        $params = array_merge(
            $this->getTwigParametersForSideBar(),
            [
                'title' => 'Liste des articles de '.$author->getFullName(),
                'articleList' => $author->getArticles()
            ]
        );

        return $this->render(
            'blog/list.html.twig',
            $params
        );
    }

    #[Route('/by-category/{categoryId<\d+>}', name: 'blog_by_category')]
    #[Entity('category', expr: "repository.find(categoryId)")]
    public function articleByCategory(
        CategoryRepository $categoryRepository,
        Category $category): Response {

        //$category = $categoryRepository->findOneById($categoryId);

        $params = array_merge(
            $this->getTwigParametersForSideBar(),
            [
                'title' => 'Liste des articles parlant de '.$category->getCategoryName(),
                'articleList' => $category->getArticles()
            ]
        );

        return $this->render(
            'blog/list.html.twig',
            $params
        );
    }

    #[Route('/search', name: 'blog_search')]
    public function search(Request $request): Response{
        $searchTerm = $request->query->get('search');
        $searchTerm = trim($searchTerm);
        $params = array_merge(
            $this->getTwigParametersForSideBar(),
            [
                'title' => "Liste des articles contenant : $searchTerm",
                'articleList' => $this->repository->getArticleBySearchTerm($searchTerm)
            ]
        );
        return $this->render('blog/list.html.twig', $params);
    }

    #[Route('/by-year/{year<\d{4}>}', name: 'blog_by_year')]
    public function articleByYear(int $year): Response{
        $params = array_merge(
            $this->getTwigParametersForSideBar(),
            [
                'title' => "Liste des articles pour l'année $year",
                'articleList' => $this->repository->getArticlesByYear($year)
            ]
        );
        return $this->render('blog/list.html.twig', $params);
    }

    #[Route('/secure/new', name: 'blog_new_article')]
    #[IsGranted('ROLE_AUTHOR')]
    public function new(  Request $request,
                                EntityManagerInterface $manager,
                                Article $article = null,
                                PhotoUploader $uploader): Response
    {
        if($article === null){
            $article = new Article();
            $article->setAuthor($this->getUser());
        }

        $form = $this->createForm(
            ArticleType::class,
            $article
        );

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            // Gestion de l'upload

            $uploader->upload($article);

            $manager->persist($article);
            $manager->flush();

            $this->addFlash('success', 'Votre article est enregistré');

            return $this->redirectToRoute('blog_list');
        }

        return $this->render('blog/form.html.twig', [
            'title' => 'Nouvel article',
            'articleForm' => $form->createView()
        ]);
    }

    #[Route('/secure/edit/{id<\d+>}', name: 'blog_edit_article')]
    #[IsGranted('ROLE_AUTHOR')]
    #[IsGranted('POST_EDIT',subject:'article')]
    public function edit(Request $request,
                         EntityManagerInterface $manager,
                         Article $article,
                         PhotoUploader $uploader){


        return $this->new($request,$manager,$article,$uploader);


    }

    #[Route('/by-date/{startDate}/{endDate}')]
    public function articlesByDate(
        DateTime $startDate,
        DateTime $endDate
    ): Response{


        $articles = $this->repository->getArticlesByDate($startDate, $endDate);

        $params = array_merge(
            $this->getTwigParametersForSideBar(),
            [
                'title' => "Liste des articles par date",
                'articleList' => $articles
            ]
        );

        return $this->render('blog/list.html.twig', $params);
    }
}
