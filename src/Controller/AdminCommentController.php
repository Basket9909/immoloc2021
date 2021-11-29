<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminCommentController extends AbstractController
{
  
    /**
     * Permet d'administrer la liste des commentaires du site
     *  @Route("/admin/comments/{page<\d+>?1}", name="admin_comments_index")
     * @param PaginationService $pagination
     * @param int $page
     * @return Response
     */
    public function index($page, PaginationService $pagination): Response
    {
        $pagination->setEntityClass(Comment::class)
                ->setPage($page)
                ->setLimit(5);

        return $this->render('admin/comment/index.html.twig', [
            'pagination' => $pagination
        ]);
    }

    /**
     * Permet d'éditer un commentaire 
     * @Route("/admin/comments/{id}/edit", name="admin_comment_edit")
     *
     * @param Comment $comment
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function edit(Comment $comment, Request $request, EntityManagerInterface $manager)
    {
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($comment);
            $manager->flush();

            $this->addFlash(
                'success',
                "Le commentaire n°<strong>{$comment->getId()}</strong> à été modifié"
            );
        }

        return $this->render("admin/comment/edit.html.twig",[
            "comment" => $comment,
            "myForm" => $form->createView()
        ]);
    }

    /**
     * Permet de supprimer un commentaire
     * @Route("/admin/comments/{id}/delete", name="admin_comment_delete")
     *
     * @param Comment $comment
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function delete(Comment $comment, EntityManagerInterface $manager)
    {
        $this->addFlash(
            'success',
            "Le commentaire n°{$comment->getId()} a bien été supprimé"
        );

        $manager->remove($comment);
        $manager->flush();

        return $this->redirectToRoute('admin_comments_index');
    }

}
