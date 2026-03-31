<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class ApiController extends Controller
{
    /**
     * Return a standardized success response
     */
    protected function success($data, $message = 'Success', $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Return a standardized error response
     */
    protected function error($message = 'Error', $code = 400, $errors = null)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Return a paginated response
     */
    protected function paginated($data, $message = 'Success')
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'count' => $data->total(),
            'next' => $data->nextPageUrl(),
            'previous' => $data->previousPageUrl(),
            'results' => $data->items(),
        ]);
    }
}
