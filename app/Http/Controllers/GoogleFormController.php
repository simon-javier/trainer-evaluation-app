<?php

namespace App\Http\Controllers;

use App\Services\GoogleFormService;
use Illuminate\Http\Request;

class GoogleFormController extends Controller
{
    public function store(Request $request, GoogleFormService $googleFormService)
    {
        $trainerName = $request->input('trainer_name', 'Unknown Trainer');
        $courseName = $request->input('course_name', 'Unknown Course');
        $startDate = $request->input('start_date', date('Y-m-d'));
        $endDate = $request->input('end_date', date('Y-m-d'));

        try {
            $result = $googleFormService->createForm($trainerName, $courseName, $startDate, $endDate);
            $form = $result['form'];

            return response()->json([
                'success' => true,
                'form_id' => $form->formId,
                'view_url' => $form->responderUri,
                'edit_url' => 'https://docs.google.com/forms/d/'.$form->formId.'/edit',
                'folder_url' => $result['root_folder_url'],
            ]);
        } catch (\Exception $e) {
            $errorMsg = $e->getMessage();
            if (str_contains($errorMsg, 'backendError') || str_contains($errorMsg, 'Internal error')) {
                $errorMsg .= ' -> This 500 error is usually caused because the Google Drive API is not enabled on your GCP project. Please enable it here: https://console.developers.google.com/apis/api/drive.googleapis.com';
            }

            return response()->json([
                'success' => false,
                'error' => $errorMsg,
            ], 500);
        }
    }
}
