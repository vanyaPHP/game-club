<?php

namespace App\Controller;

use App\Entity\Computer;
use App\Entity\Price;
use App\Entity\Role;
use App\Entity\Room;
use App\Entity\Service;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route('/services-admin', name: 'services_admin')]
    public function services_page(Request $request): Response
    {
        $rooms = $this->entityManager->getRepository(Room::class)->findAll();

        return $this->render('admin_services.html.twig', [
            'user' => $this->entityManager->getRepository(User::class)
                ->find($request->getSession()->get('user_id')),
            'services' => $this->entityManager->getRepository(Service::class)->findAll(),
            'rooms' => $rooms
        ]);
    }

    #[Route('/rooms-admin', name: 'rooms_admin')]
    public function rooms_page(Request $request): Response
    {
        return $this->render('admin_rooms.html.twig', [
            'user' => $this->entityManager->getRepository(User::class)
                ->find($request->getSession()->get('user_id')),
            'rooms' => $this->entityManager->getRepository(Room::class)->findAll()
        ]);
    }

    #[Route('/users-admin', name: 'users_admin')]
    public function usersPage(Request $request): Response
    {
        /**
         * @var UserRepository $userRepository
         */
        $userRepository = $this->entityManager->getRepository(User::class);

        return $this->render('admin_users.html.twig', [
            'user' => $userRepository->find($request->getSession()->get('user_id')),
            'users' => $userRepository
                ->findCustomers(
                    $this->entityManager->getRepository(Role::class)
                    ->findOneBy(['name' => "customer"])
                )
        ]);
    }

    #[Route('/users-block', name: 'users_admin_block', methods: 'POST')]
    public function blockUser(Request $request): RedirectResponse
    {
        $user = $this->entityManager->getRepository(User::class)->find($request->request->get('user_id'));
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return $this->redirect('http://localhost:8000/users-admin');
    }


    #[Route('/rooms-store', name: 'rooms_admin_store', methods: 'POST')]
    public function roomStore(Request $request): RedirectResponse|Response
    {
        $data = $request->request->all();

        $rooms = $this->entityManager->getRepository(Room::class)->findBy(['name' => $data['name']]);
        if (count($rooms) > 0)
        {
            return $this->render('message.html.twig', [
                'user' => $this->entityManager->getRepository(User::class)->find($request->getSession()->get('user_id')),
                'text' => 'Комната с таким названием уже существует',
                'href' => 'http://localhost:8000/rooms-admin',
                'hrefText' => 'Попробовать ещё раз'
            ]);
        }

        $room = new Room();
        $room->setName($data['name']);
        $this->entityManager->persist($room);
        $this->entityManager->flush();
        $count = $data['computersCount'];
        for($i = 1;$i <= $count;$i++)
        {
            $computer = new Computer();
            $computer->setNumber($room->getName() . "($i)");
            $computer->setRoom($room);
            $this->entityManager->persist($computer);
        }
        $this->entityManager->flush();

        return $this->redirect('http://localhost:8000/rooms-admin');
    }

    #[Route('/rooms-delete', name: 'rooms_admin_delete', methods: 'POST')]
    public function roomDelete(Request $request): RedirectResponse
    {
        $room = $this->entityManager->getRepository(Room::class)
            ->find($request->request->get('room_id'));
        $this->entityManager->remove($room);
        $this->entityManager->flush();

        return $this->redirect('http://localhost:8000/rooms-admin');
    }

    #[Route('/services-store', name: 'services_admin_store', methods: 'POST')]
    public function serviceStore(Request $request): RedirectResponse|Response
    {
        $data = $request->request->all();

        $services = $this->entityManager->getRepository(Service::class)->findBy(['name' => $data['name']]);
        if (count($services) > 0)
        {
            return $this->render('message.html.twig', [
                'user' => $this->entityManager->getRepository(User::class)->find($request->getSession()->get('user_id')),
                'text' => 'Сервис с таким названием уже существует',
                'href' => 'http://localhost:8000/services-admin',
                'hrefText' => 'Попробовать ещё раз'
            ]);
        }

        $service = new Service();
        $service->setName($data['name']);
        $service->setRoom($this->entityManager->getRepository(Room::class)->find($data['room_id']));
        $price = new Price();
        $price->setService($service);
        $price->setAmount($data['price']);
        $service->setPrice($price);

        $this->entityManager->persist($service);
        $this->entityManager->persist($price);
        $this->entityManager->flush();

        return $this->redirect('http://localhost:8000/services-admin');
    }

    #[Route('/services-delete', name: 'services_admin_delete', methods: 'POST')]
    public function serviceDelete(Request $request): RedirectResponse
    {
        $service = $this->entityManager->getRepository(Service::class)
            ->find($request->request->get('service_id'));
        $this->entityManager->remove($service);
        $this->entityManager->flush();

        return $this->redirect('http://localhost:8000/services-admin');
    }
}
