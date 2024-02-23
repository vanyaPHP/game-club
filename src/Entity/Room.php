<?php

namespace App\Entity;

use App\Repository\RoomRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoomRepository::class)]
class Room
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\OneToMany(targetEntity: Computer::class, mappedBy: 'room', orphanRemoval: true)]
    private Collection $computers;

    #[ORM\OneToMany(targetEntity: BookingRequest::class, mappedBy: 'room', orphanRemoval: true)]
    private Collection $bookingRequests;

    #[ORM\OneToMany(targetEntity: Service::class, mappedBy: 'room', orphanRemoval: true)]
    private Collection $services;

    public function __construct()
    {
        $this->computers = new ArrayCollection();
        $this->bookingRequests = new ArrayCollection();
        $this->services = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Computer>
     */
    public function getComputers(): Collection
    {
        return $this->computers;
    }

    public function addComputer(Computer $computer): static
    {
        if (!$this->computers->contains($computer)) {
            $this->computers->add($computer);
            $computer->setRoom($this);
        }

        return $this;
    }

    public function removeComputer(Computer $computer): static
    {
        if ($this->computers->removeElement($computer)) {
            // set the owning side to null (unless already changed)
            if ($computer->getRoom() === $this) {
                $computer->setRoom(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, BookingRequest>
     */
    public function getBookingRequests(): Collection
    {
        return $this->bookingRequests;
    }

    public function addBookingRequest(BookingRequest $bookingRequest): static
    {
        if (!$this->bookingRequests->contains($bookingRequest)) {
            $this->bookingRequests->add($bookingRequest);
            $bookingRequest->setRoom($this);
        }

        return $this;
    }

    public function removeBookingRequest(BookingRequest $bookingRequest): static
    {
        if ($this->bookingRequests->removeElement($bookingRequest)) {
            // set the owning side to null (unless already changed)
            if ($bookingRequest->getRoom() === $this) {
                $bookingRequest->setRoom(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Service>
     */
    public function getServices(): Collection
    {
        return $this->services;
    }

    public function addService(Service $service): static
    {
        if (!$this->services->contains($service)) {
            $this->services->add($service);
            $service->setRoom($this);
        }

        return $this;
    }

    public function removeService(Service $service): static
    {
        if ($this->services->removeElement($service)) {
            // set the owning side to null (unless already changed)
            if ($service->getRoom() === $this) {
                $service->setRoom(null);
            }
        }

        return $this;
    }
}
