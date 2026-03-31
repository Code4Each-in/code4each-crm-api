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

.btn-group .btn {
  border-radius: 50px;
  font-weight: 600;
  transition: background-color 0.25s ease, color 0.25s ease, box-shadow 0.25s ease;
  padding: 0.4rem 1.2rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.btn-group .btn:hover:not(.active) {
  box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
  transform: translateY(-1px);
}

.btn-group .btn.active {
  box-shadow: 0 6px 15px rgba(0, 123, 255, 0.45);
  /* Removed transform to prevent jump */
}

/* Toggle */
.switch {
  position: relative;
  width: 40px;
  height: 20px;
}

.switch input {
  display: none;
}

/* Default (INACTIVE = RED) */
.slider {
  position: absolute;
  inset: 0;
  background: #dc3545 !important; /* RED */
  border-radius: 50px;
  transition: 0.25s;
}

/* Circle */
.slider:before {
  content: "";
  position: absolute;
  height: 14px;
  width: 14px;
  left: 3px;
  top: 3px;
  background: #fff;
  border-radius: 50%;
  transition: 0.25s;
}

/* ACTIVE = GREEN */
.switch input:checked + .slider {
  background: #198754 !important; /* GREEN */
}

.switch input:checked + .slider:before {
  transform: translateX(18px);
}

/* Layout */
.status-toggle-wrapper {
  display: inline-flex;
  align-items: center;
  gap: 0px;
}

.status-text {
  font-size: 13px;
  font-weight: 600;
}

/* Loader (closer to text) */
.status-loader {
  display: flex;
  align-items: center;
}

th{
    white-space: nowrap;
}

.agency-cards-container {
  display: flex;
  gap: 20px;
  margin-bottom: 30px;
  flex-wrap: wrap;
}

.agency-card {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgb(0 0 0 / 0.05);
  padding: 18px 24px;
  flex: 1 1 150px; /* responsive width with min 150px */
  max-width: 180px;
  cursor: default;
  transition: box-shadow 0.3s ease;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}

.agency-card:hover {
  box-shadow: 0 6px 20px rgb(0 0 0 / 0.12);
}

.agency-card h5 {
  margin: 0 0 10px;
  font-weight: 700;
  font-size: 1rem;
  color: #1a237e; /* navy blue */
}

.agency-card .icon-count {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 1.1rem;
  color: #444;
}

.agency-card .icon-count i {
  font-size: 1.6rem;
  color: #3f51b5; /* blue accent */
}

.agency-card .count {
  font-weight: 700;
  font-size: 1.5rem;
  color: #222;
}
</style>
@section('content')
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
    <div id="statusToast" class="toast align-items-center text-white bg-success border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                Status updated successfully
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>
<div class="row">
    @php
        $allCount = $agencies->count();
        $activeCount = $agencies->filter(fn($a) => $a->agencyWebsites->first()?->status === 'active')->count();
        $inactiveCount = $agencies->filter(fn($a) => $a->agencyWebsites->first()?->status === 'inactive')->count();
        $noWebsiteCount = $agencies->filter(fn($a) => !$a->agencyWebsites->first())->count();
        $last15DaysCount = $agencies->filter(fn($a) => $a->created_at >= now()->subDays(15))->count();
    @endphp
    <div class="agency-cards-container">
        <div class="agency-card">
            <h5>All</h5>
            <div class="icon-count">
                <i class="bi bi-list"></i>
                <span class="count">{{ $allCount }}</span>
            </div>
        </div>
        <div class="agency-card">
            <h5>Active</h5>
            <div class="icon-count">
                <i class="bi bi-check-circle"></i>
                <span class="count">{{ $activeCount }}</span>
            </div>
        </div>
        <div class="agency-card">
            <h5>Inactive</h5>
            <div class="icon-count">
                <i class="bi bi-x-circle"></i>
                <span class="count">{{ $inactiveCount }}</span>
            </div>
        </div>
        <div class="agency-card">
            <h5>Last 15 Days</h5>
            <div class="icon-count">
                <i class="bi bi-clock"></i>
                <span class="count">{{ $last15DaysCount }}</span>
            </div>
        </div>
        <div class="agency-card">
            <h5>No Website Assigned</h5>
            <div class="icon-count">
                <i class="bi bi-exclamation-circle"></i>
                <span class="count">{{ $noWebsiteCount }}</span>
            </div>
        </div>
    </div>
</div>
<div class="col-lg-12">
    <div class="card">
        <div class="card-body">
            <div class="box-header with-border" id="filter-box">
                <br>
                <div class="box-header with-border mt-4" id="filter-box">
                    <div class="box-body table-responsive" style="margin-bottom: 5%">
                        <div class="mb-4 d-flex align-items-center gap-2 flex-wrap">
                            <span class="fw-semibold me-2">Status:</span>
                            <div class="btn-group btn-group-sm" role="group" aria-label="Status filter">
                                <button type="button" class="btn btn-outline-primary active d-flex align-items-center gap-1" data-status="all">
                                    <i class="bi bi-funnel-fill"></i> ALL
                                </button>
                                <button type="button" class="btn btn-outline-success d-flex align-items-center gap-1" data-status="active">
                                    <i class="bi bi-check-circle"></i> ACTIVE
                                </button>
                                <button type="button" class="btn btn-outline-secondary d-flex align-items-center gap-1" data-status="inactive">
                                    <i class="bi bi-x-circle"></i> INACTIVE
                                </button>
                                <button type="button" class="btn btn-outline-warning d-flex align-items-center gap-1" data-status="no-website">
                                    <i class="bi bi-exclamation-circle"></i> NO WEBSITE ASSIGNED
                                </button>
                                <button type="button" class="btn btn-outline-info d-flex align-items-center gap-1" data-status="last-15-days">
                                    <i class="bi bi-clock"></i> LAST 15 DAYS
                                </button>
                            </div>
                        </div>
                        <table class="table table-borderless dashboard" id="agencies">
                            <thead>
                                <tr>
                                    <th>Agency</th>
                                    <th>Contact</th>
                                    <th>Address</th>
                                    <th>Website</th>
                                    <th>Status</th>
                                    <th>Plan</th>
                                    <th>Start Date</th>
                                    <th>Days Left</th>
                                    <th>Next Invoice</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($agencies as $agency)
                                    @php
                                        $website = $agency->agencyWebsites->first();
                                    @endphp
                                    <tr 
                                        data-status="{{ $website ? ($website->status ?? 'inactive') : 'no-website' }}"
                                        data-created="{{ $agency->created_at->format('Y-m-d') }}"
                                        class="
                                            @if(is_numeric($agency->days_left))
                                                @if($agency->days_left == 0)
                                                    table-danger
                                                @elseif($agency->days_left <= 15)
                                                    table-warning
                                                @endif
                                            @endif
                                        "
                                    >
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
                                            @if($website)
                                            <form class="status-update-form" method="POST" action="{{ route('agencies.update-status', $website->id) }}">
                                                @csrf
                                                @method('PATCH')

                                                <div class="status-toggle-wrapper">
                                                    <label class="switch">
                                                        <input type="checkbox" class="status-toggle"
                                                            {{ $website->status === 'active' ? 'checked' : '' }}>
                                                        <span class="slider"></span>
                                                    </label>

                                                    <span class="status-text {{ $website->status === 'active' ? 'text-success' : 'text-danger' }}">
                                                        {{ $website->status === 'active' ? 'Active' : 'Inactive' }}
                                                    </span>

                                                    <div class="status-loader d-none">
                                                        <div class="spinner-border spinner-border-sm"></div>
                                                    </div>
                                                </div>
                                            </form>
                                            @else
                                            <span class="badge rounded-pill px-3 py-2 bg-light text-muted border">
                                                <i class="bi bi-dash-circle me-1"></i>
                                                No Website Assigned
                                            </span>
                                            @endif
                                        </td>

                                        <td>
                                            {{ $agency->plan_name }}
                                        </td>

                                        <td>
                                            {{ $agency->plan_start }}
                                        </td>

                                        <td>
                                            @if(is_numeric($agency->days_left))
                                                {{ $agency->days_left }} days
                                            @else
                                                —
                                            @endif
                                        </td>

                                        <td>
                                            {{ $agency->next_invoice }}
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
            // Initialize DataTable
            var table = $('#agencies').DataTable({
                "order": []
            });

            // Add custom status filter
            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {
                    var selectedStatus = $('.btn-group button.active').data('status');
                    var row = $(table.row(dataIndex).node());
                    var rowStatus = row.attr('data-status');
                    var createdDate = row.attr('data-created'); // format: YYYY-MM-DD

                    // ALL filter
                    if (selectedStatus === 'all') return true;

                    // Last 15 days filter
                    if (selectedStatus === 'last-15-days') {
                        if (!createdDate) return false;

                        let today = new Date();
                        let fifteenDaysAgo = new Date();
                        fifteenDaysAgo.setDate(today.getDate() - 15);

                        let rowDate = new Date(createdDate);
                        return rowDate >= fifteenDaysAgo && rowDate <= today;
                    }

                    // Other status filters
                    return rowStatus === selectedStatus;
                }
            );

            $('.btn-group button').click(function() {
                $('.btn-group button').removeClass('active');
                $(this).addClass('active');
                table.draw();
            });

            $('.status-toggle').change(function () {
                let toggle = $(this);
                let form = toggle.closest('form');
                let wrapper = toggle.closest('.status-toggle-wrapper');
                let loader = wrapper.find('.status-loader');
                let text = wrapper.find('.status-text');

                let newStatus = toggle.is(':checked') ? 'active' : 'inactive';
                let oldStatus = newStatus === 'active' ? 'inactive' : 'active';

                // Confirmation
                if (!confirm(`Are you sure you want to ${newStatus} this agency?`)) {
                    toggle.prop('checked', !toggle.is(':checked'));
                    return;
                }

                loader.removeClass('d-none');
                toggle.css('pointer-events', 'none');

                $.ajax({
                    url: form.attr('action'),
                    method: 'PATCH',
                    data: {
                        _token: '{{ csrf_token() }}',
                        status: newStatus
                    },
                    success: function () {
                        let row = toggle.closest('tr');
                        row.attr('data-status', newStatus);

                        // Update text + color
                        text.text(newStatus === 'active' ? 'Active' : 'Inactive');

                        text.removeClass('text-success text-danger')
                            .addClass(newStatus === 'active' ? 'text-success' : 'text-danger');

                        showToast(
                            newStatus === 'active'
                                ? 'Agency activated successfully'
                                : 'Agency deactivated successfully',
                            newStatus === 'active' ? 'success' : 'danger'
                        );
                        table.draw(false);
                    },
                    error: function () {
                        showToast('Update failed', 'danger');

                        // revert toggle
                        toggle.prop('checked', !toggle.is(':checked'));
                    },
                    complete: function () {
                        loader.addClass('d-none');
                        toggle.css('pointer-events', 'auto');
                    }
                });
            });

        });

        function showToast(message, type) {
            let toastEl = $('#statusToast');

            toastEl.removeClass('bg-success bg-danger')
                .addClass(type === 'success' ? 'bg-success' : 'bg-danger');

            toastEl.find('.toast-body').text(message);

            // Create toast with auto hide
            let toast = new bootstrap.Toast(toastEl[0], {
                delay: 2000, 
                autohide: true
            });

            toast.show();
        }

        document.addEventListener("DOMContentLoaded", function () {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
@endsection