<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShippingService
{
    protected string $endpoint = 'https://jsonplaceholder.typicode.com/posts'; // Mock endpoint returning JSON

    /**
     * Create a shipment via external API.
     */
    public function createShipment(Order $order): Shipment
    {
        // Calculate mock weight
        $totalWeight = $order->items->sum('quantity') * 0.5; // 0.5kg per item

        $payload = [
            'order_id' => $order->id,
            'customer_name' => $order->user->name ?? 'Guest',
            'shipping_address' => '123 Test Address, City, Country', // Helper or mock
            'total_weight' => $totalWeight,
            'order_total' => $order->total_amount,
        ];

        try {
            // Using placeholder JSON API to simulate a request
            $response = Http::timeout(5)->post($this->endpoint, $payload);

            if ($response->successful()) {
                $status = 'created';
                $providerResponse = $response->json();

                // Mock extracted data directly since the mock API won't return shipment details
                $trackingNumber = 'TRK-' . strtoupper(uniqid());
                $labelUrl = 'https://example.com/labels/' . $trackingNumber . '.pdf';
                $shipmentId = 'SH-' . $response->json('id', uniqid());
            } else {
                $status = 'failed';
                $providerResponse = $response->json(); // Might be null or error structure
                Log::error('Shipping API returned error', [
                    'order_id' => $order->id,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                $trackingNumber = null;
                $labelUrl = null;
                $shipmentId = null;
            }
        } catch (\Exception $e) {
            $status = 'failed';
            $providerResponse = ['error' => $e->getMessage()];
            Log::error('Shipping API Exception', [
                'order_id' => $order->id,
                'message' => $e->getMessage()
            ]);
            $trackingNumber = null;
            $labelUrl = null;
            $shipmentId = null;
        }

        return Shipment::create([
            'order_id' => $order->id,
            'shipment_id' => $shipmentId,
            'tracking_number' => $trackingNumber,
            'label_url' => $labelUrl,
            'provider_response' => $providerResponse,
            'status' => $status,
        ]);
    }
}
