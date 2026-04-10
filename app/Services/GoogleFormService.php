<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Forms;
use Google\Service\Forms\BatchUpdateFormRequest;
use Google\Service\Forms\Form;
use Google\Service\Forms\Info;

class GoogleFormService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client;

        // Disable SSL verification for local development to fix cURL error 60
        if (app()->isLocal()) {
            $httpClient = new \GuzzleHttp\Client([
                'verify' => false,
            ]);
            $this->client->setHttpClient($httpClient);
        }

        // Load OAuth Client ID credentials
        $credentialPath = storage_path('app/private/oauth-credentials.json');

        if (file_exists($credentialPath)) {
            $this->client->setAuthConfig($credentialPath);
        }

        $this->client->addScope([
            Forms::FORMS_BODY,
            Drive::DRIVE,
        ]);

        $tokenPath = storage_path('app/private/google-token.json');

        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $this->client->setAccessToken($accessToken);

            // Refresh the token if it's expired
            if ($this->client->isAccessTokenExpired()) {
                if ($this->client->getRefreshToken()) {
                    $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                    file_put_contents($tokenPath, json_encode($this->client->getAccessToken()));
                } else {
                    throw new \Exception('Access token expired and no refresh token found. Please authenticate again at /auth/google');
                }
            }
        } else {
            throw new \Exception('Google authentication token missing. Please authenticate at /auth/google first.');
        }
    }

    private function getOrCreateFolder(Drive $driveService, string $folderName, ?string $parentId = null): string
    {
        $q = "mimeType='application/vnd.google-apps.folder' and name='".str_replace("'", "\\'", $folderName)."' and trashed=false";
        if ($parentId) {
            $q .= " and '{$parentId}' in parents";
        } else {
            $q .= " and 'root' in parents";
        }

        $results = $driveService->files->listFiles([
            'q' => $q,
            'spaces' => 'drive',
            'fields' => 'files(id, name)',
        ]);

        if (count($results->getFiles()) > 0) {
            return $results->getFiles()[0]->getId();
        }

        $folderMetadata = new DriveFile([
            'name' => $folderName,
            'mimeType' => 'application/vnd.google-apps.folder',
        ]);

        if ($parentId) {
            $folderMetadata->setParents([$parentId]);
        }

        $folder = $driveService->files->create($folderMetadata, [
            'fields' => 'id',
        ]);

        return $folder->getId();
    }

    public function createForm(string $trainerName, string $courseName, string $startDate, string $endDate): array
    {
        $formsService = new Forms($this->client);
        $driveService = new Drive($this->client);

        // Organize folders: Trainer Evaluations / trainer_name / course_name / date
        $rootFolderId = $this->getOrCreateFolder($driveService, 'Trainer Evaluations');
        $trainerFolderId = $this->getOrCreateFolder($driveService, $trainerName, $rootFolderId);
        $courseFolderId = $this->getOrCreateFolder($driveService, $courseName, $trainerFolderId);

        $folderDateName = $startDate === $endDate ? $startDate : "{$startDate} to {$endDate}";
        $dateFolderId = $this->getOrCreateFolder($driveService, $folderDateName, $courseFolderId);

        $rootFolder = $driveService->files->get($rootFolderId, ['fields' => 'webViewLink']);
        $rootFolderUrl = $rootFolder->webViewLink;

        $title = "{$courseName} - {$trainerName}";

        $formInfo = new Info;
        $formInfo->setTitle($title);
        $formInfo->setDocumentTitle("{$courseName} - {$trainerName}");

        $form = new Form;
        $form->setInfo($formInfo);

        $createdForm = $formsService->forms->create($form);

        // Move form to the generated date folder
        $fileId = $createdForm->formId;
        $file = $driveService->files->get($fileId, ['fields' => 'parents']);
        $previousParents = implode(',', $file->parents);

        $emptyFile = new DriveFile;
        $driveService->files->update($fileId, $emptyFile, [
            'addParents' => $dateFolderId,
            'removeParents' => $previousParents,
            'fields' => 'id, parents',
        ]);

        $requests = [];
        $index = 0;

        $displayDate = $startDate === $endDate
            ? date('F j, Y', strtotime($startDate))
            : date('F j, Y', strtotime($startDate)).' to '.date('F j, Y', strtotime($endDate));

        // Update Description
        $requests[] = [
            'updateFormInfo' => [
                'info' => [
                    'description' => "Trainer Name: {$trainerName}\nCourse Name: {$courseName}\nDate: {$displayDate}",
                ],
                'updateMask' => 'description',
            ],
        ];

        $likertQuestions = [
            '1. The training met my expectations',
            '2. I will be able to apply the knowledge learned',
            '3. The training objectives for each topic were identified and followed',
            '4. The content was organized and easy to follow',
            '5. The materials distributed were pertinent and useful',
            '6. The trainer was knowledgeable',
            '7. The quality of instruction was good',
            '8. The trainer met the training objectives',
            '9. Class participation and interaction were encouraged',
            '10. Adequate time was provided for questions and discussion',
        ];

        foreach ($likertQuestions as $q) {
            $requests[] = [
                'createItem' => [
                    'item' => [
                        'title' => $q,
                        'questionItem' => [
                            'question' => [
                                'required' => true,
                                'scaleQuestion' => [
                                    'low' => 1,
                                    'high' => 5,
                                    'lowLabel' => 'Strongly Disagree',
                                    'highLabel' => 'Strongly Agree',
                                ],
                            ],
                        ],
                    ],
                    'location' => ['index' => $index++],
                ],
            ];
        }

        // Q11
        $requests[] = [
            'createItem' => [
                'item' => [
                    'title' => '11. How do you rate the training overall?',
                    'questionItem' => [
                        'question' => [
                            'required' => true,
                            'scaleQuestion' => [
                                'low' => 1,
                                'high' => 5,
                                'lowLabel' => 'Very Poor',
                                'highLabel' => 'Excellent',
                            ],
                        ],
                    ],
                ],
                'location' => ['index' => $index++],
            ],
        ];

        // Q12
        $requests[] = [
            'createItem' => [
                'item' => [
                    'title' => '12. What aspects of the training could be improved?',
                    'questionItem' => [
                        'question' => [
                            'textQuestion' => [
                                'paragraph' => true,
                            ],
                        ],
                    ],
                ],
                'location' => ['index' => $index++],
            ],
        ];

        // Q13
        $requests[] = [
            'createItem' => [
                'item' => [
                    'title' => '13. Other comments?',
                    'questionItem' => [
                        'question' => [
                            'textQuestion' => [
                                'paragraph' => true,
                            ],
                        ],
                    ],
                ],
                'location' => ['index' => $index++],
            ],
        ];

        $batchUpdateRequest = new BatchUpdateFormRequest([
            'requests' => $requests,
        ]);

        $formsService->forms->batchUpdate($createdForm->formId, $batchUpdateRequest);

        // Fetch the QR Code image
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=500x500&data='.urlencode($createdForm->responderUri);
        $qrImageContent = file_get_contents($qrUrl);

        if ($qrImageContent !== false) {
            // Save this image to a temporary file locally
            $tempFilePath = sys_get_temp_dir().'/qr_code_'.uniqid().'.png';
            file_put_contents($tempFilePath, $qrImageContent);

            // Set metadata including parent folder and name
            $qrFileMetadata = new DriveFile([
                'name' => "QR Code - {$courseName}.png",
                'parents' => [$dateFolderId],
            ]);

            // Upload to Google Drive
            $driveService->files->create($qrFileMetadata, [
                'data' => file_get_contents($tempFilePath),
                'mimeType' => 'image/png',
                'uploadType' => 'multipart',
            ]);

            // Clean up the temporary file
            unlink($tempFilePath);
        }

        return [
            'form' => $createdForm,
            'root_folder_url' => $rootFolderUrl,
        ];
    }
}
