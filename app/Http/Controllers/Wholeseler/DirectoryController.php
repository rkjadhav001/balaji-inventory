<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\helper\Helper;
use App\Models\Booking;
use App\Models\Owner;
use App\Models\Plot;
use App\Models\Floor;

class DirectoryController extends Controller
{
    public function directoryList(Request $request) {
        $data = Helper::get_token($request);
        if ($data['success'] == 1) {
            $floors = Floor::with(['plots.booking.owner:id,name,number', 'plots.booking.tenant:id,name,number'])
                ->select('id', 'name')
                ->whereHas('plots', function ($q) use ($request) {
                    $q->where('tower_id', $request->tower_id)
                    ->where('status', 1);
                })
                ->get();

                // Image fields Static to dynamic
            $baseUrl = asset('uploads/user_profile/default.png');
            $floors = $floors->map(function ($floor) use($baseUrl) {
                return [
                    'id' => $floor->id,
                    'name' => $floor->name,
                    'plots' => $floor->plots->map(function ($plot) use($baseUrl) {
                        $booking = $plot->booking;

                        $owner = $booking && $booking->owner ? $booking->owner->only(['id', 'name', 'number', 'image']) : null;
                        $tenant = $booking && $booking->tenant ? $booking->tenant->only(['id', 'name', 'number', 'image']) : null;
                        if($booking && $booking->owner) {
                            $owner['image'] = $baseUrl;
                        }
                        if($booking && $booking->owner) {
                            $tenant['image'] = $baseUrl;
                        }

                        return [
                            'id' => $plot->id,
                            'floor_id' => $plot->floor_id,
                            'plot_number' => $plot->plot_number,
                            'owner' => $owner,
                            'tenant' => $tenant,
                        ];
                    }),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'floors' => $floors
                ]
            ]);
        }
        else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001','success' => 'false', 'message' => 'Unauthorized.']);
            return response()->json([
                'data' => $errors
            ], 200);
        }
    }
}
