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
            <form method="post" action="{{route('themes.store')}}" enctype="multipart/form-data">
                @csrf
                <div class="row mb-5 mt-4">
                    <label for="name" class="col-sm-3 col-form-label required">Name</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="name" id="name" value="{{ old('name') }}">
                        @if ($errors->has('name'))
                        <span style="font-size: 12px;" class="text-danger">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row mb-5">
                    <label for="primary_font" class="col-sm-3 col-form-label required ">Primary Fonts</label>
                    <div class="col-sm-9">
                        <select name="primary_font" class="form-select" id="primary_font" multiple size="3">
                            <option selected>Select Primary Font</option>
                            @foreach ($fonts as $data)
                            <option value="{{$data['id']}}">
                                {{$data['name']}}
                            </option>
                            @endforeach
                        </select>
                        @if ($errors->has('primary_font'))
                             <span style="font-size: 12px;" class="text-danger">{{ $errors->first('primary_font') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row mb-5">
                    <label for="secondary_font" class="col-sm-3 col-form-label ">Secondary Fonts</label>
                    <div class="col-sm-9">
                        <select name="secondary_font" class="form-select" id="secondary_font" multiple size="3">
                            <option selected>Select Primary Font</option>
                            @foreach ($fonts as $data)
                            <option value="{{$data['id']}}">
                                {{$data['name']}}
                            </option>
                            @endforeach
                        </select>
                        @if ($errors->has('primary_font'))
                             <span style="font-size: 12px;" class="text-danger">{{ $errors->first('primary_font') }}</span>
                        @endif
                    </div>
                </div>


                <div class="row mb-5">
                    <label for="tertiary_font" class="col-sm-3 col-form-label">Tertiary Fonts</label>
                    <div class="col-sm-9">
                        <select name="tertiary_font" class="form-select" id="tertiary_font" multiple size="3">
                            <option selected>Select Primary Font</option>
                            @foreach ($fonts as $data)
                            <option value="{{$data['id']}}">
                                {{$data['name']}}
                            </option>
                            @endforeach
                        </select>
                        @if ($errors->has('tertiary_font'))
                             <span style="font-size: 12px;" class="text-danger">{{ $errors->first('tertiary_font') }}</span>
                        @endif
                    </div>
                </div>


                <div class="row mb-5">
                    <label for="category" class="col-sm-3 col-form-label required ">Category</label>
                    <div class="col-sm-9">
                        <select name="category[]" class="form-select" id="category" multiple size="3">
                            <option>Select Category</option>
                            @foreach ($category as $data)
                            <option value="{{$data['name']}}">
                                {{$data['name']}}
                            </option>
                            @endforeach
                        </select>
                        @if ($errors->has('category'))
                             <span style="font-size: 12px;" class="text-danger">{{ $errors->first('category') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row mb-5">
                    <label for="preview_image" class="col-sm-3 col-form-label required">Preview Image</label>
                    <div class="col-sm-9">
                        <input type="file" class="form-control" name="preview_image" id="preview_image" value="{{ old('preview_image') }}">
                        @if ($errors->has('preview_image'))
                             <span style="font-size: 12px; " class="text-danger">{{ $errors->first('preview_image') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row mb-5">
                    <label for="default_color" class="col-sm-3 col-form-label required ">Default Color</label>
                    <div class="col-sm-9">
                        <select name="default_color" class="form-select" id="default_color" multiple size="3">
                            <option selected>Select Default Color</option>
                            @foreach ($colors as $data)
                            <option value="{{$data['id']}}">
                                {{$data['title']}}
                            </option>
                            @endforeach
                        </select>
                        @if ($errors->has('default_color'))
                             <span style="font-size: 12px;" class="text-danger">{{ $errors->first('default_color') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row mb-5 mt-4">
                    <div class="col-md-3">
                        <label class="required">Dependency:</label>
                    </div>
                </div>
                <div class="dependencies-container">
                @if ($errors->has('dependencies.*'))
                    @foreach($errors->get('dependencies.*') as $key => $errorMessages)
                    <span style="font-size: 12px; padding-left:15px;" class="text-danger">
                    @foreach($errorMessages as $error)
                        @if ($error == 'The dependencies.0.name field is required.')
                            Name Field is required in Dependency.
                        @elseif ($error == 'The dependencies.0.path field is required.')
                                Path Field is required in Dependency.
                        @elseif ($error == 'The dependencies.0.version field is required.')
                                    Versioin is required in  Dependency.
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
                    <label for="demo_url" class="col-sm-3 col-form-label required">Demo Url</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="demo_url" id="demo_url" value="{{ old('demo_url') }}">
                        @if ($errors->has('demo_url'))
                        <span style="font-size: 12px;" class="text-danger">{{ $errors->first('demo_url') }}</span>
                        @endif
                    </div>
                </div>

                <!-- status for the Component -->
                <div class="row mb-5">
                    <label for="status" class="col-sm-3 col-form-label required ">Status</label>
                    <div class="col-sm-4">
                        <select name="status" class="form-select" id="status">
                            <option value="draft">Draft</option>
                            <option value="testing">Testing</option>
                            <option value="active">Active</option>
                            <option value="deactive">Deactive</option>
                        </select>
                        @if ($errors->has('status'))
                             <span style="font-size: 12px;" class="text-danger">{{ $errors->first('status') }}</span>
                        @endif
                    </div>
                </div>

                 <!-- Accessibility for the Theme -->
                 <div class="row mb-5">
                    <label for="accessibility" class="col-sm-3 col-form-label required ">Accessibility</label>
                    <div class="col-sm-4">
                        <select name="accessibility" class="form-select" id="accessibility">
                            <option value="free">Free</option>
                            <option value="paid">Paid</option>
                        </select>
                        @if ($errors->has('accessibility'))
                             <span style="font-size: 12px;" class="text-danger">{{ $errors->first('accessibility') }}</span>
                        @endif
                    </div>
                </div>
                 <!-- Ends Accessibility for the Theme -->


                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- code for clone new row for dependency -->
<div class="js-hidden-dependency-option d-none">
    <div class="row mb-2 js-dependency-option">
        <div class="col-md">
            <input type="text" class="form-control" placeholder="Name" name="dependencies[][name]" value="{{ old('dependencies[][name]') }}"/>
        </div>
        <div class="col-md">
            <select class="form-control" name="dependencies[][type]">
                <option selected>Select Type</option>
                <option value="js">Javascript</option>
                <option value="css">Css</option>
            </select>
        </div>
        <div class="col-md">
            <input type="text" class="form-control" placeholder="Path" name="dependencies[][path]" value="{{ old('dependencies[][path]') }}"/>
        </div>
        <div class="col-md">
            <input type="text" class="form-control" placeholder="Version" name="dependencies[][version]" value="{{ old('dependencies[][version]') }}"/>
        </div>
        <div class="col-md-1">
            <span class="js-remove-cloned-item text-danger" style="font-size: 20px;">&times;</span>
        </div>
    </div>
</div>


@endsection
@section('js_scripts')
<script>
        $(document).ready(function () {
            let dependencyIndex = 0;

            // Function to clone a dependency row
            function cloneDependency(firstItem = false) {
                var clonedDependencyItem = $('.js-hidden-dependency-option .js-dependency-option').clone().removeClass('d-none').addClass('js-cloned-item');
                if (firstItem) {
                    var clonedDependencyItem = $('.js-hidden-dependency-option .js-dependency-option').clone().removeClass('d-none').addClass(firstItem);
                }

                clonedDependencyItem.find('[name="dependencies[][name]"]').attr('name', 'dependencies[' + dependencyIndex + '][name]');
                clonedDependencyItem.find('[name="dependencies[][type]"]').attr('name', 'dependencies[' + dependencyIndex + '][type]');
                clonedDependencyItem.find('[name="dependencies[][path]"]').attr('name', 'dependencies[' + dependencyIndex + '][path]');
                clonedDependencyItem.find('[name="dependencies[][version]"]').attr('name', 'dependencies[' + dependencyIndex + '][version]');

                $('.dependencies-container').append(clonedDependencyItem);
                dependencyIndex++;
            }

            cloneDependency('first-cloned-item');

            $('.js-add-dependency').click(function () {
                cloneDependency();
            });

            $('body').on('click', '.js-remove-cloned-item', function () {
                $(this).closest('.js-dependency-option.js-cloned-item').remove();
            });

          

            // On Change Events Starts
            $(document).on('change', '.selectFieldType', function () {
                const selectedValue = $(this).val();
                const closestParent = $(this).closest('.js-form-fields-option');
                const defaultValueInput = closestParent.find('.formDefaultValue');
                const imageUpload = closestParent.find('.imageUploadValue');
                const defaultValueContainer = closestParent.find('.defaultValueContainer');

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

            $(document).on('change', '#multipleImageUpload', function () {
                console.log("change function",formFieldIndex);
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
            // On Change Events Starts

            //Need to create clone with multiple list
            $(this).on('click', '.again-clone-sub-form', function () {
                //var clone = $('.js-sub-cloned-item').clone(false);
                // alert()
                //$('.form-fields-container').append(clone);

               // const closestParent = $(this).closest('.js-sub-cloned-item');
                //console.log(closestParent);
               // cloneFormField(false ,closestParent, true, true);
            //    var countMultiList = $('.border-dark').children('.mb-2').length; alert(countMultiList);
               for (var i = 1; i < countMultiList; i +=1){
                    createClonedItem(false,false, false);
               }
               
               
               
            });

            //CLICKED AND DELETE MULTIPLE CLONNED ITEMS
            $('body').on('click', '.delete-clone-sub-form', function () {
                $(this).closest('.js-sub-cloned-item').remove();
            });


        });

        function updateDefaultValue(input) {
            var file = input.files[0];
            var fileName = file.name;
            const closestParent = $(input).closest('.defaultValueContainer');
            const insertDefaultValue = closestParent.find('.formDefaultValue').val(fileName);
        }

</script>
@endsection
