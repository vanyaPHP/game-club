<?php

namespace App\Controller;

use App\Entity\Service;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ServiceController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route('/services', name: 'app_service')]
    public function index(Request $request): Response
    {
        $user_id = $request->getSession()->get('user_id');
        if ($user_id != null)
        {
            $user = $this->entityManager->getRepository(User::class)->find($user_id);
        }
        else
        {
            $user = null;
        }

        return $this->render('services.html.twig', [
            'user' => $user,
            'services' => $this->entityManager->getRepository(Service::class)
                ->findAll()
        ]);
    }
}
