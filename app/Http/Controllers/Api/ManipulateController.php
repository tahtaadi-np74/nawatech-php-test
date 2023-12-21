<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Exception;

class ManipulateController extends Controller
{
    function getJsonData($file) {
        $path = resource_path($file);

        if (File::exists($path)) {
            $json_content = File::get($path);

            $data = json_decode($json_content, true);
            return $data;
        }

        return null;
    }

    function mergeData($data_1, $data_2) {
        $workDict = collect($data_2['data'])->keyBy('code');

        return collect($data_1['data'])->map(function ($item) use ($workDict) {
            $workCode = $item['booking']['workshop']['code'];
            $workDetail = $workDict[$workCode] ?? null;

            return [
                'name' => $item['name'],
                'email' => $item['email'],
                'booking_number' => $item['booking']['booking_number'],
                'book_date' => $item['booking']['book_date'],
                'ahass_code' => $item['booking']['workshop']['code'],
                'ahass_name' => $item['booking']['workshop']['name'],
                'ahass_address' => $workDetail['address'] ?? '',
                'ahass_contact' => $workDetail['phone_number'] ?? '',
                'ahass_distance' => $workDetail['distance'] ?? 0,
                'motorcycle_ut_code' => $item['booking']['motorcycle']['ut_code'],
                'motorcycle' => $item['booking']['motorcycle']['name']
            ];
        })->sortBy('ahass_distance')->values()->all();
    }

    function getData() {
        try {
            $data_1 = $this->getJsonData('json/data-1.json');
            $data_2 = $this->getJsonData('json/data-2.json');

            if (!$data_1 || !$data_2) {
                return response()->json(['error' => 'JSON file not found.'], 404);
            }

            $merge_data = $this->mergeData($data_1, $data_2);

            return response()->json([
                'status' => 1,
                'message' => 'Data Successfully Retrieved',
                'data' => $merge_data
            ], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'There is an error when processing the data.'], 500);
        }
    }
}
