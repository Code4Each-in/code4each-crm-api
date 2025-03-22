@extends('layout')
@section('title', 'Themes')
@section('subtitle', 'Themes')
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
            <form method="post" action="{{route('themes.update',$themesData->id)}}" enctype="multipart/form-data">
                @csrf
                <div class="row mb-5 mt-4">
                    <label for="edit_name" class="col-sm-3 col-form-label required">Name</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="edit_name" id="edit_name" value="{{$themesData->name}}">
                        @if ($errors->has('edit_name'))
                        <span style="font-size: 12px;" class="text-danger">{{ $errors->first('edit_name') }}</span>
                        @endif
                    </div>
                </div>
              
                <div class="row mb-5">
                    <label for="edit_primary_font" class="col-sm-3 col-form-label required ">Primary Font</label>
                    <div class="col-sm-9">
                        <select name="edit_primary_font" class="form-select" id="edit_primary_font" multiple size="3">
                            <option>Select Category</option>
                            @foreach ($fonts as $data)
                            <option value="{{ $data['id'] }}" {{ in_array($data['id'], explode(',', $themesData->primary_font)) ? 'selected' : '' }}>
                                {{ $data['name'] }}
                            </option>
                            @endforeach
                        </select>
                        @if ($errors->has('edit_primary_font'))
                        <span style="font-size: 12px;" class="text-danger">{{ $errors->first('edit_primary_font') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row mb-5">
                    <label for="edit_secondary_font" class="col-sm-3 col-form-label">Secondary Font</label>
                    <div class="col-sm-9">
                        <select name="edit_secondary_font" class="form-select" id="edit_secondary_font" multiple size="3">
                            <option>Select Category</option>
                            @foreach ($fonts as $data)
                            <option value="{{ $data['id'] }}" {{ in_array($data['id'], explode(',', $themesData->secondary_font)) ? 'selected' : '' }}>
                                {{ $data['name'] }}
                            </option>
                            @endforeach
                        </select>
                        @if ($errors->has('edit_secondary_font'))
                        <span style="font-size: 12px;" class="text-danger">{{ $errors->first('edit_secondary_font') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row mb-5">
                    <label for="edit_tertiary_font" class="col-sm-3 col-form-label ">Tertiary Font</label>
                    <div class="col-sm-9">
                        <select name="edit_tertiary_font" class="form-select" id="edit_tertiary_font" multiple size="3">
                            <option>Select Category</option>
                            @foreach ($fonts as $data)
                            <option value="{{ $data['id'] }}" {{ in_array($data['id'], explode(',', $themesData->tertiary_font)) ? 'selected' : '' }}>
                                {{ $data['name'] }}
                            </option>
                            @endforeach
                        </select>
                        @if ($errors->has('edit_tertiary_font'))
                        <span style="font-size: 12px;" class="text-danger">{{ $errors->first('edit_tertiary_font') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row mb-5">
                    <label for="edit_category" class="col-sm-3 col-form-label required ">Category</label>
                    <div class="col-sm-9">
                        <select name="edit_category[]" class="form-select" id="edit_category" multiple size="3">
                            <option>Select Category</option>
                            @foreach ($category as $data)
                            <option value="{{ $data['name'] }}" {{ in_array($data['name'], explode(',', $themesData->category)) ? 'selected' : '' }}>
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
                    <label for="edit_preview_image_image" class="col-sm-3 col-form-label">Preview Image</label>
                    <div class="col-sm-9">
                        <input type="file" class="form-control" name="edit_preview_image" id="edit_preview_image">
                    </div>
                    @if ($errors->has('edit_preview_image.*'))
                    @foreach($errors->get('edit_preview_image.*') as $key => $errorMessages)
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
                        @if ($themesData->preview_image)
                        <img src="{{ asset('storage/' . $themesData->preview_image) }}" height="150" width="200" alt="Preview Image">
                        @else
                        NO Preview Image
                        @endif
                    </div>
                </div>

                <div class="row mb-5">
                    <label for="edit_default_color" class="col-sm-3 col-form-label required ">Default Color</label>
                    <div class="col-sm-9">
                        <select name="edit_default_color" class="form-select" id="edit_default_color" multiple size="3">
                            <option>Select Default Color</option>
                            @foreach ($colors as $data)
                            <option value="{{ $data['id'] }}" {{ in_array($data['id'], explode(',', $themesData->default_color)) ? 'selected' : '' }}>
                                {{ $data['title'] }}
                            </option>
                            @endforeach
                        </select>
                        @if ($errors->has('edit_default_color'))
                        <span style="font-size: 12px;" class="text-danger">{{ $errors->first('edit_default_color') }}</span>
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
                        @foreach ( $themesData->dependencies as $index => $dependencies)
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
                    <label for="edit_demo_url" class="col-sm-3 col-form-label required">Demo Url</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="edit_demo_url" id="edit_demo_url" value="{{$themesData->demo_url}}">
                        @if ($errors->has('edit_demo_url'))
                        <span style="font-size: 12px;" class="text-danger">{{ $errors->first('edit_demo_url') }}</span>
                        @endif
                    </div>
                </div>



                <div class="row mb-5">
                    <label for="edit_status" class="col-sm-3 col-form-label required ">Status</label>
                    <div class="col-sm-4">
                        <select name="edit_status" class="form-select" id="edit_status">
                            <option value="draft" {{$themesData->status == 'draft' ? 'selected' : ' ' }}>Draft</option>
                            <option value="testing" {{$themesData->status == 'testing' ? 'selected' : ' ' }}>Testing</option>
                            <option value="active" {{$themesData->status == 'active' ? 'selected' : ' ' }}>Active</option>
                            <option value="deactive" {{$themesData->status == 'deactive' ? 'selected' : ' ' }}>Deactive</option>
                        </select>
                        @if ($errors->has('edit_category'))
                        <span style="font-size: 12px;" class="text-danger">{{ $errors->first('edit_category') }}</span>
                        @endif
                    </div>
                </div>


                <div class="row mb-5">
                    <label for="edit_accessibility" class="col-sm-3 col-form-label required ">Accessibility</label>
                    <div class="col-sm-4">
                        <select name="edit_accessibility" class="form-select" id="edit_accessibility">
                            <option value="free" {{$themesData->accessibility == 'free' ? 'selected' : ' ' }}>Free</option>
                            <option value="paid" {{$themesData->accessibility == 'paid' ? 'selected' : ' ' }}>Paid</option>
                          
                        </select>
                        @if ($errors->has('edit_accessibility'))
                        <span style="font-size: 12px;" class="text-danger">{{ $errors->first('edit_accessibility') }}</span>
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
                cloneFormField('first-cloned-item',closestParent, true);
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
