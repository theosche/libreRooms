<?php

namespace App\Services\Webdav;
use App\Models\Owner;

class WebdavUploader
{
    private $username;
    private $password;
    private $basePath;
    private $webdavBaseUrl;

    public function setOwner(Owner $owner): WebdavUploader
    {
        $webdavSettings = $owner->webdavSettings();
        $this->username = $webdavSettings->user;
        $this->password = $webdavSettings->pass;
        $this->webdavBaseUrl = $webdavSettings->webdavUrl;
        $this->basePath = $webdavSettings->savePath;
        return $this;
    }
    public function uploadFileContents($fileContents, $destinationPath) {
        static $secondTry = false;
        $url = str_replace(' ', '%20', rtrim($this->webdavBaseUrl,'/') . '/' . trim($this->basePath,'/') . '/' . ltrim($destinationPath, '/'));
        $tempStream = fopen('php://temp', 'rw+');
        fwrite($tempStream, $fileContents);
        rewind($tempStream);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERPWD, "{$this->username}:{$this->password}");
        curl_setopt($ch, CURLOPT_PUT, true);
        curl_setopt($ch, CURLOPT_INFILE, $tempStream);
        curl_setopt($ch, CURLOPT_INFILESIZE, strlen($fileContents));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($tempStream);

        if ($statusCode === 201 || $statusCode === 204) { // 204 if overwrite
            $secondTry = false;
            return true;
        } else {
            if ($secondTry) {
                $secondTry = false;
                throw new \Exception("Failed to upload file. Status code: $statusCode. Response: $response");
            } else {
                $secondTry = true;
                $this->newFolder(self::getParent($destinationPath));
                $this->uploadFileContents($fileContents, $destinationPath);
            }
        }
    }

    public function uploadFile($filePath, $destinationPath) {
        static $secondTry = false;
        $url = str_replace(' ', '%20', rtrim($this->webdavBaseUrl,'/') . '/' . trim($this->basePath,'/') . '/' . ltrim($destinationPath, '/'));
        $fileContents = file_get_contents($filePath);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERPWD, "{$this->username}:{$this->password}");
        curl_setopt($ch, CURLOPT_PUT, true);
        curl_setopt($ch, CURLOPT_INFILE, fopen($filePath, 'r'));
        curl_setopt($ch, CURLOPT_INFILESIZE, strlen($fileContents));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($statusCode === 201 || $statusCode === 204) { // 204 if overwrite
            $secondTry = false;
            return true;
        } else {
            if ($secondTry) {
                $secondTry = false;
                throw new \Exception("Failed to upload file. Status code: $statusCode. Response: $response");
            } else {
                $secondTry = true;
                $this->newFolder(self::getParent($destinationPath));
                $this->uploadFile($filePath, $destinationPath);
            }
        }
    }

    public function delete($destinationPath) {
        $url = str_replace(' ', '%20', rtrim($this->webdavBaseUrl,'/') . '/' . trim($this->basePath,'/') . '/' . ltrim($destinationPath, '/'));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERPWD, "{$this->username}:{$this->password}");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($statusCode === 204) {
            return true;
        } elseif ($statusCode === 404) {
            throw new \Exception("File or folder not found: $destinationPath");
        } else {
            throw new \Exception("Failed to delete file or folder. Status code: $statusCode. Response: $response");
        }
    }

    /**
     * Delete a file silently, ignoring errors (404, connection issues, etc.)
     * Use this for cleanup operations where failure should not block the main process.
     */
    public function deleteSilent($destinationPath): bool
    {
        try {
            $this->delete($destinationPath);
            return true;
        } catch (\Exception $e) {
            // Silently ignore all errors
            return false;
        }
    }

    public function newFolder($destinationPath) {
        $url = str_replace(' ', '%20', rtrim($this->webdavBaseUrl,'/') . '/' . trim($this->basePath,'/') . '/' . trim($destinationPath, '/') . '/');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERPWD, "{$this->username}:{$this->password}");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'MKCOL');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($statusCode === 201) {
            return true;
        } elseif ($statusCode === 405) {
            throw new \Exception("Folder already exists: $destinationPath");
        } else {
            $parent = self::getParent($destinationPath);
            if ($parent == "") {
                throw new \Exception("Could not create folder structure");
            } else {
                $this->newFolder(self::getParent($destinationPath));
                $this->newFolder($destinationPath);
            }
        }
    }

    private static function getParent($path): string
    {
        $arr = explode('/', trim($path,'/'));
        array_pop($arr);
        return(implode('/',$arr));
    }
}

