<?php

namespace App\Http\Controllers;

use App\Services\ControlCenterService;
use Illuminate\Http\Request;

class ControlCenterController extends Controller
{
    public function __construct(
        protected ControlCenterService $controlCenterService
    ) {}

    /**
     * Get all available positions
     */
    public function getPositions(Request $request)
    {
        $forceRefresh = $request->boolean('refresh', false);
        
        $positions = $this->controlCenterService->getPositions($forceRefresh);

        return response()->json($positions);
    }

    /**
     * Clear positions cache
     */
    public function clearCache()
    {
        $this->authorize('manage-staffings');

        $this->controlCenterService->clearCache();

        return response()->json(['message' => 'Cache cleared successfully']);
    }
}
