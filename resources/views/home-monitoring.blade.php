@extends('layouts.monitoring')

@section('content')
<div class="content-wrapper" style="height: calc(100vh - 60px);"> <!-- Adjust height sesuai kebutuhan -->
    <div class="row">
        <div class="col-12 mb-3">
            <a href="{{ route('home') }}" class="link">
                <div class="d-flex align-items-center justify-content-center">
                    <img src="{{ asset('assets/images/sc-logo.png') }}" alt="Logo" height="50" class="me-3">
                    <h1 class="mb-0">Quality EWS</h1>
                </div>
            </a>
        </div>
    </div>
    <div class="row h-100 g-1"> <!-- Gunakan h-100 dan tambahkan gutter -->
        <!-- Kiri Atas - Visualisasi Supplier -->
        <div class="col-md-6 h-50 d-flex flex-column"> <!-- 50% height -->
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Quality EWS</h5>
                </div>
                <div class="card-body d-flex flex-column overflow-hidden">
                    <div class="row overflow-auto">
                        @foreach($tableQuality as $data)
                        <div class="col-3 mt-3">
                            <a href="{{ route('daily-check-sheet.detail', ['supplier_id' => $data['supplier_id'], 'production_date' => $data['production_date']]) }}" style="text-decoration: none; color: inherit;">
                            <div id="card-statistik-hari-ini" class="card {{
                                $data['production_status'] === 'not-submitted' ? 'bg-not-submitted' :
                                ($data['production_status'] === 'no_production' ? 'bg-not-production' :
                                ($data['production_status'] === 'production' ?
                                    ($data['judgement'] === 'NG' ? 'bg-not-good' :
                                    ($data['judgement'] === 'Good' ? 'bg-good' : 'bg-not-submitted'))
                                : 'bg-not-submitted'))
                            }}">
                                <div id="card-statistik-hari-ini-body" class="card-body p-2">
                                    {{-- <div class="text-muted small">Total</div> --}}
                                    <div class="h6 {{ $data['production_status'] === 'not-submitted' ? 'text-black' : 'text-white' }}">{{ $data['supplier_name'] }}</div>
                                </div>
                            </div>
                            </a>
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-auto pt-2">
                        <div class="d-flex justify-content-center gap-3">
                            <div class="me-3">
                                <span class="badge bg-not-submitted text-not-submitted">|</span> Belum Submit
                            </div>
                            <div class="me-3">
                                <span class="badge bg-not-production text-not-production">|</span> Tidak ada produksi
                            </div>
                            <div class="me-3">
                                <span class="badge bg-good text-good">-</span> Ok
                            </div>
                            <div>
                                <span class="badge bg-not-good text-not-good">-</span> NG
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kanan Atas - Bad News First -->
        <div class="col-md-6 h-50 d-flex flex-column"> <!-- 50% height -->
            <div class="card h-100">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">BNF</h5>
                </div>
                <div class="overflow-auto" style="max-height: 100%;">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr class="bg-danger text-white text-center">
                                    <th width="5%" class="text-white">NO</th>
                                    <th width="25%" class="text-white">SUPPLIER</th>
                                    <th width="25%" class="text-white">PART NAME</th>
                                    <th width="10%" class="text-white">PROBLEM</th>
                                    <th width="20%" class="text-white">DESCRIPTION</th>
                                    <th width="10%" class="text-white">QTY</th>
                                    <th width="10%" class="text-white">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tableBnf as $dataBnf)
                                <tr class="{{ $dataBnf['supplier_name'] == 'NG' ? 'table-danger' : '' }}">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $dataBnf['supplier_name'] }}</td>
                                    <td>{{ $dataBnf['part_name'] }}</td>
                                    <td>{{ $dataBnf['problem'] ?? '-' }}</td>
                                    <td>{{ $dataBnf['description'] ?? '-' }}</td>
                                    <td>{{ $dataBnf['qty'] }}</td>
                                    <td><button type="button" name="close" data-id="{{$dataBnf['id']}}" class="closeBnf btn btn-primary btn-sm">Selesaikan</button></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">No Daily Checksheet Submited</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kiri Bawah - Quality Early Warning -->
        <div class="col-md-6 h-50 d-flex flex-column"> <!-- 50% height -->
            <div class="card h-100">
                <div class="card-header bg-warning text-white text-center">
                    <form method="GET" action="{{ request()->url() }}" id="dateFilterForm">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0 text-white text-center">(% / Pcs)</h4>
                            <input type="date"
                            name="filter_date"
                            id="filterDate"
                            class="form-control w-25"
                            value="{{ request('filter_date', now()->setTimezone('Asia/Jakarta')->format('Y-m-d')) }}"
                            onchange="document.getElementById('dateFilterForm').submit()">
                        </div>
                    </form>
                </div>
                <div class="overflow-auto position-relative" style="max-height: 100%;">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="thead-dark">
                                <tr class="bg-warning text-white text-center">
                                    <th class="text-white">NO</th>
                                    <th class="text-white">SUPPLIER</th>
                                    <th class="text-white">OK RATIO</th>
                                    <th class="text-white">NG RATIO</th>
                                    <th class="text-white">JUDGEMENT</th>
                                    <th class="text-white">PART NAME</th>
                                    <th class="text-white">PROBLEM</th>
                                    <th class="text-white">ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tableQuality as $data)
                                <tr class="{{ $data['judgement'] == 'NG' ? 'table-danger' : ($data['judgement'] == 'Good' ? '' : '') }}">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $data['supplier_name'] }}</td>
                                    <td>{{ $data['ok_ratio'] }}</td>
                                    <td>{{ $data['ng_ratio'] }}</td>
                                    <td>{{ $data['judgement'] }}</td>
                                    <td>{{ $data['judgement'] == 'NG' ? $data['part_name'] : ($data['judgement'] == 'Good' ? '-' : '-')  }}</td>
                                    <td>{{ $data['judgement'] == 'NG' ? $data['problem'] : ($data['judgement'] == 'Good' ? '-' : '-')}}</td>
                                    <td><a href="{{ route('daily-check-sheet.detail', ['supplier_id' => $data['supplier_id'], 'production_date' => $data['production_date']]) }}" class="edit btn btn-warning btn-sm me-2"><i class="fa fa-eye"></i></a></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">No Daily Checksheet Submited</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kanan Bawah - List Problem -->
        <div class="col-md-6 h-50 d-flex flex-column"> <!-- 50% height -->
            <div class="card h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">List Claim</h5>
                </div>
                <div class="overflow-auto" style="max-height: 100%;">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="thead-dark">
                                <tr class="bg-info text-white text-center">
                                    <th class="text-white">NO</th>
                                    <th class="text-white">DATE</th>
                                    <th class="text-white">PART NO</th>
                                    <th class="text-white">PART NAME</th>
                                    <th class="text-white">PROBLEM</th>
                                    <th class="text-white">SUPPLIER</th>
                                    <th class="text-white">CAR</th>
                                    <th class="text-white">A3 REPORT</th>
                                    <th class="text-white">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tableProblem as $dataProblem)
                                <tr class="{{
                                    (!empty($dataProblem['car_upload_at']) &&
                                    empty($dataProblem['report_upload_at']) &&
                                    \Carbon\Carbon::parse($dataProblem['car_upload_at'])->diffInDays(now()) > 3)
                                    ? 'table-danger' : ''
                                }}">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $dataProblem['car_upload_at'] }}</td>
                                    <td>{{ $dataProblem['part_number'] }}</td>
                                    <td>{{ $dataProblem['part_name'] ?? '-' }}</td>
                                    <td>{{ $dataProblem['problem_description'] ?? '-' }}</td>
                                    <td>{{ $dataProblem['supplier_name'] ?? '-' }}</td>
                                    <td>{!! $dataProblem['car'] ?? '-' !!}</td>
                                    <td>{!! $dataProblem['a3_report'] ?? '-' !!}</td>
                                    <td>{!! $dataProblem['action'] ?? '-' !!}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">No Daily Checksheet Submited</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<style>
    #card-statistik-hari-ini {
            min-height: 100px; /* Sesuaikan tinggi minimum sesuai kebutuhan */
            transition: transform 0.3s ease;
        }
        #card-statistik-hari-ini:hover {
            transform: translateY(-5px);
        }
        #card-statistik-hari-ini-body {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
    .table-sticky thead th {
        position: sticky;
        top: 0;
        background: white;
        z-index: 10;
    }

    .card {
        min-height: 300px; /* Minimum height untuk card kosong */
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .card-list-supplier {
        min-height: 50px; /* Minimum height untuk card kosong */
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .overflow-auto {
        scrollbar-width: thin;
    }

    .table-fixed {
        table-layout: fixed;
    }

    .ellipsis {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>

@push('js')
<script>
    // Add this modal handling code
    $(document).ready(function() {
            // Show upload CAR modal
            $('body').on('click', '.upload-car', function() {
                var problemId = $(this).data('id');
                $('#uploadCarModal').find('input[name="problem_id"]').val(problemId);
                $('#uploadCarModal').modal('show');
            });

            // Handle form submission
            $('#uploadCarForm').on('submit', function(e) {
                e.preventDefault();
                var formData = new FormData(this);

                $.ajax({
                    url: "{{ route('problem-list.upload-car') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Success!', response.message, 'success');
                            $('#uploadCarModal').modal('hide');
                            location.reload();
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON.message, 'error');
                    }
                });
            });
        });

    // Refresh page every minute
    setInterval(function() {
        location.reload();
    }, 60000); // 60000 milliseconds = 1 minute

     // selesaikan bnf
     $('body').on('click', '.closeBnf', function() {
                var bnfId = $(this).data('id');
                Swal.fire({
                    title: 'Apakah anda yakin?',
                    text: "Status BNF ini akan berubah menjadi selesai",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#82868',
                    confirmButtonText: 'Ya, selesaikan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "POST",
                            url: "{{ url('bad-news-firsts') }}/close/" + bnfId,
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: {
                                _method: 'POST'
                            },
                            success: function(response) {
                                location.reload();
                                showToast('success', response.message);
                            },
                            error: function(response) {
                                var errorMessage = response.responseJSON
                                    .message;
                                showToast('error',
                                    errorMessage);
                            }
                        });
                    }
                });
            });

            $('body').on('click', '.closeA3Report', function() {
                var a3ReportId = $(this).data('id');
                Swal.fire({
                    title: 'Apakah anda yakin?',
                    text: "Status Problem List ini akan berubah menjadi selesai",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#82868',
                    confirmButtonText: 'Ya, selesaikan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "POST",
                            url: "{{ url('problem-list') }}/close/" + a3ReportId,
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: {
                                _method: 'POST'
                            },
                            success: function(response) {
                                location.reload();
                                showToast('success', response.message);
                            },
                            error: function(response) {
                                var errorMessage = response.responseJSON
                                    .message;
                                showToast('error',
                                    errorMessage);
                            }
                        });
                    }
                });
            });
</script>
@endpush

{{-- Add this modal HTML before the closing body tag --}}
<div class="modal fade" id="uploadCarModal" tabindex="-1" role="dialog" aria-labelledby="uploadCarModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadCarModalLabel">Upload CAR</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="uploadCarForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="problem_id" value="">
                    <div class="form-group">
                        <label for="no_car">No. CAR</label>
                        <input type="text" class="form-control" id="no_car" name="no_car" required>
                    </div>
                    <div class="form-group">
                        <label for="car_file">CAR File</label>
                        <input type="file" class="form-control" id="car_file" name="car_file" accept=".pdf,.doc,.docx,.xls,.xlsx" required>
                        <small class="form-text text-muted">Accepted formats: PDF, DOC, DOCX, XLS, XLSX</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

