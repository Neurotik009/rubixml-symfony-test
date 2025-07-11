<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Role;
use App\Form\UserRolesType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;


#[Route('/admin/users')]
final class AdminController extends AbstractController
{
    #[Route('/', name: 'admin_users_index')]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        PaginatorInterface $paginator
    ): Response {
        if(!in_array('view_role_users', $this->getUser()->getPermissions())) {
            return $this->redirectToRoute('app_home');
        }
        $queryBuilder = $entityManager
            ->getRepository(User::class)
            ->createQueryBuilder('u')
            ->leftJoin('u.roles', 'r')
            ->addSelect('r');

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            10 // Anzahl der Einträge pro Seite
        );

        return $this->render('admin/user_management/index.html.twig', [
            'pagination' => $pagination
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_user_edit')]
    public function edit(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(UserRolesType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Benutzerrollen wurden aktualisiert.');
            return $this->redirectToRoute('admin_users_index');
        }

        return $this->render('admin/user_management/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
