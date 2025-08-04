<?php

namespace App\Http\Controllers;
use App\Models\Component;
use App\Models\WebsiteCategory;
use App\Models\WebsiteTemplate;
use App\Models\WebsiteTemplateComponent;
use Illuminate\Http\Request;

class WebsiteController extends Controller
{
    public function index()
    {
        $components = Component::all();
        $categories = WebsiteCategory::all();
        $templates = WebsiteTemplate::with(['components.component'])->get();

        // Optional: Format data for easier handling in the view
        $templates = $templates->map(function ($template) {
            return [
                'id' => $template->id,
                'template_name' => $template->template_name,
                'categories' => $template->category_id,
                'previewLink' => $template->preview_link,
                'status' => $template->status,
                'preview' => $template->featured_image,
                'components' => $template->components->map(function ($component) {
                    return $component->component ? $component->component->component_name : null;
                })->toArray(), // Convert to array for implode
            ];
        });
        // dd($templates);
        return view('websites.index',compact('components', 'categories', 'templates'));
    }

    public function store(Request $request)
    {   
        $request->validate([
            'templateName' => 'required|string|max:255',
            'category' => 'required|array',
            'category.*' => 'exists:website_categories,name',
            'previewLink' => 'required|string|max:255',
            'previewImage' => 'required|image',
            'status' => 'required|in:draft,testing,active,deactive',
            'component' => 'required|array',
            'component.*' => 'exists:components_crm,component_unique_id',
        ]);
        $categories = implode(',', $request->input('category'));
        $imagepath = null;
        if ($request->hasFile('previewImage')) {
            $uploadedFile = $request->file('previewImage');
            $filename = time() . '_' . $uploadedFile->getClientOriginalName();
            $uploadedFile->storeAs('public/WebsiteTemplates', $filename);
            $imagepath = 'WebsiteTemplates/' . $filename;
        }

        // Insert data into Website Templates table
        $template = WebsiteTemplate::create([
            'template_name' => $request->input('templateName'),
            'category_id' => $categories,
            'preview_link' => $request->input('previewLink'),
            'featured_image' => $imagepath,
            'status' => $request->input('status'),
        ]);

        // Insert data into Website Template Components table
        foreach ($request->input('component') as $index => $componentId) {
            WebsiteTemplateComponent::create([
                'template_id' => $template->id,
                'component_unique_id' => $componentId,
                'position' => $index + 1,
            ]);
        }

        return response()->json(['message' => 'Theme added successfully!']);
    }

    public function edit($id)
    {
        // Fetch the template
        $template = WebsiteTemplate::findOrFail($id);

        // Fetch the components related to the template
        $components = WebsiteTemplateComponent::where('template_id', $id)->get();

        return response()->json([
            'template' => $template,
            'components' => $components
        ]);
    }

    public function update(Request $request, $id)
    {
     
        $template = WebsiteTemplate::findOrFail($id);
    
        // Validate the request data
        $validated = $request->validate([
            'templateName' => 'required|string|max:255',
            'category' => 'required|array',
            'category.*' => 'exists:website_categories,name',
            'previewLink' => 'nullable|string|max:255',
            'status' => 'required|in:draft,testing,active,deactive',
            'component' => 'required|array',
            'component.*' => 'exists:components_crm,component_unique_id',
            'previewimage' => 'nullable|image',
        ]);

        $template->template_name = $validated['templateName'];
        $template->category_id = implode(',', $validated['category']);
        $template->status = $validated['status'];

        if ($request->hasFile('previewimage')) {
            $uploadedFile = $request->file('previewimage');
            $filename = time() . '_' . $uploadedFile->getClientOriginalName();
            $uploadedFile->storeAs('public/WebsiteTemplates', $filename);
            $template->featured_image = 'WebsiteTemplates/' . $filename;
        }
        $template->preview_link = $validated['previewLink'];
 
        $template->save();
        $newComponentIds = $validated['component'];
    
        // Remove components that are no longer associated with the template
        WebsiteTemplateComponent::where('template_id', $id)
            ->whereNotIn('component_unique_id', $newComponentIds)
            ->delete();
    
        // Add or update components
        foreach ($newComponentIds as $index => $componentId) {
            $component = WebsiteTemplateComponent::where('template_id', $id)
                ->where('component_unique_id', $componentId)
                ->first();
    
            if (!$component) {
                // If the component doesn't exist, create a new one
                WebsiteTemplateComponent::create([
                    'template_id' => $id,
                    'component_unique_id' => $componentId,
                    'position' => $index + 1,
                ]);
            } else {
                // Update the position of existing components
                $component->position = $index + 1;
                $component->save();
            }
        }
    
        return response()->json(['message' => 'Template and components updated successfully']);
    }
    

    public function destroy($id)
    {
        $template = WebsiteTemplate::find($id);
    
        if ($template) {
            $components = WebsiteTemplateComponent::where('template_id', $id)->get();
            if ($components->isNotEmpty()) {
                foreach ($components as $component) {
                    $component->delete();  
                }
            }
            $template->delete();
            return response()->json([
                'message' => 'Template deleted successfully.'
            ]);
        }
        return response()->json([
            'message' => 'Template not found.'
        ], 404);
    }
    
}

