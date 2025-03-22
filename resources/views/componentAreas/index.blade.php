@extends('layout')
@section('title', 'Components Areas')
@section('subtitle', 'Components Areas')
@section('content')
<div class="col-lg-12">
    <div class="card">
        <div class="card-body">
        <a class="btn btn-primary mt-3" href="{{ url('/componentareas/create/'.$componentId)}}">New Area</a>
            <div class="box-header with-border" id="filter-box">
                <br>
                <!-- filter -->
                <div class="box-header with-border mt-4" id="filter-box">
                    <div class="box-body table-responsive" style="margin-bottom: 5%">
                    <table class="table table-borderless dashboard" id="projects">
                            <thead>
                                <tr>
                                    <th>#</id>
                                    <th>Component Name</th>
                                    <th>Area Name</th>
                                    <th>Actions</th>
                                </tr> 
                            </thead>
                            <tbody>
                            @if ($componentAreas->isEmpty())
                            <tr><td colspan="4"><p  class="text-center"><i>No records found.</i></p></td></tr>
                            @else
                                @forelse($componentAreas as $data)
                                <tr>
                                    <td>{{$data->id}}</a>
                                    <td>{{($data->component->component_name )}}</td>
                                    <td>{{($data->area_name )}}</td>
                                    <td>
                                        <a href="{{ url('/componentareas/edit/'.$data->component->id.'/'.$data->id)}}" title="Edit Area">
                                        <i style="color:#4154f1;" href="javascript:void(0)" class="fa fa-edit fa-fw pointer"> </i>
                                        </a>
                                        <a onclick="deleteArea('<?php echo $data->id?>')" title="Delete Area">
                                        <i style="color:#4154f1;" href="javascript:void(0)" class="fa fa-trash fa-fw pointer"> </i>
                                        </a> 
                                        <form id="delete_area_{{$data->id}}" action="{{ url('/componentareas/destroy/'.$data->component->id.'/'.$data->id)}}" method="POST">
                                            @csrf
                                            @method('DELETE')                                            
                                        </form>                                       
                                    </td>                    
                                </tr>
                                @empty
                                    <tr><td colspan="4"><p  class="text-center"><i>No records found.</i></p></td></tr>
                                @endforelse 
                                @endif
                            </tbody> 
                        </table>
                    </div>
                </div>
                <div>
                </div>
            </div>
        </div>
        @endsection
<script>
    function deleteArea(areaId){
        if(confirm("Are you sure you want to delete it?"))
         {
            $('#delete_area_'+areaId).submit();
         }
         else
         {
             return false;
         }
    }
</script>

        
