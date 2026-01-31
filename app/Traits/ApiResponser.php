<?php 
namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponser
{
    /**
     * Standardized Paginated Response
     */
    protected function paginatedResponse(LengthAwarePaginator $paginator, $resource)
    {
        return response()->json([
            'status' => 'success',
            'data' => $resource, // This will be your HotelResource::collection
            'pagination' => [
                'total' => $paginator->total(),
                'perPage' => $paginator->perPage(),
                'currentPage' => $paginator->currentPage(),
                'lastPage' => $paginator->lastPage(),
            ],
        ], 200);
    }

    /**
     * Standardized Success Response (for single items)
     */
    protected function successResponse($data, $message = null, $code = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $code);
    }
}