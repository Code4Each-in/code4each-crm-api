<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\SocialAccount;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class SocialAuthController extends Controller
{
    public function redirectToFacebook(Request $request)
    {
        $userId = $request->query('user_id');

        if (!$userId) {
            abort(400, 'User ID is required');
        }

        return Socialite::driver('facebook')
            ->stateless()
            ->with([
                'state' => encrypt(json_encode([
                    'user_id' => $userId,
                ])),
            ])
            ->setScopes(['public_profile'])
            ->redirect();
    }
    
    // public function handleFacebookCallback(Request $request)
    // {
    //     try {
    //         $facebookUser = Socialite::driver('facebook')->stateless()->user();
    
    //         logger()->info('Facebook user object', [
    //             'id' => $facebookUser->getId(),
    //             'name' => $facebookUser->getName(),
    //             'email' => $facebookUser->getEmail(),
    //             'nickname' => $facebookUser->getNickname(),
    //             'avatar' => $facebookUser->getAvatar(),
    //             'token' => $facebookUser->token,
    //             'refreshToken' => $facebookUser->refreshToken,
    //             'expiresIn' => $facebookUser->expiresIn,
    //             'raw' => $facebookUser->user, // FULL RAW RESPONSE
    //         ]);
    
    //         return response()->json([
    //             'message' => 'Facebook data logged successfully'
    //         ]);
    //     } catch (\Exception $e) {
    //         logger()->error('Facebook callback error', [
    //             'error' => $e->getMessage(),
    //         ]);
    
    //         return response()->json([
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }


    public function handleFacebookCallback(Request $request)
    {
        try {
            $state = json_decode(decrypt($request->get('state')), true);
            $userId = $state['user_id'] ?? null;

            if (!$userId) {
                throw new \Exception('User ID missing from OAuth state');
            }
            $facebookUser = Socialite::driver('facebook')
                ->stateless()
                ->user();
    
            SocialAccount::updateOrCreate(
                [
                    'account_id' => $facebookUser->getId(),
                    'platform'   => 'facebook',
                ],
                [
                    'user_id'          => $userId,
                    'name'             => $facebookUser->getName(),
                    'email'            => $facebookUser->getEmail(),
                    'avatar'           => $facebookUser->getAvatar(),
                    'access_token'     => $facebookUser->token,
                    'refresh_token'    => $facebookUser->refreshToken,
                    'token_expires_at' => $facebookUser->expiresIn
                        ? now()->addSeconds($facebookUser->expiresIn)
                        : null,
                    'raw_data'         => json_encode($facebookUser->user),
                ]
            );
    
            // Redirect to Netlify frontend
            return redirect()->away(
                'https://speedysites.netlify.app/social-media-settings?facebook=connected'
            );
    
        } catch (\Exception $e) {
            logger()->error('Facebook callback error', [
                'error' => $e->getMessage(),
            ]);
    
            return redirect()->away(
                'https://speedysites.netlify.app/social-media-settings?facebook=failed'
            );
        }
    }

}
