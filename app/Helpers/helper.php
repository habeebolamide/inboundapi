<?php

function sendResponse($message,$result,$status)
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