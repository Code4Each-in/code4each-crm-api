@extends('layout')
@section('title', 'Themes')
@section('subtitle', 'Themes')
@section('content')
<div class="col-lg-12">
    <div class="card">
        <div class="card-body">
        <a class="btn btn-primary mt-3" href="{{ route('themes.create') }}">New Theme</a>
            <div class="box-header with-border" id="filter-box">
                <br>
                <!-- filter -->
                <div class="box-header with-border mt-4" id="filter-box">
                    <div class="box-body table-responsive" style="margin-bottom: 5%">
                    <table class="table table-borderless dashboard" id="projects">
                            <thead>
                                <tr>
                                    <th>#</id>
                                    <th>Name</th>
                                    <th>Demo Url</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Accessibility</th>
                                    <th>Preview</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($themes as $data)
                                <tr>
                                    <td><a href="{{ url('/themes/edit/'.$data->id)}}">{{$data->id}}</a>
                                    <td>{{($data->name )}}</td>
                                    <td>
                                    <a href="{{($data->demo_url )}}" target="_blank">{{($data->demo_url )}}</a>
                                    
                                    </td>
                                    <td>{{ $data->category}}</td>
                                    <td>{{ ucfirst($data->status) }}</td>
                                    <td>{{ ucfirst($data->accessibility) }}</td>

                                    <td>
                                        @if ($data->preview_image)
                                        <img src="{{ asset('storage/' . $data->preview_image) }}" height="70" width="120" alt="Preview Image">
                                        @else
                                            ---
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ url('/themes/edit/'.$data->id)}}">
                                        <i style="color:#4154f1;" href="javascript:void(0)" class="fa fa-edit fa-fw pointer"> </i>
                                        </a>
                                        <i style="color:#4154f1;" onClick="deleteTheme('{{ $data->id }}')" href="javascript:void(0)" class="fa fa-trash fa-fw pointer"></i>
                                    </td>
                                </tr>
                                @empty
                                @endforelse
                        </table>
                    </div>
                </div>
                <div>
                </div>
            </div>
        </div>   

        @endsection
        @section('js_scripts')
        <script>

        $(document).ready(function() {
            setTimeout(function() {
                $('.message').fadeOut("slow");
            }, 2000);
            // $('#department_table').DataTable({
            //     "order": []
            //     //"columnDefs": [ { "orderable": false, "targets": 7 }]
            // });

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        });

        function deleteTheme(id) {
                if (confirm("Are you sure You Want To Delete?") == true) {
                    $.ajax({
                        url: '/themes/' + id,
                        type: 'DELETE',
                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                        success: function(response){
                            if(response.success){
                                // Handle success, maybe remove the deleted item from the UI
                                console.log('Deleted successfully');
                            } else {
                                console.error('Delete failed:', response.message);
                            }
                        },
                        error: function(xhr, status, error){
                            console.error('AJAX request failed:', status, error);
                        }
                    });
                }
            }
   </script>

@endsection
