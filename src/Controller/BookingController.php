<?php

namespace App\Controller;

use App\Entity\BookingRequest;
use App\Entity\Computer;
use App\Entity\Room;
use App\Entity\Service;
use App\Entity\User;
use App\Enum\BookingStatusEnum;
use App\Repository\BookingRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BookingController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route('/booking-page', name: 'app_booking')]
    public function index(Request $request): Response
    {
        $service_id = $request->query->get('service_id');
        $user_id = $request->getSession()->get('user_id');
        if ($user_id != null)
        {
            $user = $this->entityManager->getRepository(User::class)->find($user_id);
        }
        else
        {
            $user = null;
        }

        return $this->render('booking_index.html.twig', [
            'user' => $user,
            'selected_service' => $service_id,
            'services' => $this->entityManager->getRepository(Service::class)->findAll(),
            'rooms' => $this->entityManager->getRepository(Room::class)->findAll()
        ]);
    }

    #[Route('/booking-request-store', name: 'app_booking_request_store', methods: 'POST')]
    public function bookingRequest(Request $request): RedirectResponse|Response
    {
        $data = $request->request->all();
        $user_id = $request->getSession()->get('user_id');
        if ($user_id == null)
        {
            return $this->render('message.html.twig', [
                'user' => null,
                'text' => 'Войдите или зарегестрируйтесь для бронирования',
                'href' => 'http://localhost:8000/login-page',
                'hrefText' => 'Войти'
            ]);
        }

        $user = $this->entityManager->getRepository(User::class)->find($user_id);
        $room = $this->entityManager->getRepository(Room::class)->find($data['room_id']);
        $service = $this->entityManager->getRepository(Service::class)->find($data['service_id']);
        $freeComputers = $room->getComputers();

        if ($service->getRoom()->getId() != $room->getId())
        {
            return $this->render('message.html.twig', [
                'user' => null,
                'text' => 'В этой комнате нет такой услуги',
                'href' => 'http://localhost:8000/booking-page',
                'hrefText' => 'Попробовать ещё раз'
            ]);
        }

        /**
         * @var BookingRequestRepository $bookingRepository
         */
        $bookingRepository = $this->entityManager->getRepository(BookingRequest::class);
        $bookings = $bookingRepository->findForTheSameDateTime($data['requestDate'], $data['requestTime']);
        if (count($bookings))
        {
            if ($room->getComputers()->count() - count($bookings) < $data['playersNumber'])
            {
                return $this->render('message.html.twig', [
                    'user' => null,
                    'text' => 'В этой комнате не достаточно свободных мест на выбранные дату и время',
                    'href' => 'http://localhost:8000/booking-page',
                    'hrefText' => 'Попробовать ещё раз'
                ]);
            }

            $busyComputers = [];
            foreach ($bookings as $booking)
            {
                /**
                 * @var BookingRequest $booking
                 */

                $busyComputers []= $booking->getComputer();
            }

            $allComputers = $room->getComputers()->toArray();
            $freeComputers = $this->getFreeComputers($allComputers, $busyComputers);
        }

        if (count($freeComputers) < $data['playersNumber'])
        {
            return $this->render('message.html.twig', [
                'user' => null,
                'text' => 'В этой комнате не достаточно свободных мест на выбранные дату и время',
                'href' => 'http://localhost:8000/booking-page',
                'hrefText' => 'Попробовать ещё раз'
            ]);
        }

        for ($i = 0;$i < $data['playersNumber']; $i++)
        {
            $booking = new BookingRequest();
            $booking->setRoom($room);
            $booking->setService($service);
            $booking->setRequestDate((new \DateTime())->setTimestamp(strtotime($data['requestDate'])));
            $booking->setRequestTime((new \DateTime())->setTimestamp(strtotime($data['requestTime'])));
            $booking->setUser($user);
            $booking->setStatus(BookingStatusEnum::PENDING->value);
            $booking->setComputer($freeComputers[$i]);
            $this->entityManager->persist($booking);
        }

        $this->entityManager->flush();

        return $this->redirect('http://localhost:8000/');
    }

    #[Route('/booking-approve', name: 'booking_approve', methods: 'POST')]
    public function bookingApprove(Request $request): RedirectResponse
    {
        $this->entityManager->getRepository(BookingRequest::class)
            ->find($request->request->get('booking_id'))
            ->setStatus(BookingStatusEnum::APPROVED->value);
        $this->entityManager->flush();

        return $this->redirect('http://localhost:8000/');
    }

    #[Route('/booking-decline', name: 'booking_decline', methods: 'POST')]
    public function bookingDecline(Request $request): RedirectResponse
    {
        $this->entityManager->getRepository(BookingRequest::class)
            ->find($request->request->get('booking_id'))
            ->setStatus(BookingStatusEnum::DISAPPROVED->value);
        $this->entityManager->flush();

        return $this->redirect('http://localhost:8000/');
    }

    private function getFreeComputers(array $allComputers, array $busyComputers): array
    {
        $result = [];
        $allComputersIds = array_map(function(Computer $computer){
            return $computer->getId();
        }, $allComputers);
        $busyComputersIds = array_map(function(Computer $computer){
            return $computer->getId();
        }, $busyComputers);

        foreach ($allComputersIds as $allComputersId)
        {
            if (!in_array($allComputersId, $busyComputersIds))
            {
                $result []= $this->entityManager->getRepository(Computer::class)->find($allComputersId);
            }
        }

        return $result;
    }
}
