<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PrintReceipt implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $html,
        public ?string $printerName
    ) {}

    public function broadcastOn(): array
    {
        // Channel ini akan didengarkan oleh Electron
        return [
            new Channel('nativephp'),
        ];
    }
}
