<?php

namespace App\Http\Controllers;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Forms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class GoogleAuthController extends Controller
{
    public function redirect(Request $request)
    {
        $client = new Client;

        // Configure Guzzle to use the cacert.pem file if it exists, or disable verify for local dev
        $guzzleOptions = [];
        if (file_exists(base_path('cacert.pem'))) {
            $guzzleOptions['verify'] = base_path('cacert.pem');
        } elseif (app()->environment('local')) {
            $guzzleOptions['verify'] = false;
        }
        $client->setHttpClient(new \GuzzleHttp\Client($guzzleOptions));

        // Load OAuth Client ID credentials
        $credentialPath = storage_path('app/private/oauth-credentials.json');

        if (! file_exists($credentialPath)) {
            return response()->json([
                'error' => 'Missing oauth-credentials.json. Please download your OAuth 2.0 Client ID JSON from Google Cloud Console and save it to storage/app/private/oauth-credentials.json',
            ], 400);
        }

        $client->setAuthConfig($credentialPath);
        $client->setRedirectUri(url('/auth/google/callback'));

        // Add required scopes
        $client->addScope(Forms::FORMS_BODY);
        $client->addScope(Drive::DRIVE);

        // Request offline access to get a refresh token
        $client->setAccessType('offline');
        $client->setPrompt('consent');

        $authUrl = $client->createAuthUrl();

        return redirect()->away($authUrl);
    }

    public function callback(Request $request)
    {
        $client = new Client;

        // Configure Guzzle to use the cacert.pem file if it exists, or disable verify for local dev
        $guzzleOptions = [];
        if (file_exists(base_path('cacert.pem'))) {
            $guzzleOptions['verify'] = base_path('cacert.pem');
        } elseif (app()->environment('local')) {
            $guzzleOptions['verify'] = false;
        }
        $client->setHttpClient(new \GuzzleHttp\Client($guzzleOptions));

        $credentialPath = storage_path('app/private/oauth-credentials.json');

        if (! file_exists($credentialPath)) {
            return response()->json(['error' => 'Missing oauth-credentials.json'], 400);
        }

        $client->setAuthConfig($credentialPath);
        $client->setRedirectUri(url('/auth/google/callback'));

        if ($request->has('code')) {
            $token = $client->fetchAccessTokenWithAuthCode($request->input('code'));

            if (isset($token['error'])) {
                return response()->json(['error' => $token['error_description'] ?? 'Failed to authenticate'], 400);
            }

            // Save the token to a file
            File::put(storage_path('app/private/google-token.json'), json_encode($token));

            return redirect('/');
        }

        return response()->json(['error' => 'No authorization code found in the request.'], 400);
    }
}
