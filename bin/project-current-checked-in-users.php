#!/usr/bin/env php
<?php

namespace Building\App;

use Building\Domain\Aggregate\Building;
use Building\Domain\Command\CheckIn;
use Building\Domain\Command\RegisterNewBuilding;
use Building\Domain\DomainEvent\CheckedIn;
use Building\Domain\DomainEvent\CheckedOut;
use Interop\Container\ContainerInterface;
use Prooph\EventSourcing\AggregateChanged;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Stream\StreamName;

(static function () {
    /** @var ContainerInterface $dic */
    $dic = require __DIR__ . '/../container.php';

    $eventStore = $dic->get(EventStore::class);

    /** @var AggregateChanged[] $history */
    $history = $eventStore->loadEventsByMetadataFrom(new StreamName('event_stream'), [
        'aggregate_type' => Building::class,
    ]);

    $usersInBuildings = [];

    foreach ($history as $event) {
        if ($event instanceof RegisterNewBuilding) {
            $usersInBuildings[$event->aggregateId()] = [];
        } elseif ($event instanceof CheckedIn) {
            $usersInBuildings[$event->buildingId()->toString()][$event->username()] = null;
        } elseif ($event instanceof CheckedOut) {
            unset($usersInBuildings[$event->buildingId()->toString()][$event->username()]);
        }
    }

    \array_walk($usersInBuildings, static function (array $users, string $buildingId) {
        \file_put_contents(
            __DIR__ . '/../data/users-' . $buildingId . '.json',
            json_encode(array_keys($users), JSON_PRETTY_PRINT)
        );
    });
})();
