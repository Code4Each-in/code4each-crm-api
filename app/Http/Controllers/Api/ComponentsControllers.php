<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgencyWebsite;
use App\Models\Component;
use App\Models\ComponentDependency;
use App\Models\User;
use App\Models\Websites;
use App\Notifications\CommonEmailNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class ComponentsControllers extends Controller
{
    public function getComponent()
    {
        $response = [
            'success' => false,
            'status' => 400,
        ];
        $component = Component::first();
        $componentDetail['component'] = $component;
        $componentDependencies = ComponentDependency::where('component_id',$component->id)->get();
        if(  $componentDependencies){
            $componentDetail['component_dependencies'] = $componentDependencies;
        }
        $response = [
            'success' => true,
            'status' => 200,
            'data' => $componentDetail,
        ];
        return response()->json($response);
    }

    public function index()
    {
        $response = Http::get('http://jsonplaceholder.typicode.com/posts');

        $jsonData = $response->json();

        dd($jsonData);
    }

    public function store()
    {
        $response = Http::post('http://jsonplaceholder.typicode.com/posts', [
                    'title' => 'This is test from tutsmake.com',
                    'body' => 'This is test from tutsmake.com as body',
                ]);
            dd($response);
        dd($response->successful());
    }

    public function agencyWebsiteDetails(Request $request)
    {
        $response = [
            'success' => false,
            'status' => 400,
        ];
        $validator = Validator::make($request->all(), [
            'agency_id' => 'required',
            'category_id' => 'required',
            'description' => 'nullable|string',
            'address' => 'required',
            'business_name' => 'required',
            'logo' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $validate = $validator->valid();
        try {
            DB::beginTransaction();
            $websitesData = Websites::where('assigned', null)->first();
            $agencyWebsiteDetails = AgencyWebsite::create([
                'website_category_id' => $validate['category_id'],
                'address' => $validate['address'],
                'description'  => $validate['description'] ?? null,
                'agency_id' => $validate['agency_id'],
                'business_name' => $validate['business_name'],
                'created_by' => auth()->user()->id,
            ]);
            if ($request->hasFile('logo')) {
                $uploadedFile = $request->file('logo');
                $filename = time() . '_' . $uploadedFile->getClientOriginalName();
                $uploadedFile->storeAs('public/AgencyWebsiteDetails', $filename);
                $path = 'AgencyWebsiteDetails/' . $filename;
                $agencyWebsiteDetails->logo = $path;
                $agencyWebsiteDetails->save();

            }
            DB::commit();

            if($agencyWebsiteDetails->agency_id && $websitesData->website_domain){
                $result = $this->sendComponentToWordpress($agencyWebsiteDetails->agency_id, $websitesData->website_domain);
                if ($result['data']['success'] == true && $result['data']['status'] == 200) {
                    $websiteUrl = $result['data']['domain'];

                    //assigned site to user
                    AgencyWebsite::where('id', $agencyWebsiteDetails->id)->update(['website_id' => $websitesData->id]);
                    Websites::where('id', $websitesData->id)->update(['assigned' => $agencyWebsiteDetails->id]);

                    // send mail to user
                    $recipient = User::find($agencyWebsiteDetails->created_by);
                    $messages = [
                        'greeting-text' => "Hey User,",
                        'subject' => 'Your Domain is Ready',
                        'additional-info' => '',
                        'lines_array' => [
                            'title' => 'Your domain is now ready for use after successfully updating your agency details. Enjoy a seamless online presence with the latest information.',
                            'body-text' => 'Here Is The Details For Your Website',
                            'special_Agency_Name' => $agencyWebsiteDetails->business_name ,
                            'special_Domain_Name' => $websiteUrl,
                        ],
                    ];
                    $recipient->notify(new CommonEmailNotification($messages));
                    $response = [
                        'message' => "Agency Website Detail Saved Successfully.",
                        'success' => true,
                        'status' => 200,
                    ];
                } else {
                    DB::rollBack();
                    $response = [
                        'message' => "An error occurred.",
                        'success' => false,
                        'status' => 400,
                    ];
                }
            }


        } catch (\Exception $e) {
            DB::rollBack();
            $errorMessage = $e->getMessage();
            $errorCode = $e->getCode();
            $errorFile = $e->getFile();
            $errorLine = $e->getLine();
            // Logging the error in log file
            \Log::error("\nError: $errorMessage\nFile: $errorFile\nLine: $errorLine \nCode:$errorCode");

            $response = [
                'message' => "An error occurred.",
                'success' => false,
                'status' => 500,
                'error' => $e->getMessage(),
            ];
        } finally {
            DB::commit();
        }
        return response()->json($response);
    }

    public function sendComponentToWordpress($agency_id, $websiteUrl){
        $response = [
            'success' => false,
        ];
        $agencyId = $agency_id;
        $agencyWebsiteDetail = AgencyWebsite::with('websiteCategory')->where('agency_id',$agencyId)->where('status','active')->first();
        $websiteCategory = $agencyWebsiteDetail->websiteCategory->name;
        $components = DB::table('components_crm')
        ->whereIn('type', ['header', 'footer', 'about_section', 'service_section', 'section'])
        ->where('category', $websiteCategory)
        ->whereIn('id', function ($query) use ($websiteCategory) {
            $query->select(DB::raw('MIN(id)'))
                ->from('components_crm')
                ->whereIn('type', ['header', 'footer', 'about_section', 'service_section', 'section'])
                ->where('category', $websiteCategory)
                ->groupBy('type');
        })
        ->get();
        $position = 1;
        foreach ($components as $component) {
            $componentData = [
                'component_detail' => [
                    'component_name' => $component->component_name,
                    'path' => $component->path,
                    'type' => $component->type,
                    'position' => null,
                    'status' => $component->status,
                ],
                'component_dependencies' => ComponentDependency::where('component_id', $component->id)
                    ->select('component_id', 'name', 'type', 'path', 'version')
                    ->get(),
            ];

            if ($component->type === 'header') {
                $componentData['component_detail']['position'] = 1;
            } elseif ($component->type === 'footer') {
                $componentData['component_detail']['position'] = count($components);
            } else {
                $componentData['component_detail']['position'] = $position + 1;
                $position++;
            }
            $postUrl = $websiteUrl . 'wp-json/v1/component';
            $componentResponse = Http::post($postUrl, $componentData );
            if ($componentResponse->successful()) {
                $data['response'] = $componentResponse->json();
                $data['status'] = $componentResponse->status();
                $data['success'] = true ;
                $data['domain'] = $websiteUrl;
            } else {
                $errorCode = $componentResponse->status();
                $errorData = $componentResponse->json();
                $data['error'] = $errorData . ' '. $errorCode;
                $data['success'] = false ;
            }
        }
        $response = [
            'data' => $data,
        ];
        return $response;
    }

}
