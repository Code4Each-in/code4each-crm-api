@extends('layout')
@section('title', 'Components')
@section('subtitle', 'Components')
<style>
    .block {
        display: block;
    }

    input {
        width: 50%;
        display: inline-block;
    }

    span {
        display: inline-block;
        cursor: pointer;
        text-decoration: underline;
    }

    .d-none {
        display: none;
    }
</style>
@section('content')
<div class="col-lg-12 mx-auto">
    <div class="card">
        <div class="card-body">
            <form method="post" action="{{route('components.update',$componentData->id)}}" enctype="multipart/form-data">
                @csrf
                <div class="row mb-5 mt-4">
                    <label for="edit_component_name" class="col-sm-3 col-form-label required">Component Name</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="edit_component_name" id="edit_component_name" value="{{$componentData->component_name}}">
                        @if ($errors->has('edit_component_name'))
                        <span style="font-size: 12px;" class="text-danger">{{ $errors->first('edit_component_name') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row mb-5 mt-4">
                    <label for="edit_path" class="col-sm-3 col-form-label required">Path</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="edit_path" id="edit_path" value="{{$componentData->path}}">
                        @if ($errors->has('edit_path'))
                        <span style="font-size: 12px;" class="text-danger">{{ $errors->first('edit_path') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row mb-5 mt-4">
                    <label for="edit_type" class="col-sm-3 col-form-label required">Type</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="edit_type" id="edit_type" value="{{$componentData->type}}">
                        @if ($errors->has('edit_type'))
                        <span style="font-size: 12px;" class="text-danger">{{ $errors->first('edit_type') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row mb-5">
                    <label for="edit_category" class="col-sm-3 col-form-label required ">Category</label>
                    <div class="col-sm-9">
                        <select name="edit_category[]" class="form-select" id="edit_category" multiple size="3">
                            <option>Select Category</option>
                            @foreach ($category as $data)
                            <option value="{{ $data['name'] }}" {{ in_array($data['name'], explode(',', $componentData->category)) ? 'selected' : '' }}>
                                {{ $data['name'] }}
                            </option>
                            @endforeach
                        </select>
                        @if ($errors->has('edit_category'))
                        <span style="font-size: 12px;" class="text-danger">{{ $errors->first('edit_category') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row mb-5">
                    <label for="edit_preview" class="col-sm-3 col-form-label">Preview Image</label>
                    <div class="col-sm-9">
                        <input type="file" class="form-control" name="edit_preview" id="edit_preview">
                    </div>
                    @if ($errors->has('edit_preview.*'))
                    @foreach($errors->get('edit_preview.*') as $key => $errorMessages)
                    @foreach($errorMessages as $error)
                    <span style="font-size: 12px; padding: 10px 100px;" class="text-danger">
                        @if ($error == 'The document failed to upload.')
                        {{$error}} The document may not be greater than 5 mb.
                        @else
                        {{$error}}
                        @endif
                    </span>
                    @endforeach
                    @endforeach
                    @endif
                </div>
                <div class="row mb-5">
                    <label for="" class="col-sm-3 col-form-label">Uploaded Preview</label>
                    <div class="col-sm-9">
                        @if ($componentData->preview)
                        <img src="{{ asset('storage/' . $componentData->preview) }}" height="150" width="200" alt="Preview Image">
                        @else
                        NO Preview Image
                        @endif
                    </div>
                </div>

                <div class="row mb-5 mt-4">
                    <div class="col-md-3">
                        <label class="required">Dependency:</label>
                    </div>
                </div>
                <div class="dependencies-container">
                    <div class="js-dependency-option-old">
                        @foreach ( $componentData->dependencies as $index => $dependencies)
                        <div class="row mb-2 js-dependency-option">
                            <input type="hidden" class="form-control" name="edit_dependencies[{{$index}}][id]" value="{{$dependencies->id}}" />
                            <div class="col-md">
                                <input type="text" class="form-control" placeholder="Name" name="edit_dependencies[{{$index}}][name]" value="{{$dependencies->name}}" />
                            </div>
                            <div class="col-md">
                                <select class="form-control" name="edit_dependencies[{{$index}}][type]">
                                    <option selected>Select Type</option>
                                    <option value="js" {{$dependencies->type == 'js' ? 'selected' : ' ' }}>Javascript</option>
                                    <option value="css" {{$dependencies->type == 'css' ? 'selected' : ' ' }}>Css</option>
                                </select>
                            </div>
                            <div class="col-md">
                                <input type="text" class="form-control" placeholder="Path" name="edit_dependencies[{{$index}}][path]" value="{{$dependencies->path}}" />
                            </div>
                            <div class="col-md">
                                <input type="text" class="form-control" placeholder="Version" name="edit_dependencies[{{$index}}][version]" value="{{$dependencies->version}}" />
                            </div>
                            <div class="col-md-1">
                                <span class="js-remove-cloned-item text-danger" style="font-size: 20px;">&times;</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @if ($errors->has('edit_dependencies.*'))
                    @foreach($errors->get('edit_dependencies.*') as $key => $errorMessages)
                    <span style="font-size: 12px; padding-left:15px;" class="text-danger">
                        @foreach($errorMessages as $error)
                        @if ($error == 'The dependencies.0.name field is required.')
                        Name Field is required in Dependency.
                        @elseif ($error == 'The dependencies.0.path field is required.')
                        Path Field is required in Dependency.
                        @elseif ($error == 'The dependencies.0.version field is required.')
                        Versioin is required in Dependency.
                        @else
                        {{$error}}
                        @endif
                    </span>
                    @endforeach
                    @endforeach
                    @endif
                </div>
                <span class="js-add-dependency clone text-success" style="font-size: 20px;">+</span>

                <div class="row mb-5 mt-4">
                    <div class="col-md-3">
                        <label class="required">Form Fields:</label>
                    </div>
                </div>

                <div class="form-fields-container">
                    <div class="js-form-fields-option-old">
                        @foreach ( $componentData->formFields as $index => $fieldsData)
                        <div class="row mb-2 js-form-fields-option">
                            <input type="hidden" class="form-control" name="edit_form-fields[{{$index}}][id]" value="{{$fieldsData->id}}" />
                            <div class="col-md">
                                <input type="text" class="form-control" placeholder="Field Name" name="edit_form-fields[{{$index}}][name]" value="{{$fieldsData->field_name}}" />
                            </div>
                            <div class="col-md">
                                <select class="form-control selectFieldType" name="edit_form-fields[{{$index}}][type]" id="fieldType">
                                    <option selected>Select Field Type</option>
                                    <option value="text" {{$fieldsData->field_type == 'text' ? 'selected' : ' ' }}>Text</option>
                                    <option value="image" {{$fieldsData->field_type == 'image' ? 'selected' : ' ' }}>Image</option>
                                    <option value="textarea" {{$fieldsData->field_type == 'textarea' ? 'selected' : ' ' }}>TextArea</option>
                                    <option value="button" {{$fieldsData->field_type == 'button' ? 'selected' : ' ' }}>Button</option>
                                    <option value="multiple_list" {{$fieldsData->field_type == 'multiple_list' ? 'selected' : ' ' }}>Multiple List</option>
                                </select>
                            </div>
                            <div class="col-md">
                                <input type="text" class="form-control" placeholder="Field Position" name="edit_form-fields[{{$index}}][field_position]" value="{{$fieldsData->field_position}}" size="2" />
                            </div>



                            <div class="col-md" id="defaultField">
                                <input type="text" class="form-control formDefaultValue" placeholder="Default Value" name="edit_form-fields[{{$index}}][default_value]" @if ($fieldsData->field_type == 'image' && $fieldsData->default_value)
                                style="display:none"
                                @endif
                                value="{{$fieldsData->default_value}}" />
                                @if ($fieldsData->field_type == 'image' && $fieldsData->default_value)
                                @if ($fieldsData->is_multiple_image)
                                <input type="file" class="form-control imageUploadValue" name="edit_form-fields[{{$index}}][default_image][]" onchange="updateDefaultValue(this)" multiple />
                                <?php $imagePaths = explode(',', $fieldsData->default_value); ?>
                                <div class="d-flex flex-nowrap">
                                    @foreach($imagePaths as $imagePath)
                                    <div class="mx-2 my-1 border border-danger">
                                        <img src="{{ asset('storage/' . trim($imagePath)) }}" height="50" width="70" alt="Image Preview" />
                                    </div>
                                    @endforeach
                                </div>
                                @else
                                <input type="file" class="form-control imageUploadValue" name="edit_form-fields[{{$index}}][default_image]" onchange="updateDefaultValue(this)" />
                                <div class="mx-2 my-1 border border-danger">
                                    <img src="{{ asset('storage/' . trim($fieldsData->default_value)) }}" height="50" width="70" alt="Image Preview" />
                                </div>
                                @endif
                                @endif
                            </div>


                            <div class="col-md">
                                <input type="text" class="form-control" placeholder="Meta Key 1 (optional)" name="edit_form-fields[{{$index}}][meta_key1]" value="{{$fieldsData->meta_key1}}" />
                            </div>
                            <div class="col-md">
                                <input type="text" class="form-control" placeholder="Meta Key 2 (optional)" name="edit_form-fields[{{$index}}][meta_key2]" value="{{$fieldsData->meta_key2}}" />
                            </div>
                            <div class="col-md-1">
                                <span class="js-remove-form-fields-cloned-item text-danger" style="font-size: 20px;">&times;</span>
                            </div>
                        </div>
                        @if ($fieldsData && isset($fieldsData['children']) && count($fieldsData['children']) > 0)
                        <div class="mx-4 my-2 border border-dark js-sub-cloned-item">
                            <p class="h6 text-decoration-underline text-success my-2 mx-2"> Sub Form Fields For  Multiple List:</p>
                        <span class="js-add-sub-form-fields clone text-success" style="font-size: 20px;">+</span>
                            @foreach ($fieldsData['children'] as $subIndx => $childFieldsData )
                            <div class="row mb-2 js-form-fields-option">
                                <input type="hidden" value="7" class="js-rowIndex">
                                <input type="hidden" value="1" class="js-rowSubIndex">
                                <input type="hidden" name="edit_form-fields[{{$index}}][multiple_list][$subIndx][id]" value="{{$childFieldsData->id}}">

                                <div class="col-md">
                                    <input type="text" class="form-control" placeholder="Field Name" name="edit_form-fields[{{$index}}][multiple_list][{{$subIndx}}][name]" value="{{$childFieldsData->field_name}}">
                                </div>
                                <div class="col-md">
                                    <select class="form-control selectFieldType" name="edit_form-fields[{{$index}}][multiple_list][{{$subIndx}}][type]">
                                        <option selected="">Select Field Type</option>
                                        <option value="text" {{$childFieldsData->field_type == 'text' ? 'selected' : ' ' }}>Text</option>
                                        <option value="image" {{$childFieldsData->field_type == 'image' ? 'selected' : ' ' }}>Image</option>
                                        <option value="textarea" {{$childFieldsData->field_type == 'textarea' ? 'selected' : ' ' }}>TextArea</option>
                                        <option value="button" {{$childFieldsData->field_type == 'button' ? 'selected' : ' ' }}>Button</option>
                                        <option value="multiple_list" {{$childFieldsData->field_type == 'multiple_list' ? 'selected' : ' ' }}>Multiple List</option>
                                    </select>
                                </div>
                                <div class="col-md">
                                    <input type="text" class="form-control" placeholder="Field Position" name="edit_form-fields[{{$index}}][multiple_list][{{$subIndx}}][field_position]" value="{{$childFieldsData->field_position}}">
                                </div>
                                <div class="col-md">
                                    <input type="text" class="form-control formDefaultValue" placeholder="Default Value" name="edit_form-fields[{{$index}}][multiple_list][{{$subIndx}}][default_value]" value="{{$childFieldsData->default_value}}">
                                    <input type="file" class="form-control imageUploadValue imageFilePath" name="edit_form-fields[{{$index}}][multiple_list][{{$subIndx}}][default_image][]" style="display: none;" onchange="updateDefaultValue(this)">
                                    <label for="multipleImageUpload" class="js-multiple-image-upload imageUploadValue" style="display: none;">multiple</label>
                                    <input type="checkbox" id="multipleImageUpload" name="edit_form-fields[{{$index}}][multiple_list][{{$subIndx}}][multiple_image]" class="js-multiple-image-upload imageUploadValue" style="margin-top: 5px; display: none;">
                                </div>
                                <div class="col-md">
                                    <input type="text" class="form-control" placeholder="Meta Key 1 (optional)" name="edit_form-fields[{{$index}}][multiple_list][{{$subIndx}}][meta_key1]" value="{{$childFieldsData->meta_key1}}">
                                </div>
                                <div class="col-md">
                                    <input type="text" class="form-control" placeholder="Meta Key 2 (optional)" name="edit_form-fields[{{$index}}][multiple_list][{{$subIndx}}][meta_key2]" value="{{$childFieldsData->meta_key2}}">
                                </div>
                                <div class="col-md-1">
                                    <span class="js-remove-form-fields-cloned-item text-danger" style="font-size: 20px;">×</span>
                                </div>
                            </div>
                            @endforeach

                        </div>
                        <!-- Your HTML/Blade code for the case where $fieldsData has children -->
                        @else
                        {{-- $fieldsData is either null or has no children --}}
                        @endif
                        @endforeach
                    </div>

                    @if ($errors->has('edit_form-fields.*'))
                    @foreach($errors->get('edit_form-fields.*') as $key => $errorMessages)
                    <span style="font-size: 12px; padding: 10px 100px;" class="text-danger">
                        @foreach($errorMessages as $error)
                        @if ($error == 'The form-fields.0.name field is required.')
                        Name field is required in Form Field.
                        @elseif ($error == 'The form-fields.0.default_value field is required.')
                        default value is required in Form Feild.
                        @else
                        {{$error}}
                        @endif
                        @endforeach
                    </span>
                    @endforeach
                    @endif
                </div>
                <span class="js-add-form-fields clone text-success" style="font-size: 20px;">+</span>


                <div class="row mb-5">
                    <label for="edit_status" class="col-sm-3 col-form-label required ">Status</label>
                    <div class="col-sm-4">
                        <select name="edit_status" class="form-select" id="edit_status">
                            <option value="draft" {{$componentData->status == 'draft' ? 'selected' : ' ' }}>Draft</option>
                            <option value="testing" {{$componentData->status == 'testing' ? 'selected' : ' ' }}>Testing</option>
                            <option value="active" {{$componentData->status == 'active' ? 'selected' : ' ' }}>Active</option>
                            <option value="deactive" {{$componentData->status == 'deactive' ? 'selected' : ' ' }}>Deactive</option>
                        </select>
                        @if ($errors->has('edit_category'))
                        <span style="font-size: 12px;" class="text-danger">{{ $errors->first('edit_category') }}</span>
                        @endif
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="js-hidden-dependency-option d-none">
    <div class="row mb-2 js-dependency-option">
        <div class="col-md">
            <input type="text" class="form-control" placeholder="Name" name="edit_dependencies[][name]" />
        </div>
        <div class="col-md">
            <select class="form-control" name="edit_dependencies[][type]">
                <option selected>Select Type</option>
                <option value="js">Javascript</option>
                <option value="css">Css</option>
            </select>
        </div>
        <div class="col-md">
            <input type="text" class="form-control" placeholder="Path" name="edit_dependencies[][path]" />
        </div>
        <div class="col-md">
            <input type="text" class="form-control" placeholder="Version" name="edit_dependencies[][version]" />
        </div>
        <div class="col-md-1">
            <span class="js-remove-cloned-item text-danger" style="font-size: 20px;">&times;</span>
        </div>
    </div>
</div>

<div class="js-hidden-form-fields-option d-none">
    <div class="row mb-2 js-form-fields-option">
        <input type="hidden" value="" class="js-rowIndex">
        <input type="hidden" value="" class="js-rowSubIndex">

        <div class="col-md">
            <input type="text" class="form-control" placeholder="Field Name" name="edit_form-fields[][name]" />
        </div>
        <div class="col-md">
            <select class="form-control selectFieldType" name="edit_form-fields[][type]">
                <option selected>Select Field Type</option>
                <option value="text">Text</option>
                <option value="image">Image</option>
                <option value="textarea">TextArea</option>
                <option value="button">Button</option>
                <option value="multiple_list">Multiple List</option>
            </select>
        </div>
        <div class="col-md">
            <input type="text" class="form-control" placeholder="Field Position" name="edit_form-fields[][field_position]" />
        </div>
        <div class="col-md">
            <input type="text" class="form-control formDefaultValue" placeholder="Default Value" name="edit_form-fields[][default_value]" />
            <input type="file" class="form-control imageUploadValue imageFilePath" name="edit_form-fields[][default_image][]" style="display: none;" onchange="updateDefaultValue(this)" />
            <label for="multipleImageUpload" class="js-multiple-image-upload imageUploadValue" style="display: none;">multiple</label>
            <input type="checkbox" id="multipleImageUpload" name="edit_form-fields[][multiple_image]" class="js-multiple-image-upload imageUploadValue" style="margin-top: 5px; display: none;">
        </div>
        <div class="col-md">
            <input type="text" class="form-control" placeholder="Meta Key 1 (optional)" name="edit_form-fields[][meta_key1]" />
        </div>
        <div class="col-md">
            <input type="text" class="form-control" placeholder="Meta Key 2 (optional)" name="edit_form-fields[][meta_key2]" />
        </div>

        <div class="col-md-1">
            <span class="js-remove-form-fields-cloned-item text-danger" style="font-size: 20px;">&times;</span>
        </div>
    </div>
</div>
@endsection
@section('js_scripts')
<script>
    //start document ready function
    $(document).ready(function() {

        var dependencyLastChild = $('.js-dependency-option:last-child');
        let dependencyIndex = dependencyLastChild.find('input[name^="edit_dependencies"]').attr('name').match(/\[(.*?)\]/)[1];
        if (!dependencyIndex) {
            dependencyIndex = 0;
        } else {
            dependencyIndex = parseInt(dependencyIndex) + 1;
        }


        // Function to clone a dependency row
        function cloneDependency() {
            var clonedDependencyItem = $('.js-hidden-dependency-option .js-dependency-option').clone().removeClass('d-none').addClass('js-cloned-item');
            clonedDependencyItem.find('[name="edit_dependencies[][name]"]').attr('name', 'edit_dependencies[' + dependencyIndex + '][name]');
            clonedDependencyItem.find('[name="edit_dependencies[][type]"]').attr('name', 'edit_dependencies[' + dependencyIndex + '][type]');
            clonedDependencyItem.find('[name="edit_dependencies[][path]"]').attr('name', 'edit_dependencies[' + dependencyIndex + '][path]');
            clonedDependencyItem.find('[name="edit_dependencies[][version]"]').attr('name', 'edit_dependencies[' + dependencyIndex + '][version]');

            $('.dependencies-container').append(clonedDependencyItem);
            dependencyIndex++;
        }

        // cloneDependency('first-cloned-item');

        $('.js-add-dependency').click(function() {
            cloneDependency();
        });

        $('body').on('click', '.js-remove-cloned-item', function() {
            $(this).closest('.js-dependency-option').remove()
        });

        var lastChild = $('.js-form-fields-option:last-child');
        let formFieldIndex = lastChild.find('input[name^="edit_form-fields"]').attr('name').match(/\[(.*?)\]/)[1];

        if (!formFieldIndex) {
            formFieldIndex = 0;
        } else {
            formFieldIndex = parseInt(formFieldIndex) + 1;
        }

        function createClonedItem(formFieldIndex, isSubClone, subFieldIndex = false) {
            // console.log('SUb Field Index', subFieldIndex);
            //     console.log('form Field ', formFieldIndex - 1);


            var clonedFormFieldItem = $('.js-hidden-form-fields-option .js-form-fields-option').clone().removeClass('d-none');

            var namePrefix = isSubClone ? 'edit_form-fields[' + formFieldIndex + '][multiple_list][' + subFieldIndex + '][' : 'edit_form-fields[' + formFieldIndex + '][';
            clonedFormFieldItem.find('.js-rowIndex').val(formFieldIndex);
            clonedFormFieldItem.find('.js-rowSubIndex').val(subFieldIndex);
            clonedFormFieldItem.find('[name="edit_form-fields[][name]"]').attr('name', namePrefix + 'name]');
            clonedFormFieldItem.find('[name="edit_form-fields[][type]"]').attr('name', namePrefix + 'type]');
            clonedFormFieldItem.find('[name="edit_form-fields[][field_position]"]').attr('name', namePrefix + 'field_position]');
            clonedFormFieldItem.find('[name="edit_form-fields[][default_value]"]').attr('name', namePrefix + 'default_value]');
            clonedFormFieldItem.find('[name="edit_form-fields[][default_image][]"]').attr('name', namePrefix + 'default_image][]');
            clonedFormFieldItem.find('[name="edit_form-fields[][multiple_image]"]').attr('name', namePrefix + 'multiple_image]');
            clonedFormFieldItem.find('[name="edit_form-fields[][meta_key1]"]').attr('name', namePrefix + 'meta_key1]');
            clonedFormFieldItem.find('[name="edit_form-fields[][meta_key2]"]').attr('name', namePrefix + 'meta_key2]');

            return clonedFormFieldItem;
        }

        function cloneFormField(target = false, subClone = false, innerSubClone = false) {

            // console.log("fomindex", formFieldIndex);
            if (subClone && target) {

                var subformFieldIndex = target.find('.js-rowSubIndex').val();
                console.log("start", subformFieldIndex);

                if (subformFieldIndex === "false") {
                    console.log("in", subformFieldIndex);
                    subformFieldIndex = 1;
                } else {
                    // Increment subformFieldIndex by 1
                    subformFieldIndex++;
                    target.find('.js-rowSubIndex').val(subformFieldIndex);
                }

                console.log("out", subformFieldIndex);

                var currentRowIndex = target.find('.js-rowIndex').val();
                console.log(currentRowIndex);

                var newDiv = '';
                console.log('target', target);
                console.log('subclone', subClone);
                console.log('innseer', innerSubClone);

                newDiv = innerSubClone ? target : $('<div>').addClass('mx-4 my-2 border border-dark js-sub-cloned-item ');

                var titleText = $('<p>').addClass('h6 text-decoration-underline text-success').text('Sub Form Fields For  Multiple List:');
                innerSubClone ? '' : newDiv.append(titleText);

                var addButton = $('<span>').addClass('js-add-sub-form-fields clone text-success').css('font-size', '20px').text('+');
                innerSubClone ? '' : newDiv.append(addButton);
                newDiv.append(createClonedItem(currentRowIndex, true, subformFieldIndex));


                innerSubClone ? '' : $('.form-fields-container').append(newDiv);
                target.after(newDiv);
                // console.log("subclone");
            } else {
                // console.log('form Field ', formFieldIndex);
                // console.log("else");
                var clonedFormFieldItem = createClonedItem(formFieldIndex, false);
                $('.form-fields-container').append(clonedFormFieldItem);
                formFieldIndex++;
            }

        }

        // cloneFormField();

        $('body').on('click', '.js-add-form-fields', function() {
            // console.log("pressed");
            cloneFormField();
        });

        $('body').on('click', '.js-add-sub-form-fields', function() {
            const closestParent = $(this).closest('.js-sub-cloned-item');
            cloneFormField(closestParent, true, true);
        });

        $('body').on('click', '.js-remove-form-fields-cloned-item', function() {
            $(this).closest('.js-form-fields-option').remove()
        });

        const selectedValue = $('.selectFieldType').val();


        $(document).on('change', '.selectFieldType', function() {
            const selectedValue = $(this).val();
            const closestParent = $(this).closest('.js-form-fields-option');
            const defaultValueInput = closestParent.find('.formDefaultValue');
            const imageUpload = closestParent.find('.imageUploadValue');
            const formFieldsContainer = closestParent.find('.form-fields-container');

            if (selectedValue === 'image') {
                defaultValueInput.hide();
                imageUpload.show();
            } else {
                defaultValueInput.show();
                imageUpload.hide();
            }

            if (selectedValue === 'multiple_list') {
                console.log("in multiple list");
                cloneFormField(closestParent, true);
            }
        });


        $(document).on('change', '#multipleImageUpload', function() {
            const isChecked = $(this).prop('checked');
            const imageUpload = $(this).closest('.js-form-fields-option').find('.imageFilePath');

            if (isChecked) {
                // If checkbox is checked, show multiple file input
                imageUpload.attr('multiple', 'multiple');
            } else {
                // If checkbox is unchecked, hide multiple file input
                imageUpload.removeAttr('multiple');
            }
        });


    });
    //end document ready function

    function updateDefaultValue(input) {
        const closestParent = $(input).closest('.js-form-fields-option');
        const defaultValueInput = closestParent.find('.formDefaultValue');
        // Update the default value input with the file name (temporary name for now)
        defaultValueInput.val(input.files[0].name);
    }
</script>
@endsection
