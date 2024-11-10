<?php
namespace App\Http\Responses;
class ApiResponse
{
    public static function success( $message = 'Success', $statusCode = 200 , $data = [] )
    {
        return response()->json([
            'message' => $message,
            'status' => $statusCode,
            'error' => false,
            'data' => $data
        ], $statusCode);
    }

    public static function error($message = 'Error', $statusCode = 500, $data = [])
    {
        return response()->json([
            'message' => $message,
            'status' => $statusCode,
            'error' => true,
            'data' => $data
        ], $statusCode);
    }
}
