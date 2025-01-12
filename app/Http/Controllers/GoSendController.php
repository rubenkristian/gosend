<?php

namespace App\Http\Controllers;

use App\Services\Gosend\GosendService;
use Illuminate\Http\Request;

class GoSendController extends Controller
{
    private $goSendService;

    public function __construct()
    {
        $this->goSendService = new GosendService();
    }

    public function createBooking(Request $request)
    {
        $request->validate([
            'shipment_method' => 'required|string',
            'origin_name' => 'required|string',
            'origin_note' => 'string',
            'origin_contact_name' => 'string',
            'origin_contact_phone' => 'string',
            'origin_lat' => 'required|numeric',
            'origin_long' => 'required|numeric',
            'origin_address' => 'required|string',
            'destination_name' => 'required|string',
            'destination_contact_name' => 'required|string',
            'destination_contact_phone' => 'required|string',
            'destination_lat' => 'required|numeric',
            'destination_long' => 'required|numeric',
            'destination_address' => 'required|string',
            'item' => 'required|array',
            'store_order_id' => 'required|string'
        ]);

        $response = $this->goSendService->booking([
            "paymentType" => 0,
            "shipment_method" => $request->shipment_method,
            "routes" => [
                [
                    "originName" => $request->origin_name,
                    "originNote" => $request->origin_note,
                    "originContactName" => $request->origin_contact_name,
                    "originContactPhone" => $request->origin_contact_phone,
                    "originLatLong" => "$request->origin_lat,$request->origin_long",
                    "originAddress" => $request->origin_address,
                    "destinationName" => $request->destination_name,
                    "destinationContactName" => $request->destination_contact_name,
                    "destinationContactPhone" => $request->destination_contact_phone,
                    "destinationLatLong" => "$request->destination_lat,$request->destination_long",
                    "destinationAddress" => $request->destination_address,
                    "item" => implode(",", $request->item),
                    "storeOrderId" => $request->store_order_id,
                ]
            ]
        ]);

        if ($response['code'] == 201) {
            return response()->json([
                'data' => $response['data']
            ], 201);
        }
        
        return response()->json([
            'message' => $response['message'],
        ], $response['code']);
    }

    public function cancelBooking(string $orderNo)
    {
        $response = $this->goSendService->cancel($orderNo);
    }

    public function orderDetail(string $orderId)
    {
        $response = $this->goSendService->getTrackingHistory($orderId);
    }

    public function estimate(Request $request)
    {
        $request->validate([
            'origin' => 'required|string',
            'destination' => 'required|string',
        ]);

        $response = $this->goSendService->getShippingCost($request->origin, $request->destination);
    }
}
