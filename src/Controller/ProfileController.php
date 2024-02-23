<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route('/profile', name: 'profile_page')]
    public function index(Request $request): Response
    {
        return $this->render('profile.html.twig', [
            'user' => $this->entityManager->getRepository(User::class)
                ->find($request->getSession()->get('user_id'))
        ]);
    }
}
