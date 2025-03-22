@extends('layout')
@section('title', 'Dashboard')
@section('subtitle', 'Dashboard')
@section('content')
<div class="row">
    <div class="col-lg-8 dashboard" style="margin-top: 20px !important;">
        <div class="row">
            <!-- Users Card -->
            <div class="col-xxl-4 col-md-6">
                <div class="card info-card sales-card">
                    <div class="filter">
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Users</h5>
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-person"></i>
                            </div>
                            <div class="ps-3">

                                <h6>{{ $userCount}}</h6>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <!-- End Users Card -->

            <!-- Components Card -->
            <div class="col-xxl-4 col-md-6">
                <div class="card info-card sales-card">
                    <div class="filter">
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Components</h5>
                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-person"></i>
                            </div>
                            <div class="ps-3">

                                <h6>{{ $componentsCount}}</h6>
                            </div>
                        </div>
                    </div>

                </div>
            </div><!-- End Components Card -->

            <!-- Customers Card -->

            <div class="col-12">
                <div class="card">

                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 dashboard" style="margin-top: 20px ">

    </div>
</div>
@endsection
@section('js_scripts')
<script>
</script>
@endsection
