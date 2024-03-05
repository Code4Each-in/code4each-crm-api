<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class Helper
{
    public static function deleteFile($filePath)
    {
        try {
            if (file_exists($filePath)) {
                unlink($filePath);
                return "File $filePath has been deleted.";
            } else {
                return "File $filePath not found.";
            }
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            Log::error("\nError: $errorMessage");

            return $errorMessage;
        }
    }
}
