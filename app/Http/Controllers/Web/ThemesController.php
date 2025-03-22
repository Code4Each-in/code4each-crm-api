<?php

namespace App\Http\Controllers\Web;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\ComponentColorCombination;
use App\Models\FontFamily;
use App\Models\Theme;
use App\Models\ThemeDependency;
use App\Models\WebsiteCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class ThemesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $themes = Theme::all();
        return view('themes.index',compact('themes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Show  category On Create
        $category = WebsiteCategory::all();
        // Show  Fonts On Create
        $fonts = FontFamily::all();

        // Show  Fonts On Create
        $colors = ComponentColorCombination::all();

        return view('themes.create',compact('category','fonts','colors'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'primary_font' => 'required',
            'secondary_font' => 'nullable',
            'tertiary_font' => 'nullable',
            'category' => 'required',
            'preview_image' => 'required|file|mimes:jpg,jpeg,png,gif|max:5000',
            'default_color' => 'required',
            'dependencies' => 'required|array',
            'dependencies.*.name' => 'required',
            'dependencies.*.type' => 'required',
            'dependencies.*.path' => 'required',
            'dependencies.*.version' => 'required',
            'demo_url' => 'required|url',
            'status' => 'required',
            'accessibility' => 'required',
        ]);

        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator);
        }

        $validated = $validator->valid();

        $category = implode(",",$validated['category'] );
        $themes =  Theme::create([
            'name' => $validated['name'],
            'primary_font' => $validated['primary_font'],
            'secondary_font' => $validated['secondary_font'] ?? null,
            'tertiary_font' => $validated['tertiary_font'] ?? null,
            'category' => $category,
            'demo_url' => $validated['demo_url'],
            'default_color' => $validated['default_color'],
            'status' => $validated['status'],
            'accessibility' => $validated['accessibility'],
        ]);

        if ($request->hasFile('preview_image')) {
            $uploadedFile = $request->file('preview_image');
            $filename = time() . '_' . $uploadedFile->getClientOriginalName();
            $uploadedFile->storeAs('public/Themes', $filename);
            $path = 'Themes/' . $filename;
            $themes->preview_image = $path;
            $themes->save();
        }

        if($themes){
            //INSERT THE DEPEDENCIES INTO 'component_dependencies_crm'
            foreach ($validated['dependencies'] as $dependencyData) {
                ThemeDependency::create([
                    'theme_id' => $themes->id,
                    'name' => $dependencyData['name'],
                    'type' => $dependencyData['type'],
                    'path' => $dependencyData['path'],
                    'version' => $dependencyData['version'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }

        $message = "Theme Saved Successfully.";
        return redirect()->route('themes.index')->with('message', $message);
          
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
         // Show  category On Create
         $category = WebsiteCategory::all();
        
         // Show  Fonts On Create
         $fonts = FontFamily::all();
 
         // Show  Fonts On Create
         $colors = ComponentColorCombination::all();

        $themesData = Theme::with('dependencies')->find($id);
        return view('themes.edit',compact('category','fonts','colors','themesData'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'edit_name' => 'required|string|max:255',
            'edit_primary_font' => 'required',
            'edit_secondary_font' => 'nullable',
            'edit_tertiary_font' => 'nullable',
            'edit_category' => 'required',
            'edit_preview_image' => 'file|mimes:jpg,jpeg,png,gif|max:5000',
            'edit_default_color' => 'required',
            'edit_dependencies' => 'required|array',
            'edit_dependencies.*.name' => 'required',
            'edit_dependencies.*.type' => 'required',
            'edit_dependencies.*.path' => 'required',
            'edit_dependencies.*.version' => 'required',
            'edit_demo_url' => 'required|url',
            'edit_status' => 'required',
            'edit_accessibility' => 'required',
        ]);

        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator);
        }
        $validated = $validator->valid();
          //Assign All dependency Data from edit_dependencies
          $dependencyData = $validated['edit_dependencies'];

           // get detail of Component with id of Component
        $themeDetail = Theme::with('dependencies')->find($id);
        $dependencyDetail = $themeDetail->dependencies->toArray();
        $category = implode(",",$validated['edit_category'] );

        $themes =  Theme::where('id',$id)->update([
            'name' => $validated['edit_name'],
            'primary_font' => $validated['edit_primary_font'],
            'secondary_font' => $validated['edit_secondary_font'] ?? null,
            'tertiary_font' => $validated['edit_tertiary_font'] ?? null,
            'category' => $category,
            'demo_url' => $validated['edit_demo_url'],
            'default_color' => $validated['edit_default_color'],
            'status' => $validated['edit_status'],
            'accessibility' => $validated['edit_accessibility'],
        ]);

         //Only if Needs to update the preview image then this will update the image
         if ($request->hasFile('edit_preview_image')) {
            $oldFilePath = 'storage/'.$themeDetail->preview_image;
            //Delete The Old Stored Image in path And Upload New
            if (Helper::deleteFile($oldFilePath)) {
                $uploadedFile = $request->file('edit_preview_image');
                $filename = time() . '_' . $uploadedFile->getClientOriginalName();
                $uploadedFile->storeAs('public/Themes', $filename);
                $path = 'Themes/' . $filename;
                Theme::where('id',$id)->update(['preview_image' => $path]);
            } else {
                return back()->with("File $oldFilePath not found.");
            }

        }

        if($themes){
             //Create or Update the Dependencies
             foreach ($dependencyData as $dependency) {
                if (isset($dependency['id'])) {
                     ThemeDependency::where('id', $dependency['id'])
                        ->where('theme_id', $id)
                        ->update([
                            'name' => $dependency['name'],
                            'type' => $dependency['type'],
                            'path' => $dependency['path'],
                            'version' => $dependency['version'],
                            'updated_at' => now(),
                        ]);
                } else {
                      ThemeDependency::create([
                        'theme_id' => $id,
                        'name' => $dependency['name'],
                        'type' => $dependency['type'],
                        'path' => $dependency['path'],
                        'version' => $dependency['version'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            //Delete Removed Dependencies From dependencyArray
            $unmatchedDependencies = array_filter($dependencyDetail, function($record) use ($dependencyData) {
                return !in_array($record['id'], array_column($dependencyData, 'id'));
            });
            if($unmatchedDependencies){
                foreach ($unmatchedDependencies as  $umDependency) {
                    ThemeDependency::where('id',$umDependency['id'])->delete();
                }
            }
        }

        $message = "Component Updated Successfully.";
        return redirect()->back()->with('message', $message);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $themeDetail = Theme::with('dependencies')->find($id);
      
        foreach ($themeDetail->dependencies as $key => $dependency) {
            $oldFilePath = 'storage/' . $dependency->preview_image;
            
            // Delete the old stored image in the path and upload new
            if (Helper::deleteFile($oldFilePath)) {
                $message = "Files Deleted Successfully.";
            } else {
                return back()->with("File $oldFilePath not found.");
            }
            
            ThemeDependency::where('id', $dependency->id)->delete();
        }

         Theme::where('id',$id)->delete();
       
         $message = "Theme Deleted Successfully.";
        return redirect()->back()->with('message', $message);
    }
}
