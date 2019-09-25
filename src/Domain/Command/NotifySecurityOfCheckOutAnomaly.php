<?php

declare(strict_types=1);

namespace Building\Domain\Command;

use Prooph\Common\Messaging\Command;
use Rhumsaa\Uuid\Uuid;

final class NotifySecurityOfCheckOutAnomaly extends Command
{
    private $buildingId;
    private $username;

    public static function inBuilding(Uuid $buildingId, string $username): self
    {
        return new self(
            $buildingId,
            $username
        );
    }

    private function __construct(Uuid $buildingId, string $username)
    {
        $this->init();

        $this->buildingId = $buildingId;
        $this->username = $username;
    }

    public function buildingId(): Uuid
    {
        return $this->buildingId;
    }

    public function username(): string
    {
        return $this->username;
    }


    public function payload() : array
    {
        return [
            'buildingId' => $this->buildingId->toString(),
            'username' => $this->username,
        ];
    }

    protected function setPayload(array $payload)
    {
        $this->buildingId = Uuid::fromString($payload['buildingId']);
        $this->username = $payload['username'];
    }
}
