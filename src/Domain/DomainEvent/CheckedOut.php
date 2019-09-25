<?php

declare(strict_types=1);

namespace Building\Domain\DomainEvent;

use Prooph\EventSourcing\AggregateChanged;
use Rhumsaa\Uuid\Uuid;

final class CheckedOut extends AggregateChanged
{
    public static function ofBuilding(Uuid $buildingId, string $username): self
    {
        return new self($buildingId->toString(), [
            'username' => $username,
        ]);
    }

    public function username() : string
    {
        return $this->payload['username'];
    }

    public function buildingId(): Uuid
    {
        return Uuid::fromString($this->aggregateId());
    }
}
