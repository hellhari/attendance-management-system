<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Aws\Rekognition\RekognitionClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; 

class FaceController extends Controller
{
    public function scanFace(Request $request)
    {
        try {
            // 1. Process the incoming image from the Kiosk
            $imageData = $request->input('image');
            if (!$imageData) {
                return response()->json(['success' => false, 'message' => 'No camera image received.'], 400);
            }
            $image = str_replace('data:image/jpeg;base64,', '', $imageData);
            $image = str_replace(' ', '+', $image);
            $imageBytes = base64_decode($image);

            // 2. Connect to AWS Rekognition (Mumbai Region)
            $rekognition = new RekognitionClient([
                'region'    => env('AWS_DEFAULT_REGION', 'ap-south-1'),
                'version'   => 'latest',
            ]);

            // 3. Search the Collection for a Match
            $result = $rekognition->searchFacesByImage([
                'CollectionId' => 'pragnaware-employee-faces', 
                'Image' => ['Bytes' => $imageBytes],
                'MaxFaces' => 1,
                'FaceMatchThreshold' => 90, // Require a 90% confidence match
            ]);

            // 4. Handle the Result (Toggle Logic)
            if (!empty($result['FaceMatches'])) {
                
                // AWS returns the Employee ID we attached
                $employeeId = $result['FaceMatches'][0]['Face']['ExternalImageId'];
                $today = now()->toDateString();
                $currentTime = now()->toTimeString();

                // Look up the employee's most recent scan for today
                $lastRecord = DB::table('attendances')
                    ->where('emp_id', $employeeId)
                    ->where('attendance_date', $today)
                    ->orderBy('id', 'desc')
                    ->first();

                // Determine if this is a Check-In or Check-Out
                // If there is no record today, OR the last record was a Check-Out (0)
                if (!$lastRecord || $lastRecord->state == 0) {
                    $newState = 1; // Check-In
                    $statusMessage = 'Face Verified! Check-IN successful for Employee ID: ' . $employeeId;
                } else {
                    $newState = 0; // Check-Out
                    $statusMessage = 'Face Verified! Check-OUT successful for Employee ID: ' . $employeeId;
                }
                
                // Log the calculated status to the database
                DB::table('attendances')->insert([
                    'emp_id' => $employeeId,
                    'attendance_date' => $today,
                    'attendance_time' => $currentTime,
                    'status' => 1, 
                    'state' => $newState, 
                    'type' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => $statusMessage,
                    'state' => $newState // Sending the state back to the frontend
                ]);

            } else {
                // This gracefully handles BOTH incorrect faces and pitch-black/empty images
                return response()->json([
                    'success' => false,
                    'message' => 'No face detected or recognized. Please ensure you are well-lit and looking at the camera.'
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Attendance Check-in Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
        }
    }
}