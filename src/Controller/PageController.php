<?php

namespace App\Controller;

use App\Entity\BookingRequest;
use App\Entity\User;
use App\Enum\BookingStatusEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PageController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route('/', name: 'app_index')]
    public function index(Request $request): Response
    {
        $session = $request->getSession();
        $user_id = $session->get('user_id');
        if ($user_id != null)
        {
            $user = $this->entityManager->getRepository(User::class)->find($user_id);
            if ($user->getRole()->getName() == "admin")
            {
                $bookingsInfo = $this->entityManager->getRepository(BookingRequest::class)->findAll();
            }
            else
            {
                $bookingsInfo = $this->entityManager->getRepository(BookingRequest::class)->findBy(['user' => $user]);
            }

            foreach ($bookingsInfo as $bookingInfo)
            {
                if (new \DateTime(substr($bookingInfo->getRequestDate()->format("Y-m-d H:i"), 0, 10)
                        . " "
                        . $bookingInfo->getRequestTime()->format("H:i")) < new \DateTime())
                {
                    $bookingInfo->setStatus(BookingStatusEnum::CLOSED->value);
                }

                $this->entityManager->flush();
            }

            $bookings = [];
            foreach ($bookingsInfo as $bookingInfo)
            {
                $bookings []= [
                    'id' => $bookingInfo->getId(),
                    'service' => $bookingInfo->getService(),
                    'computer' => $bookingInfo->getComputer(),
                    'room' => $bookingInfo->getRoom(),
                    'requestDate' => $bookingInfo->getRequestDate()->format('Y-m-d'),
                    'requestTime' => $bookingInfo->getRequestTime()->format('H:i'),
                    'status' => $bookingInfo->getStatus(),
                    'user' => $bookingInfo->getUser()
                ];
            }
        }
        else
        {
            $user = null;
            $bookings = null;
        }

        return $this->render('app_index.html.twig', [
            'user' => $user,
            'bookings' => $bookings
        ]);
    }
}
