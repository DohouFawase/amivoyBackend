<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RouteSuggestionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'country' => ['sometimes', 'nullable', 'string', 'max:120'],
        ]);
        $routes = config('route_suggestions', []);

        if (! empty($filters['country'])) {
            $routes = array_values(array_filter(
                $routes,
                fn (array $route): bool => mb_strtolower($route['country']) === mb_strtolower($filters['country']),
            ));
        }

        return response()->json(['data' => $routes]);
    }
}
