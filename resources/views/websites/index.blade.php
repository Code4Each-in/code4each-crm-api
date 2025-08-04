@extends('layout')
@section('title', 'Websites')
@section('subtitle', 'Websites')
@section('content')
<div class="col-lg-12">
    <div class="card">
        <div class="card-body">
            <a class="btn btn-primary mt-3" href="#" data-bs-toggle="modal" data-bs-target="#addThemeModal">Add New Template</a>
            <div class="box-header with-border" id="filter-box">
                <br>
                <div class="box-header with-border mt-4" id="filter-box">
                    <div class="box-body table-responsive" style="margin-bottom: 5%">
                        <table class="table table-borderless dashboard" id="projects">
                            <thead>
                                <tr>
                                    <th>#</id>
                                    <th>Template Name</th>
                                    <th>Category</th>
                                    <th>Component</th>
                                    <th>Status</th>
                                    <th>Preview</th>
                                    <th>Preview Link</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($templates as $template)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $template['template_name'] }}</td>
                                        <td>{{ $template['categories'] }}</td>
                                        <td>
                                            @if(!empty($template['components']))
                                                {{ implode(', ', $template['components']) }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>{{ ucfirst($template['status']) }}</td>
                                        <td>
                                            @if ($template['preview'])
                                            <img src="{{ asset('storage/' . $template['preview']) }}" height="70" width="120" alt="Preview Image">
                                            @else
                                                ---
                                            @endif
                                        </td>
                                        <td>
                                            @if ($template['previewLink'])
                                                <a href="{{ $template['previewLink'] }}" target="_blank" title="Preview Link">
                                                    <i class="fa fa-link fa-fw"></i> 
                                                </a>
                                            @else
                                                ---
                                            @endif
                                        </td>
                                        <td>
                                            <a href="javascript:void(0)" class="edit-template" data-id="{{ $template['id'] }}" title="Edit template">
                                            <i class="fa fa-edit fa-fw pointer"></i>
                                            </a>
                                            <a href="javascript:void(0)" class="delete-template" data-id="{{ $template['id'] }}" title="Delete template">
                                            <i class="fa fa-trash fa-regular"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Template Modal Start -->
<div class="modal fade" id="addThemeModal" tabindex="-1" aria-labelledby="addThemeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addThemeModalLabel">Add New Theme</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addThemeForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="templateName" class="form-label">Template Name<span class="text-danger templateName-asterisk">*</span></label>
                        <input type="text" class="form-control" id="templateName" name="templateName">
                        <div class="text-danger templateName-error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="category" class="form-label">Category<span class="text-danger category-asterisk">*</span></label>
                        <select class="form-select category" id="category" name="category[]" multiple>
                            <option value="" disabled>Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->name }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <div class="text-danger category-error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="previewImage" class="form-label">Preview Image<span class="text-danger previewImage-asterisk">*</span></label>
                        <input type="file" class="form-control" id="previewImage" name="previewImage" accept="image/*">
                        <div class="text-danger previewImage-error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status<span class="text-danger status-asterisk">*</span></label>
                        <select name="status" class="form-select" id="status">
                            <option value="">Select Status</option>
                            <option value="draft">Draft</option>
                            <option value="testing">Testing</option>
                            <option value="active">Active</option>
                            <option value="deactive">Deactive</option>
                        </select>
                        <div class="text-danger status-error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="component" class="form-label">Component<span class="text-danger component-asterisk">*</span></label>
                            <select class="form-select component" id="component" name="component[]" multiple>
                                <option value="" disabled>Select Component</option>
                                @foreach($components as $component)
                                    <option value="{{ $component->component_unique_id }}">{{ $component->component_name }}</option>
                                @endforeach
                            </select>
                            <div class="text-danger component-error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="previewLink" class="form-label">Preview Link<span class="text-danger previewLink-asterisk">*</span></label>
                        <input type="text" class="form-control" id="previewLink" name="previewLink">
                        <div class="text-danger previewLink-error"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Add Template Modal End -->
