<?php

namespace App\Http\Controllers;

use App\Models\Owner;
use App\Models\SystemSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use SimpleCalDAVClient;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

class ConfigTestController extends Controller
{
    /**
     * Test SMTP mail configuration.
     */
    public function testMail(Request $request): JsonResponse
    {

        $host = $request->input('mail_host');
        $port = (int) $request->input('mail_port');
        $user = $request->input('mail');
        $pass = $request->input('mail_pass');

        // Retrieve existing password if empty and entity_id is provided
        if (empty($pass) && $request->input('entity_id')) {
            $pass = $this->getExistingPassword(
                $request->input('entity_type'),
                $request->input('entity_id'),
                'mail_pass'
            );
        }

        if (! $host || ! $port || ! $user || ! $pass) {
            return response()->json([
                'success' => false,
                'message' => 'Configuration incomplète',
            ]);
        }

        try {
            $transport = new EsmtpTransport($host, $port, $port === 465);
            $transport->setUsername($user);
            $transport->setPassword($pass);
            $transport->getStream()->setTimeout(5);
            $transport->start();
            $transport->stop();

            return response()->json([
                'success' => true,
                'message' => 'Connexion SMTP réussie',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Échec de connexion : '.$this->sanitizeErrorMessage($e->getMessage()),
            ]);
        }
    }

    /**
     * Test CalDAV configuration.
     */
    public function testCaldav(Request $request): JsonResponse
    {
        $url = $request->input('dav_url');
        $user = $request->input('dav_user');
        $pass = $request->input('dav_pass');

        // Retrieve existing password if empty and entity_id is provided
        if (empty($pass) && $request->input('entity_id')) {
            $pass = $this->getExistingPassword(
                $request->input('entity_type'),
                $request->input('entity_id'),
                'dav_pass'
            );
        }

        if (! $url || ! $user || ! $pass) {
            return response()->json([
                'success' => false,
                'message' => 'Configuration incomplète',
            ]);
        }

        try {
            $client = new SimpleCalDAVClient;
            $client->connect(
                rtrim($url, '/').'/'.$user,
                $user,
                $pass
            );
            $client->findCalendars();

            return response()->json([
                'success' => true,
                'message' => 'Connexion CalDAV réussie',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Échec de connexion : '.$this->sanitizeErrorMessage($e->getMessage()),
            ]);
        }
    }

    /**
     * Test WebDAV configuration.
     */
    public function testWebdav(Request $request): JsonResponse
    {
        $endpoint = $request->input('webdav_endpoint');
        $user = $request->input('webdav_user');
        $pass = $request->input('webdav_pass');
        $savePath = $request->input('webdav_save_path');

        // Retrieve existing password if empty and entity_id is provided
        if (empty($pass) && $request->input('entity_id')) {
            $pass = $this->getExistingPassword(
                $request->input('entity_type'),
                $request->input('entity_id'),
                'webdav_pass'
            );
        }

        if (! $endpoint || ! $user || ! $pass) {
            return response()->json([
                'success' => false,
                'message' => 'Configuration incomplète',
            ]);
        }

        try {
            // Test WebDAV connection with a PROPFIND request
            $url = rtrim($endpoint, '/').'/';
            if ($savePath) {
                $url .= trim($savePath, '/').'/';
            }
            $url = str_replace(' ', '%20', $url);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_USERPWD, "{$user}:{$pass}");
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PROPFIND');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Depth: 0',
                'Content-Type: application/xml',
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, '<?xml version="1.0" encoding="utf-8"?><D:propfind xmlns:D="DAV:"><D:prop><D:resourcetype/></D:prop></D:propfind>');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            $response = curl_exec($ch);
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de connexion : '.$error,
                ]);
            }

            if ($statusCode === 207 || $statusCode === 200) {
                return response()->json([
                    'success' => true,
                    'message' => 'Connexion WebDAV réussie',
                ]);
            }

            $errorMessages = [
                401 => 'Authentification échouée',
                403 => 'Accès refusé',
                404 => 'Chemin non trouvé',
            ];

            $message = $errorMessages[$statusCode] ?? "Erreur HTTP {$statusCode}";

            return response()->json([
                'success' => false,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Échec de connexion : '.$this->sanitizeErrorMessage($e->getMessage()),
            ]);
        }
    }

    /**
     * Retrieve existing password from database.
     */
    private function getExistingPassword(string $entityType, int $entityId, string $field): ?string
    {
        if ($entityType === 'owner') {
            $owner = Owner::find($entityId);

            return $owner?->$field;
        }

        if ($entityType === 'system') {
            $settings = SystemSettings::first();

            return $settings?->$field;
        }

        return null;
    }

    /**
     * Sanitize error messages for display.
     */
    private function sanitizeErrorMessage(string $message): string
    {
        // Remove potentially sensitive information like passwords from error messages
        $message = preg_replace('/password[^\s]*/i', '***', $message);
        // Limit message length
        if (strlen($message) > 200) {
            $message = substr($message, 0, 200).'...';
        }

        return $message;
    }
}
