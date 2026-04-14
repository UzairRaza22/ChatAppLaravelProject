<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;

class EventService
{
    public function send(array $event)
    {
        DB::connection('mongodb')
            ->collection('events')
            ->insert([
                'eventName'   => $event['eventName'],
                'module'      => $event['module'],
                'operation'   => $event['operation'],
                'referenceId' => $event['referenceId'],
                'userIds'     => $event['userIds'],
                'metadata'    => $event['metadata'],
                'timestamp'   => now(),
            ]);
    }
}