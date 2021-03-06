<?php

namespace App\Controller;

use App\Entity\Commentary;
use App\Entity\Post;
use App\Entity\User;
use App\Form\CommentaryType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentaryController extends AbstractController
{
    /**
     * @Route("/ajouter-un-commentaire?post_id={id}", name="add_commentary", methods={"GET|POST"})
     * @param Post $post
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function addCommentary(Post $post, Request $request, EntityManagerInterface $entityManager): Response
    {
        $commentary = new Commentary();

        $form = $this->createForm(CommentaryType::class, $commentary)
            ->handleRequest($request);


        if($form->isSubmitted() && $form->isValid() === false){

            $this->addFlash('warning', 'Le commentaire ne peut être vide');

            return $this->redirectToRoute('show_post', [
                'cat_alias' => $post->getCategory()->getAlias(),
                'post_alias' => $post->getAlias(),
                'id' => $post->getId()
            ]);
        }

        if($form->isSubmitted() && $form->isValid()) {

            $commentary->setPost($post);
            $commentary->setAuthor($this->getUser());

            $entityManager->persist($commentary);
            $entityManager->flush();

            $this->addFlash("success", "Vous avez commenté l'article <strong>". $post->getTitle() ."</strong> avec succès !");

            return $this->redirectToRoute('show_post', [
                'cat_alias' => $post->getCategory()->getAlias(),
                'post_alias' => $post->getAlias(),
                'id' => $post->getId()
            ]);
        }

        return $this->render('rendered/form_commentary.html.twig', [
            'form' => $form->createView()
        ]);
    }
}