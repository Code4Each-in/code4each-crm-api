<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\SocialPost;
use App\Models\SocialPostPlatform;

class SocialAccountsController extends Controller
{
    /*
     ** This function will return connected platforms for the agency
    */
    public function getConnectedPlatforms()
    {
        $user = Auth::user();

        $accounts = SocialAccount::where('user_id', $user->id)
            ->get()
            ->keyBy('platform');
            
        return response()->json([
            'success' => true,

            'facebook' => [
                'connected' => $accounts->has('facebook'),
                'name'      => $accounts->get('facebook')->name ?? null,
                'avatar'    => $accounts->get('facebook')->avatar ?? null,
            ],

            'instagram' => [
                'connected' => $accounts->has('instagram'),
                'name'      => $accounts->get('instagram')->name ?? null,
                'avatar'    => $accounts->get('instagram')->avatar ?? null,
            ],

            'linkedin' => [
                'connected' => $accounts->has('linkedin'),
                'name'      => $accounts->get('linkedin')->name ?? null,
                'avatar'    => $accounts->get('linkedin')->avatar ?? null,
            ],
        ], 200);
    }


    /*
     ** This function will create a post on connected platforms
    */
    public function createPlatformPost(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'agency_id' => 'required',
            'caption' => 'required',
            'platforms' => 'required|array|min:1',
            'platforms.*' => 'string',
            'media' => 'required|file|mimes:jpg,jpeg,png,mp4,mov',
            'scheduled_at' => 'nullable|date',
        ]);

        DB::beginTransaction();

        try {
            /** ---------------- Upload Media ---------------- */
            $path = $request->file('media')->store('social-posts', 'public');

            /** ---------------- Create Social Post ---------------- */
            $post = SocialPost::create([
                'user_id'      => $request->user_id,
                'agency_id'    => $request->agency_id ?? null,
                'caption'      => $request->caption,
                'image'        => $path,
                'status'       => 'pending',
                'scheduled_at' => $request->scheduled_at,
            ]);

            /** ---------------- Insert Platforms ---------------- */
            foreach ($request->platforms as $platform) {
                SocialPostPlatform::create([
                    'social_post_id' => $post->id,
                    'platform'       => $platform,
                    'status'         => 'pending',
                    'response'       => null,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Post created successfully',
                'post_id' => $post->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create post',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}