<!-- Edit Template Modal Start -->
<div class="modal fade" id="editThemeModal" tabindex="-1" aria-labelledby="editThemeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editThemeModalLabel">Edit Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editThemeForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id" id="editTemplateId" value="">
                    <div class="mb-3">
                        <label for="editTemplateName" class="form-label">Template Name<span class="text-danger editTemplateName-asterisk">*</span></label>
                        <input type="text" class="form-control" id="editTemplateName" name="templateName">
                        <div class="text-danger editTemplateName-error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="editCategory" class="form-label">Category<span class="text-danger editCategory-asterisk">*</span></label>
                        <select class="form-select editCategory" id="editCategory" name="category[]" multiple>
                            <option value="" disabled>Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->name }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <div class="text-danger editCategory-error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="editPreviewImage" class="form-label">Preview Image<span class="text-danger editPreviewImage-asterisk">*</span></label>
                        <input type="file" class="form-control" id="editPreviewImage" name="previewimage" accept="image/*">
                        <div class="text-danger editPreviewImage-error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="" class="form-label">Uploaded Preview</label>
                        <div class="col-sm-9">
                                <img id="edituploadedPreview" src="" alt="Uploaded Preview" class="img-thumbnail" style="max-width: 200px;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editStatus" class="form-label">Status<span class="text-danger editStatus-asterisk">*</span></label>
                        <select name="status" class="form-select" id="editStatus">
                            <option value="">Select Status</option>
                            <option value="draft">Draft</option>
                            <option value="testing">Testing</option>
                            <option value="active">Active</option>
                            <option value="deactive">Deactive</option>
                        </select>
                        <div class="text-danger editStatus-error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="editComponent" class="form-label">Component<span class="text-danger editComponent-asterisk">*</span></label>
                        <select class="form-select editComponent" id="editComponent" name="component[]" multiple>
                            <option value="" disabled>Select Component</option>
                            @foreach($components as $component)
                                <option value="{{ $component->component_unique_id }}">{{ $component->component_name }}</option>
                            @endforeach
                        </select>
                        <div class="text-danger editComponent-error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="editPreviewLink" class="form-label">Preview Link<span class="text-danger editPreviewLink-asterisk">*</span></label>
                        <input type="text" class="form-control" id="editPreviewLink" name="previewLink">
                        <div class="text-danger editPreviewLink-error"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Edit Template Modal End -->

@endsection

@section('js_scripts')
<script>
$(document).ready(function() {
    $('#addThemeModal').on('shown.bs.modal', function() {
        $('.component').select2({
            dropdownParent: $('#addThemeModal')
        });
        $('.category').select2({
            dropdownParent: $('#addThemeModal')
        });
    });
    $('#editThemeModal').on('shown.bs.modal', function() {
        $('.editCategory').select2({
            dropdownParent: $('#editThemeModal')
        });
        $('.editComponent').select2({
            dropdownParent: $('#editThemeModal')
        });
    });

    $('#addThemeModal').on('hidden.bs.modal', function() {
        // Reset form fields and error messages
        $('#addThemeForm')[0].reset(); 
        $('.templateName-error').text(''); 
        $('.category-error').text(''); 
        $('.previewLink-error').text(''); 
        $('.previewImage-error').text(''); 
        $('.status-error').text(''); 
        $('.component-error').text(''); 
        $('.select2').val('').trigger('change'); 
    });
    $('#editThemeModal').on('hidden.bs.modal', function() {
        $('.editTemplateName-error').text(''); 
        $('.editCategory-error').text(''); 
        $('.editPreviewLink-error').text(''); 
        $('.editPreviewImage-error').text(''); 
        $('.editStatus-error').text(''); 
        $('.editComponent-error').text(''); 
    });
    
    $('#addThemeForm').on('submit', function(e) {
        e.preventDefault();

        let formData = new FormData(this);

        $.ajax({
            url: "{{ route('website.store') }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                alert(response.message);
                $('#addThemeModal').modal('hide');
                location.reload(); // Reload to reflect new theme
            },
            error: function(xhr) {
                let errors = xhr.responseJSON.errors;
                // $('.text-danger').text('');
                for (let field in errors) {
                    let errorMessage = errors[field][0]; 
                    $('.' + field + '-error').text(errorMessage); 
                }
            }
        });
    });

    // When the user clicks "Edit" on a template
    $('body').on('click', '.edit-template', function(e) {
        e.preventDefault();
        let templateId = $(this).data('id');
        $("#editTemplateId").val(templateId);
        $.ajax({
            url: '/template/' + templateId + '/edit',
            type: 'GET',
            success: function(response) {
                console.log(response.template);
                let template = response.template;
                let components = response.components;
                $('#editTemplateName').val(template.template_name);
                $('#editCategory').val(template.category_id.split(',')).trigger('change');
                $('#edituploadedPreview').attr('src', 'storage/' + template.featured_image);
                $('#editStatus').val(template.status).trigger('change');
                $('#editComponent').val(components.map(component => component.component_unique_id)).trigger('change'); 
                $('#editPreviewLink').val(template.preview_link);
                $('#editThemeModal').modal('show');
            }
        });
    });


    // Handle Edit Form Submission
    $('#editThemeForm').on('submit', function(e) {
    e.preventDefault();
    let templateId = $('#editTemplateId').val();
    let formData = new FormData(this);

        $.ajax({
            url: "/template/" + templateId, 
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                alert(response.message);
                $('#editThemeModal').modal('hide');
                location.reload();
            },
            error: function(xhr) {
                let errors = xhr.responseJSON.errors;
                for (let field in errors) {
                    let errorMessage = errors[field][0];
                    $('.edit' + field.charAt(0).toUpperCase() + field.slice(1) + '-error').text(errorMessage);
                }
            }
        });
    });

    $('body').on('click', '.delete-template', function(e) {
        e.preventDefault();
        let templateId = $(this).data('id'); 
        if (confirm('Are you sure you want to delete this template?')) {
            $.ajax({
                url: '/template/' + templateId,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    alert(response.message);
                    $('tr').has('a[data-id="' + templateId + '"]').remove();
                },
                error: function(xhr) {
                    alert('Error: ' + xhr.responseJSON.message);
                }
            });
        }
    });
});
</script>
@endsection
