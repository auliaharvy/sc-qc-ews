@extends('layouts.monitoring')

@push('css')
    <style>
        #dateFilter {
            max-width: 200px;
            padding: 0.375rem 0.75rem;
            border-radius: 0.25rem;
            border: 1px solid #ced4da;
        }
    </style>
    <style>
        #card-statistik-hari-ini {
            min-height: 50px; /* Sesuaikan tinggi minimum sesuai kebutuhan */
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
        }
        .chart-container {
            min-height: 100px;
            max-height: 600px;
        }
    </style>
@push('css')
    <style>
        /* Existing styles... */

        /* New styles */
        .table-fixed {
            table-layout: fixed;
        }
        .ellipsis {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .show-more {
            cursor: pointer;
            color: #007bff;
            text-decoration: underline;
        }
        .card-body {
            padding: 0.75rem;
        }
        .table td, .table th {
            padding: 0.5rem;
            font-size: 0.9rem;
        }
        .table thead th {
            white-space: nowrap;
        }
    </style>
@endpush

@section('content')
<div class="content-wrapper">
    <div class="row mb-4">
        <!-- Left Side - Information Panel -->
        <div class="col-md-6 h-100"> <!-- Added h-100 to make full height -->
            <div id="cardQuality" class="card h-100"> <!-- Added h-100 to make full height -->
                <div class="card-header bg-primary text-white text-center">
                    <h5 class="text-white">VISUALISASI SUPPLIER </h5>
                    {{-- <div id="realtime-date" class="h6 text-white"></div> --}}
                </div>
                <div class="card-body">
                    <div class="mt-2">
                        <div class="row text-center">
                            @foreach($tableQuality as $data)
                            <div class="col-3 mt-3">
                                <a href="{{ route('daily-check-sheet.detail', ['supplier_id' => $data->supplier_id, 'production_date' => $data->production_date]) }}" style="text-decoration: none; color: inherit;">
                                <div id="card-statistik-hari-ini" class="card {{ $data->judgement == 'NG' ? 'bg-danger' : ($data->judgement == 'Good' ? 'bg-success' : 'bg-warning') }}">
                                    <div id="card-statistik-hari-ini-body" class="card-body p-2">
                                        <div class="h6 text-white">{{ $data->supplier_name }}</div>
                                    </div>
                                </div>
                                </a>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="d-flex justify-content-center">
                            <div class="me-3">
                                <span class="badge bg-warning text-warning">-</span> Belum Submit
                            </div>
                            <div class="me-3">
                                <span class="badge bg-success text-success">-</span> Ok
                            </div>
                            <div>
                                <span class="badge bg-danger text-danger">-</span> NG
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-header bg-warning text-white text-center">
                    <form method="GET" action="{{ request()->url() }}" id="dateFilterForm">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0 text-white text-center">Quality Early Warning System</h4>
                            <input type="date"
                                name="filter_date"
                                id="filterDate"
                                class="form-control w-25"
                                value="{{ request('filter_date', now()->setTimezone('Asia/Jakarta')->format('Y-m-d')) }}"
                                onchange="document.getElementById('dateFilterForm').submit()">
                        </div>
                    </form>
                </div>
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
                            <tr class="{{ $data->judgement == 'NG' ? 'table-danger' : '' }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $data->supplier_name }}</td>
                                <td>{{ $data->oke_ratio }}</td>
                                <td>{{ $data->ng_ratio }}</td>
                                <td>{{ $data->judgement }}</td>
                                <td>{{ $data->part_name }}</td>
                                <td>{{ $data->problem ?? '-' }}</td>
                                <td><a href="{{ route('daily-check-sheet.detail', ['supplier_id' => $data->supplier_id, 'production_date' => $data->production_date]) }}" class="edit btn btn-warning btn-sm me-2"><i class="fa fa-eye"></i></a></td>
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

        <!-- Right Side -->
        <div class="col-md-6 h-100"> <!-- Added h-100 to make full height -->
            <div id="cardQuality" class="card h-100"> <!-- Added h-100 to make full height -->
                <div class="card-header bg-danger text-white text-center">
                    <h4 class="mb-0 text-white text-center">BAD NEWS FIRST</h4>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr class="bg-danger text-white text-center">
                                <th class="text-white">NO</th>
                                <th class="text-white">SUPPLIER</th>
                                <th class="text-white">PART NAME</th>
                                <th class="text-white">PROBLEM</th>
                                <th width="400px" class="text-white">DESCRIPTION</th>
                                <th class="text-white">QTY</th>
                                <th class="text-white">Action</th>
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

                <div class="card-header bg-info text-white text-center">
                    <h4 class="mb-0 text-white text-center">LIST PROBLEM & FOLLOW UP CHECK RECEIVING INSPECTION</h4>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
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
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $dataProblem['formated_date'] }}</td>
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
@endsection

