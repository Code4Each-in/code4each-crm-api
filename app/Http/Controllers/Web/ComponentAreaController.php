<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Component;
use App\Models\ComponentArea;
use App\Models\ComponentFormFields;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class ComponentAreaController extends Controller
{


    /**
     * LIST ALL THE AREAS UNDER COMPONENT
     */
    public function index($componentId){
        if($componentId){
            $componentAreas = ComponentArea::where('component_id', $componentId)->with('component')->get();
        
            //check if a component Area has fields or not
        $componentAreaHasFields = ComponentFormFields::where('component_id', $componentId)->get();
        
        return view('componentAreas.index',compact('componentAreas', 'componentId'));

        }
    }

    /**
     * ADD COMPONENT AREA HERE
     */
    public function create($componentId){
        $componentData = Component::find($componentId);
        $prefilledAreas = ComponentArea::where('component_id', $componentId)->get()->toArray();
       //dump($componentData);
        return view('componentAreas.create',['componentId'=>$componentId, 'componentData'=>$componentData, 'prefilledAreas'=>json_encode($prefilledAreas)]);
       // return view('componentAreas.create');
    }

 
    /**
     * SAVE A NEW AREA with Component FIELDS UNDER A COMPONENT 
     */
    public function saveareafields($componentId, Request $request){
      //  dump($request); dd('--');
        $validator = Validator::make($request->all(), [
            'area_name' => 'required|string|max:50',
            'form-fields' => 'required|array',
            'form-fields.*.name' => 'required',
            'form-fields.*.type' => 'required',
            //'form-fields.*.multiple_list.*.meta_key1' => 'nullable',
            //'form-fields.*.multiple_list.*.meta_key2' => 'nullable',
            'form-fields.*.default_value' => 'required',
            'form-fields.*.default_image.*' => 'nullable|file|max:5120|mimes:jpeg,png',
            'form-fields.*.multiple_image' => 'nullable',
            'form-fields.*.field_position' => 'required',
            'form-fields.*.meta_key1' => 'nullable',
            'form-fields.*.meta_key2' => 'nullable',
        ]);
    
        
        if ($validator->fails()) {
            return Redirect::back()->withInput()->withErrors($validator);
        }
        $validate = $validator->valid();

        $componentArea = new ComponentArea();
        $componentArea->component_id = $componentId;
        $componentArea->area_name = $request->input('area_name');
        $componentArea->x_axis = $request->input('rectLeft');
        $componentArea->y_axis = $request->input('rectTop');
        $componentArea->area_width = $request->input('rectWidth');
        $componentArea->area_height = $request->input('rectHeight');
        $componentArea->save();
        
        //get the last inserted area id from component_area table
        $componentAreaId = $componentArea->id;

        //WHEN COMPONENT BASIC INFORMATION GET SAVED IN  'component_crm' TABLE
        if ($componentId && $componentAreaId) 
        {
            //CHECK FOR COMPONENT FORM FIELDS AND THEN MOVE DATA INTO 'component_form_fields' TABLE
            foreach ($validate['form-fields'] as $formFieldData) 
            {
                //dump($formFieldData);
                
                    // Handle default_image or multiple images upload if it exists
                    if (isset($formFieldData['default_image'])) {
                        $uploadedFiles = $formFieldData['default_image'];

                        // Check if it's a single file or an array of files
                        if (is_array($uploadedFiles)) {
                            foreach ($uploadedFiles as $uploadedFile) {
                                // Handle the upload and update $formFieldData as needed for multiple images
                                $this->handleUpload($uploadedFile, $formFieldData);
                            }
                        } else {
                            // Handle the upload and update $formFieldData as needed for a single image
                            $this->handleUpload($uploadedFiles, $formFieldData);
                        }
                    }

                    // Create ComponentFormFields instance
                    $componentFormField = [
                        'component_id' => $componentId,
                        'area_id' => $componentAreaId,
                        'field_name' => $formFieldData['name'],
                        'field_type' => $formFieldData['type'],
                        'field_position' => $formFieldData['field_position'],
                        'default_value' => $formFieldData['default_value'],
                        'meta_key1' => $formFieldData['meta_key1'] ?? null,
                        'meta_key2' => $formFieldData['meta_key2'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    // check for image type multiple then add to the database
                    if (isset($formFieldData['multiple_image']) && $formFieldData['multiple_image'] == 'on') {
                        $componentFormField['is_multiple_image'] = true;
                    }

                    $saveComponentFields = ComponentFormFields::create($componentFormField);
            }
            $message = "Component Area and Fields Saved Successfully.";
           // dd('----Testing----');
            return redirect()->route('componentareas.index', ['id' => $componentId])->with('message', $message);
        }
    }

    /**
     * EDIT THE AREAS WITH FIELDS UNDER THAT AREA AND IT'S COMPONENT
     */
    public function edit($componentId, $componentAreaId){
        $componentArea = ComponentArea::with('component') ->where('id', $componentAreaId)->where('component_id', $componentId)->first();
        $prefilledAreas = ComponentArea::where('component_id', $componentId)->get()->toArray();
        $componentAreaFields = ComponentFormFields::where('component_id', $componentId)->where('area_id', $componentAreaId)->get();
        //dump($prefilledAreas); dd('---');
        
        return view('componentAreas.edit',['componentId'=>$componentId,'componentArea'=>$componentArea, 'componentAreaFields'=>$componentAreaFields, 'prefilledAreas'=>json_encode($prefilledAreas)]);
    }

    /**
     * UPDATE AREA WITH FIELDS
     */
    public function updateareafields($componentId, $componentAreaId,Request $request){
       
        $validator = Validator::make($request->all(), [
            'edit_form-fields' => 'required|array',
            'edit_form-fields.*.name' => 'required',
            'edit_form-fields.*.type' => 'required',
            'edit_form-fields.*.field_position' => 'required',
            'edit_form-fields.*.default_value' => 'required',
            'edit_form-fields.*.default_image.*' => 'nullable|file|max:5120|mimes:jpeg,png',
            'edit_form-fields.*.multiple_image' => 'nullable',
            'edit_form-fields.*.meta_key1' => 'nullable',
            'edit_form-fields.*.meta_key2' => 'nullable',
        ]);

        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator);
        }

        $validate = $validator->valid();

        $formFieldsData  = $validate['edit_form-fields'];
        // get detail of Component with id of Component
        $componentDetail = Component::with('formFields')->find($componentId);
        $formFieldsDetail = $componentDetail->formFields->toArray();

        $areaUpdate = ComponentArea::where('id', $componentAreaId)->update([ 'area_name' => $request->input('area_name'),
        'x_axis' => $request->input('rectLeft'),
        'y_axis' => $request->input('rectTop'),
        'area_width' => $request->input('rectWidth'),
        'area_height' => $request->input('rectHeight')
        ]);
        
        if($areaUpdate){
            
            //Create or Update the formFields
            foreach ($formFieldsData as $formFieldData) 
            {
                 
                    // Handle default_image or multiple images upload if it exists
                    if (isset($formFieldData['default_image'])) {
                        $uploadedFiles = $formFieldData['default_image'];

                        // Check if it's a single file or an array of files
                        if (is_array($uploadedFiles)) {
                            foreach ($uploadedFiles as $uploadedFile) {
                                // Handle the upload and update $formFieldData as needed for multiple images
                                $this->handleUpload($uploadedFile, $formFieldData);
                            }
                        } else {
                            // Handle the upload and update $formFieldData as needed for a single image
                            $this->handleUpload($uploadedFiles, $formFieldData);
                        }
                    }


                    if (isset($formFieldData['id'])) {
                        ComponentFormFields::where('id', $formFieldData['id'])
                            ->where('component_id', $componentId)
                            ->update([
                                'field_name' => $formFieldData['name'],
                                'field_type' => $formFieldData['type'],
                                'field_position' => $formFieldData['field_position'],
                                'default_value' => $formFieldData['default_value'],
                                'meta_key1' => $formFieldData['meta_key1'] ?? null,
                                'meta_key2' => $formFieldData['meta_key2'] ?? null,
                                'is_multiple_image' => isset($formFieldData['multiple_image']) && $formFieldData['multiple_image'] == 'on',
                                'updated_at' => now(),
                            ]);
                    } else 
                    {
                        $componentFormField = [
                            'component_id' => $componentId,
                            'area_id'=> $componentAreaId,
                            'field_name' => $formFieldData['name'],
                            'field_type' => $formFieldData['type'],
                            'field_position' => $formFieldData['field_position'],
                            'default_value' => $formFieldData['default_value'],
                            'meta_key1' => $formFieldData['meta_key1'] ?? null,
                            'meta_key2' => $formFieldData['meta_key2'] ?? null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];

                        // check for image type multiple then add to the database
                        if (isset($formFieldData['multiple_image']) && $formFieldData['multiple_image'] == 'on') {
                            $componentFormField['is_multiple_image'] = true;
                        }

                    $componentFormFields =  ComponentFormFields::create($componentFormField);
                    //$componentFormFieldId = $componentFormFields->id;

                }
            }

            //Delete Removed FormFields From fromFieldsArray
            $unmatchedFormFields = array_filter($formFieldsDetail, function($record) use ($formFieldsData) {
                return !in_array($record['id'], array_column($formFieldsData, 'id'));
            });
            
            if($unmatchedFormFields){
                foreach ($unmatchedFormFields as  $umFields) {
                    //dump($umFields['id']); dd('----');
                    ComponentFormFields::where('id',$umFields['id'])->delete();
                }
            }
            $message = "Component Area And Fields Saved Successfully.";
            return redirect()->route('componentareas.index', ['id' => $componentId])->with('message', $message);
        }
    }

    public function destroy($componentId, $componentAreaId,Request $request)
    {
        $areaDeleted = ComponentArea::where('id', $componentAreaId)->where('component_id', $componentId)->delete();
        if($areaDeleted){
            ComponentFormFields::where('area_id',$componentAreaId)->where('component_id', $componentId)->delete();
        }
        return redirect()->route('componentareas.index', ['id' => $componentId])->with('message', 'Area deleted successfully.');
    }

    /**
     * PRIVATE METHOD FOR IMAGE UPLOADING
     */
    private function handleUpload($uploadedFile, &$formFieldData)
    {
        if ($uploadedFile->isValid()) {
            $filename = time() . '_' . $uploadedFile->getClientOriginalName();
            $uploadedFile->storeAs('public/Components', $filename);
            $path = 'Components/' . $filename;

            // Check if multiple_image is 'on', update default_value accordingly
            if (isset($formFieldData['multiple_image']) && $formFieldData['multiple_image'] == 'on') {
                // Convert default_value to a string if it's not already
                $formFieldData['default_value'] = is_array($formFieldData['default_value'])
                    ? implode(',', $formFieldData['default_value'])
                    : $formFieldData['default_value'];

                // Append the new path to the comma-separated string
                $formFieldData['default_value'] .= ',' . $path;
            } else {
                // Store a single path
                $formFieldData['default_value'] = $path;
            }
        }
    } 

}

?>