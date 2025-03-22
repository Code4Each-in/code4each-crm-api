@extends('layout')
@section('title', 'Component Area')
@section('subtitle', 'Component Area')
<style>
    .imagemaps-wrapper{
        position:relative;
    }
    .imagemaps-wrapper img {
        max-width:100%        
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
<div class="col-lg-10 mx-auto">
    <div class="card">
        <div class="card-body">
            <div class="row mb-5 mt-4">
                <div class="font-weight-bold"><h4>Add area and fields under <i class="text-danger fw-bold">{{$componentData->component_name}}</i> Component</h4></div>
            </div>
            
            <form method="post" action="{{url('/componentareas/saveareafields/'.$componentId)}}" enctype="multipart/form-data">
                @csrf

            <!--############  CANVA DIV #################-->    
            <div class="row mb-5 mt-4">
                    <div id="canvas-container">
                        <canvas id="c"></canvas>
                    </div>
                    <input type="hidden" id="rectLeft" name="rectLeft" value="10">
                    <input type="hidden" id="rectTop" name="rectTop" value="10">
                    <input type="hidden" id="rectWidth" name="rectWidth" value="150">
                    <input type="hidden" id="rectHeight" name="rectHeight" value="100">
            </div>
            <!--############  CANVA DIV #################-->
            
            <div class="row mb-5 mt-4">
                <label for="component_name" class="col-sm-3 col-form-label required">Area Name</label>
                <div class="col-sm-9">
                <input type="text" name = "area_name" class="form-control" required>
                </div>
            </div>

            <!--add fields under this area-->                
                <div class="row mb-5 mt-4">
                    <div class="col-md-3">
                        <label  class="required" >Area Form Fields:</label>
                    </div>
                </div>

                <div class="form-fields-container" id="formWithFields">              
                    @if ($errors->has('form-fields.*'))
                        @foreach($errors->get('form-fields.*') as $key => $errorMessages)
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
                <!-- add button for new form field in component -->
                <span class="js-add-form-fields clone text-success" style="font-size: 20px;">+</span>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <a href="{{ route('componentareas.index', ['id' => $componentId]) }}" class="btn btn-secondary">Back</a>
                </div>
            </form>
            <!--##add fields-->
        </div>
    </div>
</div>

<!-- code for clone new row for form fields -->

<div class="js-hidden-form-fields-option d-none">
    <div class="overflow-auto">
        <div class="row mb-2 js-form-fields-option">
        <input type="hidden" value="" class="js-rowIndex">
        <input type="hidden" value="" class="js-rowSubIndex">
            <!-- Your existing fields -->
            <div class="col-md">
                <input type="text" class="form-control" placeholder="Field Name" name="form-fields[][name]" value="{{ old('dependencies[][name]') }}" required/>
            </div>
            <div class="col-md">
                <select class="form-control selectFieldType" name="form-fields[][type]" id="fieldType" value="{{ old('dependencies[][type]') }}" required>
                    <option selected>Select Field Type</option>
                    <option value="text">Text</option>
                    <option value="image">Image</option>
                    <option value="textarea">TextArea</option>
                    <option value="button">Button</option>
                   <!-- <option value="multiple_list">Multiple List</option>-->
                </select>
            </div>
            <div class="col-md">
                <input type="text" class="form-control" placeholder="Field Position" name="form-fields[][field_position]" value="{{ old('field_position]') }}" required/>
            </div>
            <div class="col-md-3 defaultValueContainer">
                <input type="text" class="form-control formDefaultValue" placeholder="Default Value" name="form-fields[][default_value]" value="" required/>
                <input type="file" class="form-control imageUploadValue imageFilePath" name="form-fields[][default_image][]" style="display: none;" onchange="updateDefaultValue(this)" />
                <label for="multipleImageUpload" class="js-multiple-image-upload imageUploadValue" style="display: none;">multiple</label>
                <input type="checkbox" id="multipleImageUpload" name="form-fields[][multiple_image]" class="js-multiple-image-upload imageUploadValue" style="margin-top: 5px; display: none;">
            </div>
            <div class="col-md">
                <input type="text" class="form-control" placeholder="Meta Key1 (Optional)" name="form-fields[][meta_key1]" value="{{ old('meta_key1') }}"/>
            </div>
            <div class="col-md">
                <input type="text" class="form-control" placeholder="Meta Key2 (Optional)" name="form-fields[][meta_key2]" value="{{ old('meta_key2') }}"/>
            </div>
            <div class="col-md-1">
                <span class="js-remove-form-fields-cloned-item text-danger" style="font-size: 20px;">&times;</span>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js_scripts')
<script src="{{ asset('js/fabric.min.js') }}"></script>
<script>
/**
 *###################################### Script for canvas  #############################
 */
var jsonObject = <?php echo $prefilledAreas; ?>;
var canvas = this.__canvas = new fabric.Canvas('c');

fabric.Image.fromURL('{{ asset('/storage/' . $componentData->preview) }}', function(img) {
        // Calculate scaling factors
        var containerWidth = document.getElementById('canvas-container').offsetWidth;
    var containerHeight = document.getElementById('canvas-container').offsetHeight;
    var scaleToFitWidth = containerWidth / img.width;
    var scaleToFitHeight = containerHeight / img.height;
    var scaleToFit = Math.min(scaleToFitWidth, scaleToFitHeight);

    // Set canvas dimensions
    canvas.setWidth(img.width * scaleToFit);
    canvas.setHeight(img.height * scaleToFit);

    // Add image to canvas
    canvas.add(img);
    img.set({
        scaleX: scaleToFit,
        scaleY: scaleToFit
    });
    canvas.renderAll();
    img.selectable = false; // Make the image unselectable

    $.each(jsonObject, function(index, element) {
        Add(element.x_axis, element.y_axis, element.area_width,element.area_height, 'rect1Left', 'rect1Top', 'rect1Width', 'rect1Height','#7F8A8C','#06242A',1,2); // Position of the first rectangle
    });

    Add(10,10, 150,100, 'rectLeft', 'rectTop', 'rectWidth', 'rectHeight', '#E3F03E', '#49F03E'); // Call the Add() function to add a new rectangle

});

function Add(left, top, width, height, leftInputId, topInputId, widthInputId, heightInputId,fillColor,StrokeColor,isMovable) {
    var rect = new fabric.Rect({
        left: left,
        top: top,
        fill: fillColor,
        width: width,
        height: height,
        objectCaching: false,
        stroke: StrokeColor, 
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

    canvas.add(rect);

    // Log coordinates while moving
    rect.on('moving', function() {
        document.getElementById(leftInputId).value = rect.left;
        document.getElementById(topInputId).value = rect.top;
       // document.getElementById(widthInputId).value = rect.width;
       // document.getElementById(heightInputId).value = rect.height;
    });
	
	 rect.on('scaling', function() {
        var scaleX = rect.scaleX;
        var scaleY = rect.scaleY;
        var width = rect.width * scaleX;
        var height = rect.height * scaleY;

        // Update hidden input fields
        document.getElementById(widthInputId).value = width;
        document.getElementById(heightInputId).value = height;
    });
}

// Event listener for the Add button
/*document.getElementById('add').addEventListener('click', function() {
    Add(20,50, 250,50, 'rectLeft', 'rectTop', 'rectWidth', 'rectHeight', '#888', '#444'); // Call the Add() function to add a new rectangle
});*/

/**
 ############################### Script for canvas#########################
 */

</script>
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

            //Form Fields Starts
            let formFieldIndex = 0;

            function createClonedItem(firstItem ,formFieldIndex, isSubClone, subFieldIndex = false) {

                var clonedFormFieldItem = $('.js-hidden-form-fields-option .js-form-fields-option').clone().removeClass('d-none');
                    if (firstItem) {
                        clonedFormFieldItem.addClass(firstItem);
                    }else{
                        clonedFormFieldItem.addClass('js-cloned-item');
                    }
                var namePrefix = isSubClone ? 'form-fields[' + formFieldIndex + '][multiple_list][' + formFieldIndex + '][' + subFieldIndex + '][' : 'form-fields[' + formFieldIndex + '][';
                clonedFormFieldItem.find('.js-rowIndex').val(formFieldIndex);
                clonedFormFieldItem.find('.js-rowSubIndex').val(subFieldIndex);
                clonedFormFieldItem.find('[name="form-fields[][name]"]').attr('name', namePrefix + 'name]');
                clonedFormFieldItem.find('[name="form-fields[][type]"]').attr('name', namePrefix + 'type]');
                clonedFormFieldItem.find('[name="form-fields[][field_position]"]').attr('name', namePrefix + 'field_position]');
                clonedFormFieldItem.find('[name="form-fields[][default_value]"]').attr('name', namePrefix + 'default_value]');
                clonedFormFieldItem.find('[name="form-fields[][default_image][]"]').attr('name', namePrefix + 'default_image][]');
                clonedFormFieldItem.find('[name="form-fields[][multiple_image]"]').attr('name', namePrefix + 'multiple_image]');
                clonedFormFieldItem.find('[name="form-fields[][meta_key1]"]').attr('name', namePrefix + 'meta_key1]');
                clonedFormFieldItem.find('[name="form-fields[][meta_key2]"]').attr('name', namePrefix + 'meta_key2]');

                return clonedFormFieldItem;
            }

            function cloneFormField(firstItem = false ,target = false, subClone = false, innerSubClone = false) {
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
                    newDiv.append(createClonedItem(firstItem,currentRowIndex, true, subformFieldIndex));


                    innerSubClone ? '' : $('.form-fields-container').append(newDiv);
                    target.after(newDiv);

                    //we will add append button here in new div
                    var appendBtn = '<div class="container text-end"><span class="again-clone-sub-form clone text-success" style="font-size: 20px;">+</span></div/>';
                    innerSubClone ? '' : newDiv.prepend(appendBtn);

                    //append delete button for multilist box
                    var appendBtn = '<div class="container text-end"><span class="delete-clone-sub-form clone text-danger" style="font-size: 20px;">x</span></div/>';
                    innerSubClone ? '' : newDiv.append(appendBtn);

                } else {
                    if(firstItem){
                        var clonedFormFieldItem = createClonedItem(firstItem ,formFieldIndex, false);
                        $('.form-fields-container').append(clonedFormFieldItem);
                    }else{
                        var clonedFormFieldItem = createClonedItem(false,formFieldIndex, false);
                        $('.form-fields-container').append(clonedFormFieldItem);
                    }

                    formFieldIndex++;
                }
            }

            cloneFormField('first-cloned-item');

            $('.js-add-form-fields').click(function () {
                cloneFormField();
            });

            $('body').on('click', '.js-add-sub-form-fields', function() {
                const closestParent = $(this).closest('.js-sub-cloned-item');
                cloneFormField(false ,closestParent, true, true);
            });

            $('body').on('click', '.js-remove-form-fields-cloned-item', function () {
                $(this).closest('.js-form-fields-option.js-cloned-item').remove();
            });

            //Form Fields End

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