@push('js')
    <script src="{{ asset('vendor/chart-js/chart.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@1"></script>
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

        // Real-time Clock
        function updateDateTime() {
            const now = new Date(document.getElementById('filterDate').value);
            const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit' };

            document.getElementById('realtime-date').innerHTML = now.toLocaleDateString('id-ID', dateOptions);
            // document.getElementById('realtime-clock').innerHTML = now.toLocaleTimeString('id-ID', timeOptions);
        }
        setInterval(updateDateTime, 1000);
        updateDateTime();

        // // Initialize visibility
        // const sections = ['cardQuality', 'tableQuality', 'tableBnf', 'tableProblem'];
        // let currentIndex = 0;

        // // Function to show only one section
        // function showSection(index) {
        //     sections.forEach((section, i) => {
        //         const element = document.getElementById(section);
        //         if (element) {
        //             element.style.display = i === index ? 'block' : 'none';
        //         }
        //     });

        //     // Update navigation buttons
        //     document.querySelectorAll('.section-nav').forEach(btn => {
        //         btn.classList.remove('active');
        //         if (btn.dataset.section === sections[index]) {
        //             btn.classList.add('active');
        //         }
        //     });
        // }

        // // Show first section immediately
        // showSection(currentIndex);

        // // Rotate sections every 5 seconds
        // const rotationInterval = setInterval(() => {
        //     currentIndex = (currentIndex + 1) % sections.length;
        //     showSection(currentIndex);
        // }, 10000);

        // // Manual navigation
        // document.querySelectorAll('.section-nav').forEach(btn => {
        //     btn.addEventListener('click', function() {
        //         const section = this.dataset.section;
        //         currentIndex = sections.indexOf(section);
        //         showSection(currentIndex);

        //         // Reset the auto-rotation timer
        //         clearInterval(rotationInterval);
        //         setInterval(() => {
        //             currentIndex = (currentIndex + 1) % sections.length;
        //             showSection(currentIndex);
        //         }, 5000);
        //     });
        // });

        // Refresh page every minute
        setInterval(function() {
            location.reload();
        }, 60000); // 60000 milliseconds = 1 minute


        // Chart initialization
        document.addEventListener('DOMContentLoaded', function() {
            // Register plugin
            // Chart.register(ChartAnnotation);
            // Chart.register(ChartDataLabels);

            // Chart config
            const tableQuality = @json($tableQuality);
            const chart = document.getElementById('okRatioChart').getContext('2d');
            const suppliers = tableQuality.map(item => item.supplier_name);
            const okRatios = tableQuality.map(item => parseFloat(item.oke_ratio.replace('%', '')));

            new Chart(chart, {
                type: 'bar', // Gunakan tipe 'bar'
                data: {
                    labels: suppliers.map(supplier => supplier.length > 10 ? supplier.substring(0, 10) + '...' : supplier),
                    datasets: [{
                        label: 'OK Ratio (%)',
                        data: okRatios,
                        backgroundColor: okRatios.map(ratio => ratio >= 95 ? '#4CAF50' : '#F44336'),
                        borderWidth: 1,
                    }]
                },
                options: {
                    indexAxis: 'x', // Ini yang membuat grafik horizontal
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            type: 'linear',
                            beginAtZero: true,
                            min: 0,
                            max: 100,
                            ticks: {
                                stepSize: 10
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Part Name'
                            }
                        }
                    },
                    plugins: {
                        datalabels: {
                            display: true,
                            color: '#fff',
                            anchor: 'end',
                            align: 'start',
                            formatter: (value) => value + '%'
                        },
                        annotation: {
                            annotations: {
                                line95: {
                                    type: 'line',
                                    yMin: 95,
                                    yMax: 95,
                                    borderColor: '#F44336',
                                    borderWidth: 2,
                                    borderDash: [5, 5],
                                    label: {
                                        content: 'Target 95%',
                                        display: true,
                                        position: 'end',
                                        backgroundColor: '#F44336',
                                        color: '#333',
                                        font: {
                                            weight: 'bold'
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            });
        });

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
