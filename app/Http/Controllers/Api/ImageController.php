<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\WordpressComponentController;


class ImageController extends Controller
{

    private $wordpressComponentClass;
    public function __construct()
    {
       $this->wordpressComponentClass = new WordpressComponentController();
    }

    public function uploadImages(Request $request)
    {

        $response = [
            'success' => false,
            'status' => 400,
        ];

        if (!$request->hasFile('uploaded_images')) {
            return response()->json(['upload_file_not_found'], 400);
        }

        if (!$request->type) {
            return response()->json(['component type is required'], 400);
        }
        if (!$request->website_url) {
            return response()->json(['website url  is required'], 400);
        }
        $website_url = $request->website_url;
        $allowedFileExtensions = ['pdf', 'jpg', 'png'];
        $files = $request->file('uploaded_images');
        $fileArray = [];
        $imageUrls = []; // Modified array for image URLs

        foreach ($files as $file) {
            $extension = $file->getClientOriginalExtension();
            $check = in_array($extension, $allowedFileExtensions);

            if ($check) {
                $media_ext = $file->getClientOriginalName();
                $media_no_ext = pathinfo($media_ext, PATHINFO_FILENAME);
                $filename = $media_no_ext . '-' . uniqid() . '.' . $extension;
                $file->storeAs('public/HeaderImages', $filename);
                $path = url('/') . '/storage/HeaderImages/' . $filename;

                // Add the image path to the modified array
                $imageUrls[] = $path;
            } else {
                return response()->json(['invalid_file_format'], 422);
            }
        }

        $fileArray['images-url'] = $imageUrls; // Modify the key
        $fileArray['type'] = $request->type;

        $uploadComponentImagesResponse = $this->wordpressComponentClass->uploadComponentImages(
            $website_url,
            $fileArray
        );

        if ($uploadComponentImagesResponse['success'] && $uploadComponentImagesResponse['response']['status'] == 200) {
            $response = [
                "message" => "Images Uploaded Successfully.",
                'success' => true,
                'status' => 200,
            ];

        }
        return response()->json($response);
    }

    public function getComponentImages()
    {
        $response = [
            'success' => false,
            'status' => 400,
        ];
        if (!request()->website_url) {
            return response()->json(['website url  is required'], 400);
        }
        if (!request()->type) {
            return response()->json(['type is required'], 400);
        }

        $request =  request()->all();
        $website_url = $request['website_url'];
        $type = $request['type'];

        $getUploadedImagesResponse = $this->wordpressComponentClass->getUploadedImages(
            $website_url,
            $type
        );

        if ($getUploadedImagesResponse['success'] && $getUploadedImagesResponse['response']['status'] == 200) {
            $response = [
                "message" => "Images Fetched Successfully.",
                'data' => $getUploadedImagesResponse['response']['data'],
                'success' => true,
                'status' => 200,
            ];

        }

        return response()->json($response);

    }


    public function deleteUploadedImages(Request $request)
    {
        $response = [
            'success' => false,
            'status' => 400,
        ];
        if (!request()->website_url) {
            return response()->json(['website url  is required'], 400);
        }
        if (!request()->delete_images) {
            return response()->json(['array data is required  to delete images'], 400);
        }

        $delete_images = $request->delete_images;
        $website_url = $request->website_url;

        $deleteUploadedImagesResponse = $this->wordpressComponentClass->deleteComponentUploadedImages(
            $website_url,
            $delete_images
        );

        if ($deleteUploadedImagesResponse['success'] && $deleteUploadedImagesResponse['response']['status'] == 200) {
            $response = [
                "message" => "Images Deleted Successfully.",
                'success' => true,
                'status' => 200,
            ];

        }
        return response()->json($response);
    }
}
