@extends('layout')
@section('title', 'Agencies')
@section('subtitle', 'Agencies')
<style>
.avatar {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}
.btn-primary {
    transition: all 0.2s ease;
}

.btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
}
</style>
@section('content')
<div class="col-lg-12">
    <div class="card">
        <div class="card-body">
            <div class="box-header with-border" id="filter-box">
                <br>
                <div class="box-header with-border mt-4" id="filter-box">
                    <div class="box-body table-responsive" style="margin-bottom: 5%">
                        <table class="table table-borderless dashboard" id="agencies">
                            <thead>
                                <tr>
                                    <th>Agency</th>
                                    <th>Contact</th>
                                    <th>Address</th>
                                    <th>Website</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($agencies as $agency)
                                    @php
                                        $website = $agency->agencyWebsites->first();
                                    @endphp
                                    <tr>
                                        {{-- Agency --}}
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div>
                                                    <div class="fw-semibold">{{ $agency->name }}</div>
                                                    <small class="text-muted">ID #{{ $agency->id }}</small>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Contact --}}
                                        <td>
                                            @if($website)
                                                <div>{{ $website->email }}</div>
                                                <small class="text-muted">{{ $website->phone }}</small>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>

                                        {{-- Address --}}
                                        <td style="max-width: 260px; white-space: normal; ">
                                            @if($website)
                                                {{ collect([
                                                    $website->address,
                                                    $website->city,
                                                    $website->state,
                                                    $website->country,
                                                    $website->pin
                                                ])->filter()->implode(', ') }}
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>

                                        {{-- Website --}}
                                        <td style="max-width: 260px;">
                                            @if($website?->websiteDetail?->website_domain)
                                                <a href="{{ $website->websiteDetail->website_domain }}"
                                                target="_blank"
                                                class="text-decoration-none"
                                                data-bs-toggle="tooltip"
                                                data-bs-placement="bottom"
                                                title="{{ $website->websiteDetail->website_domain }}">
                                                    Click Here
                                                </a>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>

                                        {{-- Status --}}
                                        <td>
                                            @php
                                                $status = $website->status ?? null;
                                            @endphp

                                            @if(!$website)
                                                <span class="badge rounded-pill px-3 py-2 bg-light text-muted border">
                                                    <i class="bi bi-dash-circle me-1"></i>
                                                    No Website Assigned
                                                </span>

                                            @elseif($status === 'active')
                                                <span class="badge rounded-pill px-3 py-2 bg-success-subtle text-success border border-success">
                                                    <i class="bi bi-check-circle me-1"></i>
                                                    Active
                                                </span>

                                            @else
                                                <span class="badge rounded-pill px-3 py-2 bg-light text-muted border">
                                                    <i class="bi bi-question-circle me-1"></i>
                                                    {{ ucfirst($status) }}
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Action --}}
                                        <td class="text-end">
                                            <form action="{{ route('agencies.login-as', $agency->id) }}" method="POST" target="_blank">
                                                @csrf
                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-primary d-flex align-items-center gap-1 px-3"
                                                >
                                                    <i class="bi bi-box-arrow-in-right"></i>
                                                    <span>Login</span>
                                                </button>
                                            </form>
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

@endsection

@section('js_scripts')
    <script>
        $(document).ready(function() {
            $('#agencies').DataTable({
                "order": []

            });
        });
        document.addEventListener("DOMContentLoaded", function () {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
@endsection