<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class PostController extends AbstractController
{
    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    /**
     * @Route("/admin/creer-un-article", name="post_create_post", methods={"GET|POST"})
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function createPost(Request $request, EntityManagerInterface $entityManager): Response
    {
        $post = new Post();

        $form = $this->createForm(PostType::class, $post)
            ->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            # On set l'alias de l'article avec le slugger
            $post->setAlias($this->slugger->slug($form->get('title')->getData()));

            # La méthode getData() vous permet de récupérer les valeurs du form et de les passer à l'objet $post
                # => En fait on "hydrate" notre objet Post dès la ligne 25, donc pas nécessaire de getData() ici.
//            $post = $form->getData();

            /** @var UploadedFile $file */
            $file = $form->get('photo')->getData();

            if($file) {
                $extension = '.' . $file->guessExtension();
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);
                # $safeFilename = $post->getAlias();
                $newFilename = $safeFilename . '_' . uniqid() . $extension;

                try {
                    # On a paramétré le chemin 'uploads_dir' dans le fichier config/services.yaml
                    $file->move($this->getParameter('uploads_dir'), $newFilename);

                    $post->setPhoto($newFilename);

                } catch (FileException $exception){
                    // code à exécuter si une erreur est attrapée.
                }
            } # end if($file)

            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash('success', 'Votre article est bien en ligne !');

            return $this->redirectToRoute('default_home');
        } # end if($form->isValid)

        return $this->render('post/form_post.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/voir/{cat_alias}/{post_alias}_{id}", name="show_post", methods={"GET"})
//     * @ParamConverter("post", options={"mapping": {"post_alias" : "alias"} })
     * @param Post $post
     * @return Response
     */
    public function showPost(Post $post): Response
    {
        return $this->render('post/show_post.html.twig', [
            'post' => $post
        ]);
    }
}