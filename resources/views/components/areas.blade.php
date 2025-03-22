@extends('layout')
@section('title', 'Components')
@section('subtitle', 'Components')
<style>
    .imagemaps-wrapper{
        position:relative;
    }
    .imagemaps-wrapper img { 
        max-width:100%        
    }
</style>
@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">
<div class="col-lg-10 mx-auto">
    <div class="card">
        <div class="card-body">
            <div class="row mb-5 mt-4">
                <label for="component_name" class="col-sm-3 col-form-label">Component Name</label>
                <div class="col-sm-9">
                    {{$componentData->component_name}}
                </div>
            </div>

            <div class="row mb-5 mt-4">
                <label for="path" class="col-sm-3 col-form-label">Component Image</label>
                <div class="col-sm-9">
                    <div class="imagemaps-wrapper">                    
                        @if ($componentData->preview)
                            <img src="{{ asset('storage/' . $componentData->preview) }}" alt="Component Image">

                        @else
                            NO Preview Image
                        @endif
                    </div>
                    <div class="imagemaps-control">
                        <fieldset>
                            <legend>Settings</legend>
                            <table class="table table-hover">
                            <thead>
                                <tr>
                                <th scope="col">#</th>
                                <th scope="col">Area Name</th>
                                <!--<th scope="col">Target</th>-->
                                <th scope="col">Save</th>
                                <th scope="col">Delete</th>
                                </tr>
                            </thead>
                            <tbody class="imagemaps-output">
                                <tr class="item-###">
                                <th scope="row">###</th>
                                <td><input type="text" class="form-control area-href"></td>
                                <!--<td>
                                    <select class="form-control area-target">
                                    <option value="_self">_selfuyiuy</option>
                                    <option value="_blank">_blank</option>
                                    </select>
                                </td>-->
                                <td>
                                    <button type="button" class="btn btn-success btn-save">Save</button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-delete">Delete</button>
                                </td>
                                </tr>
                            </tbody>
                            </table>
                        </fieldset>
                        <div style="display:none;">
                            <button type="button" class="btn btn-info btn-add-map">Add New</button>
                            <button type="button" class="btn btn-success btn-get-map">Get Code</button>
                        </div>
                    </div>
                    <div id="ajaxMessage" style="color:green"></div>
                    
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js_scripts')
<script src="{{ asset('js/jquery-ui.min.js') }}"></script>
<script src="{{ asset('js/jquery.imagemaps.js') }}"></script>
<script>
    var x_axis = '';  var y_axis = '';
$('.imagemaps-wrapper').imageMaps({
    addBtn: '.btn-add-map',
    output: '.imagemaps-output',
    stopCallBack: function(active, coords){
        // console.log(active);
         //console.log(coords);
         x_axis = coords.x1;
         y_axis = coords.y1;
         var rectWidth = coords.x2;
         var rectHeight = coords.y2;

         console.log('x-axis'+x_axis+'------'+'y-axis'+y_axis);
    }
});

//click and make area once 
$(document).ready(function () {
    $(".btn-add-map").trigger('click');
});

//save button
$(document).on(
    'click',
    '.btn-save',
    function(event) {
        event.preventDefault();  
        var areaId = $(this).attr('area-id');  
        var areaName = $(this).closest('tr').find("input").val();
        if(!areaName){
            alert('Please provide some area name!');
        }else{
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                type: 'POST',
                url: "{{ url('/saveArea')}}",
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                data: JSON.stringify({'componentId':'<?php  echo $componentData->id; ?>','areaId':areaId, 'areaName': areaName, 'x_axis':x_axis, 'y_axis':y_axis}),
                processData: false,
                contentType: false,
                success: (data) => {
                    if(data.status == 'success'){
                        $('#ajaxMessage').html(data.message);
                        setTimeout(function() {
                            $('#ajaxMessage').fadeOut('fast');
                        }, 1000); // <-- time in milliseconds
                    }
                },
                error: function(xhr, status, error) {
                    // Handle error response
                    console.error(xhr.responseText);
                }
            });
            return false;
        }
        return false;
    }
);

$('.btn-get-map').on('click', function(){
  let oParent = $(this).parent().parent().parent();
  //console.log(oParent);
  let result  = oParent.find('.imagemaps-wrapper').clone();
  result.children('div').remove();
  // console.log(result.html());
  alert(result.html());
});


</script>

@endsection   

