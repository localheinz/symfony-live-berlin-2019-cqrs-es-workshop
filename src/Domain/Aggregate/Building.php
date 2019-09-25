<?php

declare(strict_types=1);

namespace Building\Domain\Aggregate;

use Building\Domain\DomainEvent\CheckedIn;
use Building\Domain\DomainEvent\CheckedOut;
use Building\Domain\DomainEvent\CheckInAnomalyDetected;
use Building\Domain\DomainEvent\CheckOutAnomalyDetected;
use Building\Domain\DomainEvent\NewBuildingWasRegistered;
use Prooph\EventSourcing\AggregateRoot;
use Rhumsaa\Uuid\Uuid;

final class Building extends AggregateRoot
{
    /**
     * @var Uuid
     */
    private $uuid;

    /**
     * @var string
     */
    private $name;

    private $checkedInUsers = [];

    public static function new(string $name) : self
    {
        $self = new self();

        $self->recordThat(NewBuildingWasRegistered::occur(
            (string) Uuid::uuid4(),
            [
                'name' => $name
            ]
        ));

        return $self;
    }

    public function checkInUser(string $username)
    {
        $anomalyDetected = array_key_exists($username, $this->checkedInUsers);

        $this->recordThat(CheckedIn::toBuilding(
            $this->uuid,
            $username
        ));

        if ($anomalyDetected) {
            $this->recordThat(CheckInAnomalyDetected::inBuildingForUser(
                $this->uuid,
                $username
            ));
        }
    }

    public function checkOutUser(string $username)
    {
        $anomalyDetected = !array_key_exists($username, $this->checkedInUsers);

        $this->recordThat(CheckedOut::ofBuilding(
            $this->uuid,
            $username
        ));

        if ($anomalyDetected) {
            $this->recordThat(CheckOutAnomalyDetected::inBuildingForUser(
                $this->uuid,
                $username
            ));
        }
    }

    public function whenNewBuildingWasRegistered(NewBuildingWasRegistered $event)
    {
        $this->uuid = Uuid::fromString($event->aggregateId());
        $this->name = $event->name();
    }

    public function whenCheckedIn(CheckedIn $event)
    {
        $this->checkedInUsers[$event->username()] = null;

    }

    public function whenCheckedOut(CheckedOut $event)
    {
        unset($this->checkedInUsers[$event->username()]);
    }

    public function whenCheckInAnomalyDetected(CheckInAnomalyDetected $event)
    {

    }

    public function whenCheckOutAnomalyDetected(CheckOutAnomalyDetected $event)
    {

    }

    /**
     * {@inheritDoc}
     */
    protected function aggregateId() : string
    {
        return (string) $this->uuid;
    }
}
