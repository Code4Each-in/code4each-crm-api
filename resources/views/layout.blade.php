<!DOCTYPE html>
<html lang="en">
@include('includes.css')

<body>
    <main id="main" class="main">
        <!-- Navigation -->
        @include('includes.header')
        <!-- Navigation -->
        <div class="pagetitle">
            <div class="row">
                <div class="col">
                    <h1>@yield('title')</h1>
                </div>
            </div>
        </div><!-- End Page Title -->
        <section class="section">
            <div class="row">
                <!-- <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body"> -->
                @yield('content')
                <!-- </div>
                    </div>
                </div> -->
            </div>
        </section>
    </main>
    @include('includes.jss')
    <script type="text/javascript">
    $(document).ready(function() {});
    </script>
    @yield('js_scripts')
</body>

</html>
