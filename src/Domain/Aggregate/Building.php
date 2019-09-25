<?php

declare(strict_types=1);

namespace Building\Domain\Aggregate;

use Building\Domain\DomainEvent\CheckedIn;
use Building\Domain\DomainEvent\CheckedOut;
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
        if (array_key_exists($username, $this->checkedInUsers)) {
            throw new \DomainException(sprintf(
                'User "%s" has already checked in.',
                $username
            ));
        }

        $this->recordThat(CheckedIn::toBuilding(
            $this->uuid,
            $username
        ));
    }

    public function checkOutUser(string $username)
    {
        if (!array_key_exists($username, $this->checkedInUsers)) {
            throw new \DomainException(sprintf(
                'User "%s" has not checked in.',
                $username
            ));
        }

        $this->recordThat(CheckedOut::ofBuilding(
            $this->uuid,
            $username
        ));
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

    /**
     * {@inheritDoc}
     */
    protected function aggregateId() : string
    {
        return (string) $this->uuid;
    }
}
