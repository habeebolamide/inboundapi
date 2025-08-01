<?php

function sendResponse($message, $result, $status)
{
    return response()->json([
        'success' => true,
        'data'    => $result,
        'message' => $message,
    ], $status);
}


function sendError($error, $errorMessages, $code)
{
    $response = [
        'success' => false,
        'message' => $error,
    ];

    if (!empty($errorMessages)) {
        $response['errors'] = $errorMessages;
    }

    return response()->json($response, $code);
}

function calculateDistance($lat1, $lng1, $lat2, $lng2)
{
    $earthRadius = 6371000; // meters

    $dLat = deg2rad($lat2 - $lat1);
    $dLng = deg2rad($lng2 - $lng1);

    $a = sin($dLat / 2) * sin($dLat / 2) +
        cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
        sin($dLng / 2) * sin($dLng / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return $earthRadius * $c; // returns distance in meters
}
