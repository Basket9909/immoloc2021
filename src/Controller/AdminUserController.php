<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\PaginationService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminUserController extends AbstractController
{
    /**
     * @Route("/admin/user/{page<\d+>?1}", name="admin_users_index")
     */
    public function index(PaginationService $pagination, $page): Response
    {
        $pagination->setEntityClass(User::class)
                ->setPage($page)
                ->setLimit(5);

        return $this->render('admin/user/index.html.twig', [
            'pagination' => $pagination
        ]);
    }
}
