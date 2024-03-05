<!DOCTYPE html>
<html lang="en">

<body>

    <!-- ======= Header ======= -->
    <header id="header" class="header fixed-top d-flex align-items-center">

        <div class="d-flex align-items-center justify-content-between">
            <a href="{{ url('/dashboard') }}" class="logo d-flex align-items-center">
                <img src="{{ asset('img/speedy-logo-3.png') }}" alt="Speedy Sites">
                <!-- <span class="d-none d-lg-block">Management</span> -->
            </a>
            <i class="bi bi-list toggle-sidebar-btn"></i>
        </div><!-- End Logo -->

        <!-- <div class="search-bar">
            <form class="search-form d-flex align-items-center" method="POST" action="#">
                <input type="text" name="query" placeholder="Search" title="Enter search keyword">
                <button type="submit" title="Search"><i class="bi bi-search"></i></button>
            </form>
        </div>End Search Bar -->

        <nav class="header-nav ms-auto">
            <ul class="d-flex align-items-center">

                <li class="nav-item d-block d-lg-none">
                    <a class="nav-link nav-icon search-bar-toggle " href="#">
                        <i class="bi bi-search"></i>
                    </a>
                </li><!-- End Search Icon-->

                <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
                    {{-- @if (auth()->user()->profile_picture)
                    <img src="{{asset('assets/img/').'/'.auth()->user()->profile_picture}}" id="profile_picture"
                        alt="Profile" height="50px" width="50px" class="rounded-circle picture js-profile-picture">
                    @else
                    <img src="{{asset('img/blankImage.jpg')}}" id="profile_picture"
                        alt="Profile" height="50px" width="50px" class="rounded-circle picture js-profile-picture">
                    @endif --}}
                    <img src="{{asset('img/blankImage.jpg')}}" id="profile_picture"
                        alt="Profile" height="50px" width="50px" class="rounded-circle picture js-profile-picture">
                </a>
                <li class="nav-item dropdown pe-3">

                    <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                        <!-- <img src="assets/img/profile-img.jpg" alt="Profile" class="rounded-circle"> -->
                        <span
                            class="d-none d-md-block dropdown-toggle ps-2">{{ auth()->user()->first_name ?? " " }}</span>
                    </a><!-- End Profile Iamge Icon -->

                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                        <li class="dropdown-header">
                            <div class="row">
                                <div class="col-md-4">
                                    {{-- @if (auth()->user()->profile_picture)
                                    <img src="{{asset('assets/img/').'/'.auth()->user()->profile_picture}}"
                                        id="profile_picture" alt="Profile" height="50px" width="50px"
                                        class="rounded-circle picture js-profile-picture">
                                    @else
                                    <img src="{{asset('assets/img/blankImage.jpg')}}"
                                        id="profile_picture" alt="Profile" height="50px" width="50px"
                                        class="rounded-circle picture js-profile-picture">
                                    @endif --}}
                                </div>
                                <div class="col-md-5">
                                    <h6>{{ auth()->user()->first_name ?? " " }}</h6>
                                    <span>{{ auth()->user()->role->name ?? " " }}</span>
                                </div>
                            </div>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="">
                                <i class="bi bi-person"></i>
                                <span>My Profile</span>
                            </a>
                        </li>
                        <hr class="dropdown-divider">

                        <a class="dropdown-item d-flex align-items-center" href="{{ route('logout')}}">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Log Out</span>
                        </a>
                </li>

            </ul><!-- End Profile Dropdown Items -->
            </li><!-- End Profile Nav -->

            </ul>
        </nav><!-- End Icons Navigation -->

        @if(session()->has('message'))
            <div class="alert alert-success header-alert fade show" role="alert" id="header-alert">
                        <i class="bi bi-check-circle me-1"></i>
                        {{ session()->get('message') }}
            </div>
        @endif

        @if(session()->has('error'))

        <div class="alert alert-danger header-alert fade show" role="alert" id="header-alert">
                        <i class="bi bi-exclamation-octagon me-1"></i>
                        {{ session()->get('error') }}
        </div>
        @endif

    </header><!-- End Header -->

    <!-- ======= Sidebar ======= -->
    <aside id="sidebar" class="sidebar">

        <ul class="sidebar-nav" id="sidebar-nav">
        <li class="nav-item">
                <a class="nav-link {{ request()->is('dashboard') ? '' : 'collapsed' }}" href="{{ route('dashboard.index') }}">
                    <i class="bi bi-person-square"></i>
                    <span>Dashboard
                    </span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('components') ? '' : 'collapsed' }}" href="{{ route('components.index') }}">
                    <i class="bi bi-person-square"></i>
                    <span>Components
                    </span>
                </a>
            </li>

        </ul>

    </aside><!-- End Sidebar-->

</body>

</html>
