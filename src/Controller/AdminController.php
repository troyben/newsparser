<?php

namespace App\Controller;

use App\Entity\News;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin/dashboard", name="app_admin_dashboard")
     */
    public function index(ManagerRegistry $doctrine, PaginatorInterface $paginator, Request $request): Response
    {
        $entityManager = $doctrine->getManager();
        $newsRepo = $entityManager->getRepository(News::class);
        $allNewsQuery = $newsRepo->createQueryBuilder('n')->getQuery();

        $pagination = $paginator->paginate(
            $allNewsQuery, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10
        );
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
            'pagination' => $pagination
        ]);
    }
}