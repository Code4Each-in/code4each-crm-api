@extends('layout')
@section('title', 'Component Area')
@section('subtitle', 'Component Area')
<style>
    span {
        display: inline-block;
        cursor: pointer;
        text-decoration: underline;
    }
    .controls {
        display: inline-block;
    }

    #canvas-container {
        width: 100%; /* Set the width of the container */
        height: 500px; /* Set the height of the container */
        border: 1px solid #ccc; /* Optional: Add border to container */
        position: relative; /* Required for absolute positioning of canvas */
    }
 
    canvas {
        position: absolute; /* Position canvas absolute inside container */
        top: 0;
        left: 0;
        width: 100%; /* Make canvas fill container width */
        height: 100%; /* Make canvas fill container height */
        border: 1px solid #000;
    }
</style>
@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">
<div class="col-lg-10 mx-auto">
    <div class="card">
        <div class="card-body">
            <div class="row mb-5 mt-4">
                <div class="font-weight-bold"><h4>Edit area and fields under <i class="text-danger fw-bold">{{$componentArea->component->component_name}}</i> Component</h4></div>
            </div>

            <form method="post" action="{{url('/componentareas/updateareafields/'.$componentId.'/'.$componentArea->id)}}" enctype="multipart/form-data">
                @csrf

                <!--############  CANVA DIV #################-->    
                <div class="row mb-5 mt-4">
                        <div id="canvas-container">
                            <canvas id="c"></canvas>
                        </div>
                        <input type="hidden" id="rectLeft" name="rectLeft" value="{{$componentArea->x_axis}}">
                        <input type="hidden" id="rectTop" name="rectTop" value="{{$componentArea->y_axis}}">
                        <input type="hidden" id="rectWidth" name="rectWidth" value="{{$componentArea->area_width}}">
                        <input type="hidden" id="rectHeight" name="rectHeight" value="{{$componentArea->area_height}}">

                </div>
                <!--############  CANVA DIV #################-->
                
                <div class="row mb-5 mt-4">
                    <label for="component_name" class="col-sm-3 col-form-label">Area Name</label>
                    <div class="col-sm-9">                   
                        <input type="text" class="form-control" placeholder="Field Name" name="area_name" value="{{$componentArea->area_name}}" required/>
                    </div>
                </div>
                <!--#form fields under this area-->

                <div class="row mb-5 mt-4">
                    <div class="col-md-3">
                        <label  class="required" >Area Form Fields:</label>
                    </div>
                </div>
                
                <div class="form-fields-container">
                    <div class="js-form-fields-option-old">
                        @foreach ( $componentAreaFields as $index => $fieldsData)
                        <div class="row mb-2 js-form-fields-option js-cloned-item">
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
                            <div class="row mb-2 js-form-fields-option js-cloned-item">
                                <input type="hidden" value="{{$index}}" class="js-rowIndex">
                                <input type="hidden" value="{{$subIndx}}" class="js-rowSubIndex">
                                <input type="hidden" name="edit_form-fields[{{$index}}][multiple_list][{{$subIndx}}][id]" value="{{$childFieldsData->id}}">

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
                <div class="text-center"> 

                    
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('componentareas.index', ['id' => $componentId]) }}" class="btn btn-secondary">Back</a>
                </div>
            </form>
            <!--## end of form fields under this area-->
        </div>
    </div>
</div>


<!--HTML for clonning-->
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
<script src="{{ asset('js/jquery-ui.min.js') }}"></script>
<script src="{{ asset('js/jquery.imagemaps.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/4.5.0/fabric.min.js"></script>

