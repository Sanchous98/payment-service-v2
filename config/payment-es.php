<?php

use PaymentSystem\Laravel\Uuid;

return [
    'events_table' => 'stored_events',
    'snapshots_table' => 'snapshots',
    'id_type' => Uuid::class,
];
