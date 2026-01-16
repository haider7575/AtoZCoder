<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessShipment implements ShouldQueue
{
    use Queueable;

    public $order;

    /**
     * Create a new job instance.
     */
    public function __construct(\App\Models\Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     */
    public function handle(\App\Services\ShippingService $shippingService): void
    {
        // Don't process if already has shipment
        if ($this->order->shipment) {
            return;
        }

        $shippingService->createShipment($this->order);
    }
}