<!--############  CANVAS SCRIPT################--->
<script>
    var canvas = this.__canvas = new fabric.Canvas('c');
    var jsonObject = <?php echo $prefilledAreas; ?>;
    var currentAreaId = <?php echo $componentArea->id; ?>

    // Load the image onto the canvas
    fabric.Image.fromURL('{{ asset('/storage/' . $componentArea->component->preview) }}', function(img) {

        var container = document.getElementById('canvas-container');
            var containerWidth = container.offsetWidth;
            var imgWidth = img.width;
            var imgHeight = img.height;

            var scale = containerWidth / imgWidth;
            var minScale = 0.4; // Minimum scale factor to ensure the image is visible

            if (scale < minScale) {
                scale = minScale;
            }

            var newWidth = imgWidth * scale;
            var newHeight = imgHeight * scale;

            canvas.setDimensions({ width: newWidth, height: newHeight });
            
            img.set({
                left: 0, // Set the image position to the top-left corner of the canvas
                top: 0,
                selectable: false,
                scaleX: scale, // Set the scale based on the calculated factor
                scaleY: scale,
            });
            
            canvas.add(img);
            canvas.renderAll();

        //canvas.add(img);
        img.selectable = false; // Make the image unselectable
       // canvas.renderAll();

        $.each(jsonObject, function(index, element) {
            var areaId = element.id;
            //CASE I :- the current area to whom we are trying to edit
            if(currentAreaId == areaId){
                Add(element.x_axis, element.y_axis, element.area_width,element.area_height, 'rectLeft', 'rectTop', 'rectWidth', 'rectHeight','#ECF305','#B6F305',0,2);
            }else{
                //this is the case in which the area will not move
                Add(element.x_axis, element.y_axis, element.area_width,element.area_height, 'rect1Left', 'rect1Top', 'rect1Width', 'rect1Height','#7F8A8C','#06242A',1,2); // Position of the first rectangle
            }
        });
    });

    function Add(left, top, width, height, leftInputId, topInputId, widthInputId, heightInputId,fillColor,StrokeColor,isMovable,area_id) {
        
        var rect = new fabric.Rect({
            left: left,
            top: top,
            fill: fillColor, // filler color
            width: width,
            height: height,
            objectCaching: false,
            stroke: StrokeColor, // border color
            strokeWidth: 2,
            hasControls: true, // Enable controls (handles) for moving the rectangle
            hasBorders: false, // Disable borders for a cleaner look
            perPixelTargetFind: true, // Prevent deselection when clicking outside
            opacity: 0.5 // Set opacity to 50%
        });

        // Make the rectangle unselectable
        if(isMovable == 1){
            rect.selectable = false;
        }

       //console.log(left);
        

        canvas.add(rect);

        // Log coordinates while moving
        rect.on('moving', function() {
            console.log(leftInputId);
            document.getElementById(leftInputId).value = rect.left;
            document.getElementById(topInputId).value = rect.top;
            document.getElementById(widthInputId).value = rect.width;
             document.getElementById(heightInputId).value = rect.height;
        });
        
        rect.on('scaling', function() {
            var scaleX = rect.scaleX;
            var scaleY = rect.scaleY;
            var width = rect.width * scaleX;
            var height = rect.height * scaleY;

            // Update hidden input fields
            document.getElementById(leftInputId).value = rect.left;
            document.getElementById(topInputId).value = rect.top;
            document.getElementById(widthInputId).value = width;
            document.getElementById(heightInputId).value = height;
        });
    }

</script>
<!--############  CANVAS SCRIPT################--->
<script>
    //start document ready function
    $(document).ready(function() {

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

        function createClonedItem(firstItem ,formFieldIndex, isSubClone, subFieldIndex = false) {

            var clonedFormFieldItem = $('.js-hidden-form-fields-option .js-form-fields-option').clone().removeClass('d-none');
            if (firstItem) {
                    clonedFormFieldItem.addClass(firstItem);
                }else{
                    clonedFormFieldItem.addClass('js-cloned-item');
                }
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

        function cloneFormField(firstItem , target = false, subClone = false, innerSubClone = false) {
            if (subClone && target) {
                var subformFieldIndex = target.find('.js-rowSubIndex').val();

                if (subformFieldIndex === "false") {
                    subformFieldIndex = 1;
                } else {
                    // Increment subformFieldIndex by 1
                    subformFieldIndex++;
                    target.find('.js-rowSubIndex').val(subformFieldIndex);
                }

                var currentRowIndex = target.find('.js-rowIndex').val();

                var newDiv = '';

                newDiv = innerSubClone ? target : $('<div>').addClass('mx-4 my-2 border border-dark js-sub-cloned-item ');

                var titleText = $('<p>').addClass('h6 text-decoration-underline text-success').text('Sub Form Fields For  Multiple List:');
                innerSubClone ? '' : newDiv.append(titleText);

                var addButton = $('<span>').addClass('js-add-sub-form-fields clone text-success').css('font-size', '20px').text('+');
                innerSubClone ? '' : newDiv.append(addButton);
                newDiv.append(createClonedItem(firstItem, currentRowIndex, true, subformFieldIndex));


                innerSubClone ? '' : $('.form-fields-container').append(newDiv);
                target.after(newDiv);
            } else {
                if(firstItem){
                    var clonedFormFieldItem = createClonedItem(firstItem,formFieldIndex, false);
                     $('.form-fields-container').append(clonedFormFieldItem);
                }else{
                    var clonedFormFieldItem = createClonedItem(false,formFieldIndex, false);
                    $('.form-fields-container').append(clonedFormFieldItem);
                }
                formFieldIndex++;
            }

        }

        // cloneFormField();

        $('body').on('click', '.js-add-form-fields', function() {
            cloneFormField();
        });

        $('body').on('click', '.js-add-sub-form-fields', function() {
            const closestParent = $(this).closest('.js-sub-cloned-item');
            cloneFormField(false,closestParent, true, true);
        });

        $('body').on('click', '.js-remove-form-fields-cloned-item', function() {
            $(this).closest('.js-form-fields-option.js-cloned-item').remove()
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

