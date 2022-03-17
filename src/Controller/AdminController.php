<?php

namespace App\Controller;

use App\Entity\Admin;
use App\Entity\Article;
use App\Entity\Comment;
use App\Form\AdminType;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/moderate', name: 'comment_list')]
    public function moderate(CommentRepository $commentRepository): Response
    {
        $pendingComments = $commentRepository->findBy(['approvedAt'=>null]);
        return $this->render('admin/comments.html.twig', [
            'comments' => $pendingComments,
        ]);
    }

    #[Route('/new', name: 'admin_new')]
    public function new(Request $request, EntityManagerInterface $manager):Response{

        $admin = new Admin();
        $form = $this->createForm(AdminType::class, $admin);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($admin);
            $manager->flush();

            return $this->redirectToRoute('admin_index');
        }
        return $this->render('admin/register.html.twig', [
            'adminForm' => $form->createView(),
        ]);
    }
    #[Route('/comment/moderate/{id<\d+>}/{approved}', name: 'admin_comments_moderate')]
    public function moderateComments(Comment $comment, bool $approved, EntityManagerInterface $manager){
        $comment->setApproved($approved)
            ->setApprovedAt(new \DateTime());

        $manager->persist($comment);
        $manager->flush();

        return $this->redirectToRoute('comment_list');

    }
}
