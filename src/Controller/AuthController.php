<?php

namespace App\Controller;

use App\Entity\Role;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AuthController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route('/login-page', name: 'login_page')]
    public function loginPage(): Response
    {
        return $this->render('login.html.twig', [
            'user' => null
        ]);
    }

    #[Route('/register-page', name: 'register.html.twig')]
    public function registerPage(): Response
    {
        return $this->render('register_page.html.twig', [
           'user' => null
        ]);
    }

    #[Route('/login', name: 'login_post', methods: 'POST')]
    public function login(Request $request): RedirectResponse|Response
    {
        $data = $request->request->all();
        $user = $this->entityManager->getRepository(User::class)
            ->findOneBy(['username' => $data['username'], 'password' => $data['password']]);
        if ($user == null)
        {
            return $this->render('message.html.twig', [
                'user' => null,
                'text' => 'Неверные данные',
                'href' => 'http://localhost:8000/login-page',
                'hrefText' => 'Попробовать ещё раз'
            ]);
        }

        $session = $request->getSession();
        $session->set('user_id', $user->getId());
        $session->save();

        return $this->redirect('http://localhost:8000/');
    }

    #[Route('/register', name: 'register_post', methods: 'POST')]
    public function register(Request $request): RedirectResponse|Response
    {
        $data = $request->request->all();
        $users = $this->entityManager->getRepository(User::class)
            ->findBy(['username' => $data['username'], 'email' => $data['email']]);
        if (count($users) > 0)
        {
            return $this->render('message.html.twig', [
                'user' => null,
                'text' => 'Пользователь с таким никнеймом или email уже существует',
                'href' => 'http://localhost:8000/register-page',
                'hrefText' => 'Попробовать ещё раз'
            ]);
        }

        $user = new User();
        $user->setUsername($data['username']);
        $user->setEmail($data['email']);
        $user->setPassword($data['password']);
        $user->setRole($this->entityManager->getRepository(Role::class)
            ->findOneBy(['name' => 'customer'])
        );
        $user->setRegistrationDate(new \DateTime());
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $session = $request->getSession();
        $session->set('user_id', $user->getId());
        $session->save();

        return $this->redirect('http://localhost:8000/');
    }

    #[Route('/logout', name: 'logout')]
    public function logout(Request $request): RedirectResponse
    {
        $session = $request->getSession();
        $session->remove('user_id');
        $session->save();

        return $this->redirect('http://localhost:8000/');
    }
}
