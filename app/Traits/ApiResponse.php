<?php

namespace App\Traits;

trait ApiResponse
{
    protected function success($data = null, $message = 'Success', $code = 200)
    {
        return response()->json([
            'error' => false,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function error($message, $code = 400, $errors = null)
    {
        $response = [
            'error' => true,
            'message' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }
}
