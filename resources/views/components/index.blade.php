@extends('layout')
@section('title', 'Components')
@section('subtitle', 'Components')
@section('content')
<div class="col-lg-12">
    <div class="card">
        <div class="card-body">
        <a class="btn btn-primary mt-3" href="{{ route('components.create') }}">New Component</a>
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
                                    <th>Path</th>
                                    <th>Type</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Preview</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($components as $data)
                                <tr>
                                    <td><a href="{{ url('/components/edit/'.$data->id)}}">{{$data->id}}</a>
                                    <td>{{($data->component_name )}}</td>
                                    <td>
                                    {{($data->path )}}
                                    </td>
                                    <td>
                                    {{($data->type )}}
                                    </td>
                                    <td>{{ $data->category}}</td>
                                    <td>{{ $data->status }}</td>
                                    <td>
                                        @if ($data->preview)
                                        <img src="{{ asset('storage/' . $data->preview) }}" height="70" width="120" alt="Preview Image">
                                        @else
                                            ---
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ url('/components/edit/'.$data->id)}}">
                                        <i style="color:#4154f1;" href="javascript:void(0)" class="fa fa-edit fa-fw pointer"> </i>
                                        </a>

                                        <!-- <i style="color:#4154f1;" onClick="deleteProjects('{{ $data->id }}')" href="javascript:void(0)" class="fa fa-trash fa-fw pointer"></i> -->
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

        <!----Add Projects--->
        <div class="modal fade" id="addProjects" tabindex="-1" aria-labelledby="role" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content" style="width: 630px;">
                    <div class="modal-header">
                        <h5 class="modal-title" id="role">Add Project</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="addProjectsForm" enctype="multipart/form-data" >
                        @csrf
                        <div class="modal-body">
                            <div class="alert alert-danger" style="display:none"></div>
                            <div class="row mb-3">
                                <label for="title" class="col-sm-3 col-form-label required">Project Name</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="project_name" id="project_name">
                                </div>
                            </div>
                            {{-- <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label required ">Assign To</label>
                                <div class="col-sm-9">
                                <select class="form-select form-control" id="user" name="assign_to[]" data-placeholder="Select User" multiple>
                                <option value="" disabled>Select User</option>
                                         @foreach ($users as $data)
                                        <option value="{{$data->id}}">
                                        {{$data->first_name}} - {{$data->role_name}}
                                        </option>
                                        @endforeach
                                </select>
                                </div>
                            </div> --}}
                            <div class="row mb-3">
                                <label for="title" class="col-sm-3 col-form-label ">Live Url</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="live_url" id="live_url">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="title" class="col-sm-3 col-form-label ">Dev Url</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="dev_url" id="dev_url">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="title" class="col-sm-3 col-form-label ">Git Repository</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="git_repo" id="git_repo">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="title" class="col-sm-3 col-form-label">Tech Stacks</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="tech_stacks" id="tech_stacks" data-role="taginput">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="tinymce_textarea" class="col-sm-3 col-form-label">Description</label>
                                <div class="col-sm-9">
                                    <textarea name="description" class="form-control" id="tinymce_textarea"></textarea>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="start_date" class="col-sm-3 col-form-label required">Start Date</label>
                                <div class="col-sm-9">
                                    <input type="date" class="form-control" id="start_date" name="start_date">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="end_date" class="col-sm-3 col-form-label">End Date</label>
                                <div class="col-sm-9">
                                    <input type="date" class="form-control" id="end_date" name="end_date">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="tinymce_textarea" class="col-sm-3 col-form-label">Credentials</label>
                                <div class="col-sm-9">
                                    <textarea name="credentials" class="form-control" id="tinymce_textarea"></textarea>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="status" class="col-sm-3 col-form-label required">Status</label>
                                <div class="col-sm-9">
                                    <select name="status" class="form-select" id="status">
                                        <option value="not_started">Not Started</option>
                                        <option value="active">Active</option>
                                        <option value="deactivated">Deactivated</option>
                                        <option value="completed">Completed</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="document" class="col-sm-3 col-form-label ">Document</label>
                                <div class="col-sm-9">
                                    <input type="file" class="form-control" name="add_document[]" id="add_document" multiple />
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary" href="javascript:void(0)">Save</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!---end Add modal-->

        <div class="modal fade" id="ShowAssign" tabindex="-1" aria-labelledby="ShowAssign" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Project Assign To</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row projectAsssign">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class=" btn
                            btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <!---end Add modal-->

        @endsection
        @section('js_scripts')
        <script>
            $(document).ready(function() {
                setTimeout(function() {
                    $('.message').fadeOut("slow");
                }, 2000);
                $('#projects').DataTable({
                    "order": []

                });
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $("#addProjectsForm").submit(function(event) {
                    event.preventDefault();
                    var formData = new FormData(this);
                    // var totalfiles = document.getElementById('add_document').files.length;

                    // for (var index = 0; index < totalfiles; index++) {
                    //     formData.append("add_document[]" + index, document.getElementById('add_document')
                    //         .files[
                    //             index]);
                    // }
                    // console.log(formData);
                    $.ajax({
                        type: 'POST',
                        url: "{{ url('/add/projects')}}",
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: (data) => {
                            if (data.errors) {
                                $('.alert-danger').html('');
                                $.each(data.errors, function(key, value) {
                                    $('.alert-danger').show();
                                    $('.alert-danger').append('<li>' + value + '</li>');
                                })

                            } else {
                                $("#addProjects").modal('hide');
                                location.reload();
                            }
                        },
                        error: function(data) {}
                    });
                });

                $( '#user' ).select2( {
                    dropdownParent: $('#addProjects')
                } );
            });

            function ShowAssignModal(id) {
                $('.projectAsssign').html('');
                $('#ShowAssign').modal('show');
                $.ajax({
                    type: 'POST',
                    url: "{{ url('/project/assign')}}",
                    data: {
                        id: id
                    },
                    cache: false,
                    success: (data) => {
                        if (data.projectAssigns.length > 0) {
                            var html = '';
                            $.each(data.projectAssigns, function(key, assign) {
                                var picture = 'blankImage';
                                if (assign.profile_picture != "") {
                                    picture = assign.profile_picture;
                                }
                                html +=
                                    '<div class="row leaveUserContainer mt-2 "> <div class="col-md-2"><img src="{{asset("assets/img")}}/' +
                                    picture +
                                    '"" width="50" height="50" alt="" class="rounded-circle"></div><div class="col-md-10 "><p><b>' +
                                    assign.first_name + '</b></p></div></div>';
                            })
                            $('.projectAsssign').html(html);
                        } else {
                            $('.projectAsssign').html('<span>No record found <span>');
                        }
                    },
                    error: function(data) {}
                });

            }

            function openprojectModal() {
                document.getElementById("addProjectsForm").reset();
                $('#addProjects').modal('show');
            }

            function editTickets(id) {
                $('#editProjects').modal('show');
                $('#ticket_id').val(id);

                $.ajax({
                    type: "POST",
                    url: "{{ url('/edit/project') }}",
                    data: {
                        id: id
                    },
                    success: function(res) {
                        if (res.projects != null) {
                            $('#edit_title').val(res.projects.title);
                            $('#edit_description').val(res.projects.description);
                            $('#edit_status').val(res.projects.status);
                            $('#edit_comment').val(res.projects.comment);

                            $('#edit_priority').val(res.projects.priority);
                            // var test = "http://127.0.0.1:8000/public/assets/img/" + res.projects.profile_picture;
                            // $("#profile").html(
                            //     '<img src="{{asset("assets/img")}}/' + res.projects.profile_picture +
                            //     '" width = "100" class = "img-fluid img-thumbnail" > '
                            // );

                        }
                        if (res.ticketAssign != null) {
                            $.each(res.ticketAssign, function(key, value) {
                                $('#edit_assign option[value="' + value.user_id + '"]')
                                    .attr(
                                        'selected', 'selected');
                            })
                        }
                    }
                });
            }

            // function deleteProjects(id) {
            //     $('#ticket_id').val(id);
            //     // var id = $('#department_name').val();

            //     if (confirm("Are you sure ?") == true) {
            //         $.ajax({
            //             type: "DELETE",
            //             url: "{{ url('/delete/projects') }}",
            //             data: {
            //                 id: id
            //             },
            //             dataType: 'json',
            //             success: function(res) {
            //                 location.reload();
            //             }
            //         });
            //     }
            // }

            $('.readMoreLink').click(function(event) {
                event.preventDefault();

                var description = $(this).siblings('.description');
                var fullDescription = $(this).siblings('.fullDescription');

                description.text(fullDescription.text());
                $(this).hide();
                $(this).siblings('.readLessLink').show();
            });

            $('.readLessLink').click(function(event) {
                event.preventDefault();

                var description = $(this).siblings('.description');
                var fullDescription = $(this).siblings('.fullDescription');

                var truncatedDescription = fullDescription.text().substring(0, 100) + '...';
                description.text(truncatedDescription);
                $(this).hide();
                $(this).siblings('.readMoreLink').show();
            });

            //TAGS KEY JS
            $('#tech_stacks').tagsinput({
            confirmKeys: [13, 188]
            });

            $('#tech_stacks').on('keypress', function(e){
            if (e.keyCode == 13){
                e.preventDefault();
            };
            });

        </script>
    <!-- <script src="{{ asset('assets/js/bootstrap-tags.js') }}"></script> -->
@endsection
