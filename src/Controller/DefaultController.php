<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/", name="default_home", methods={"GET"})
     */
    public function home()
    {
        $posts = $this->entityManager->getRepository(Post::class)->findBy(['deletedAt' => null]);

        return $this->render('default/home.html.twig', [
            'posts' => $posts
        ]);
    }

    /**
     * @Route("/categories", name="render_categories_in_nav", methods={"GET"})
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function renderCategoriesInNav(EntityManagerInterface $entityManager): Response
    {
        $categories = $entityManager->getRepository(Category::class)->findBy(['deletedAt' => null]);

        return $this->render('rendered/nav_categories_in_nav.html.twig', [
            'categories' => $categories
        ]);
    }

}
