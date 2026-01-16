<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function handleShipping(Request $request)
    {
        $signature = $request->header('X-Signature');
        $payload = $request->getContent();
        $secret = config('app.shipping_webhook_secret') ?? env('SHIPPING_WEBHOOK_SECRET');

        $computedSignature = hash_hmac('sha256', $payload, $secret);

        if (!hash_equals($computedSignature, (string)$signature)) {
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $data = json_decode($payload, true);
        $event = $data['event'] ?? '';
        $shipmentId = $data['shipment_id'] ?? '';

        $shipment = \App\Models\Shipment::where('shipment_id', $shipmentId)->first();

        if (!$shipment) {
            return response()->json(['message' => 'Shipment not found'], 404);
        }

        if ($event === 'shipment.delivered') {
            $shipment->status = 'delivered';
            $shipment->save();
        } elseif ($event === 'shipment.failed') {
            $shipment->status = 'failed';
            $shipment->save();
        }

        return response()->json(['message' => 'Webhook handled']);
    }
}
