@extends('layouts.administrator.master')

@push('css')
    <link rel="stylesheet" href="{{ asset('vendor/chart.js/Chart.min.css') }}">
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
        @media (max-width: 768px) {
        .table-responsive {
            font-size: 14px;
        }

        .form-control-sm {
            padding: 0.25rem 0.5rem;
            height: calc(1.5em + 0.5rem);
        }

        table th,
        table td {
            white-space: nowrap; /* Prevent line breaks in headers */
        }
        }
    </style>
    <style>
        /* Styling khusus untuk tabel */
        .compact-table {
            max-height: 70vh;
            border: 1px solid #dee2e6;
        }

        .sticky-header {
            position: sticky;
            top: 0;
            background: white;
            z-index: 100;
        }

        .ng-types-header {
            min-width: 800px; /* Sesuaikan dengan kebutuhan */
            position: relative;
        }

        .ng-types-container {
            width: 800px; /* Sesuaikan dengan kebutuhan */
            max-width: 100vw;
            overflow-x: auto;
            padding: 0;
        }

        .ng-types-wrapper {
            display: flex;
            min-width: fit-content;
        }

        .ng-type-item {
            flex: 0 0 120px;
            padding: 0 5px;
            text-align: center;
        }

        .ng-type-item label {
            font-size: 0.8rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: block;
            margin-bottom: 2px;
        }

        .input-cell {
            min-width: 100px;
            vertical-align: middle;
        }
        .identity-part {
            min-width: 250px;
            vertical-align: middle;
        }

        .scroll-hint {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 0.8rem;
            color: #666;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .ng-type-item {
                flex: 0 0 100px;
            }

            .ng-type-item input {
                width: 80%;
                margin: 0 auto;
            }

            .scroll-hint {
                display: none;
            }
        }
    </style>
@endpush

@section('content')
    <div class="content-wrapper">
        <div class="row mb-4">
            <!-- Left Side - Information Panel -->
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Informasi Real-time</h5>
                    </div>
                    <div class="card-body">
                        <!-- Tanggal dan Waktu -->
                        <div class="mb-4 text-center">
                            <div id="realtime-date" class="h6 text-muted"></div>
                            <div id="realtime-clock" class="h2 text-primary font-weight-bold"></div>
                        </div>

                        <!-- Informasi User -->
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-left align-items-center">
                                Supplier :
                                <span class="badge badge-primary badge-pill text-primary">{{ $supplier->name }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-left align-items-center">
                                User Login :
                                <span class="badge badge-info badge-pill text-primary">{{ auth()->user()->name }}</span>
                            </li>
                        </ul>


                        <!-- Statistik Hari Ini -->
                        <div class="mt-4">
                            <h6><i class="fas fa-chart-line"></i> Statistik Hari Ini</h6>
                            <div class="row text-center mt-3">
                                <div class="col-4">
                                    <div id="card-statistik-hari-ini" class="card">
                                        <div id="card-statistik-hari-ini-body" class="card-body p-2">
                                            <div class="text-muted small">Total</div>
                                            <div class="h5 text-primary">{{ $todayStats['total'] ?? 0 }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div id="card-statistik-hari-ini" class="card">
                                        <div id="card-statistik-hari-ini-body" class="card-body p-2">
                                            <div class="text-muted small">OK</div>
                                            <div class="h5 text-success">{{ $todayStats['ok'] ?? 0 }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div id="card-statistik-hari-ini" class="card">
                                        <div id="card-statistik-hari-ini-body" class="card-body p-2">
                                            <div class="text-muted small">NG</div>
                                            <div class="h5 text-danger">{{ $todayStats['ng'] ?? 0 }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row text-center mt-4">
                                <!-- Good Ratio -->
                                <div class="col-6">
                                    <div class="card h-100 bg-success text-white">
                                        <div class="card-body p-2 d-flex flex-column justify-content-center align-items-center">
                                            <div class="small mb-1">Good Ratio</div>
                                            <div class="h4">
                                                @if($todayStats['ok'])
                                                    {{ number_format(($todayStats['ok']/$todayStats['total'])*100, 2) ?? 0 }}%
                                                @else
                                                    0%
                                                @endif

                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- NG Ratio -->
                                <div class="col-6">
                                    <div class="card h-100 bg-danger text-white">
                                        <div class="card-body p-2 d-flex flex-column justify-content-center align-items-center">
                                            <div class="small mb-1">NG Ratio</div>
                                            <div class="h4">
                                                @if($todayStats['ok'])
                                                    {{ number_format(($todayStats['ng']/$todayStats['total'])*100, 2) ?? 0 }}%
                                                @else
                                                    0%
                                                @endif

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Monthly Chart -->
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header">
                        <h6 class="card-title">Grafik Produksi Hari Ini <div id="realtime-date" class="h6 text-muted"></div></h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive m">
                            <table class="table table-bordered">
                                <thead class="thead-dark">
                                    <tr class="bg-warning text-white text-center">
                                        <th class="text-white">NO</th>
                                        <th class="text-white">PART NAME</th>
                                        <th class="text-white">OK RATIO</th>
                                        <th class="text-white">NG RATIO</th>
                                        <th class="text-white">JUDGEMENT</th>
                                        <th class="text-white">PROBLEM</th>
                                        {{-- <th class="text-white">ACTION</th> --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data as $item)
                                    <tr class="{{ $item['ng_ratio_number'] >= 5 ? 'table-danger' : '' }}">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item['part_name'] }}</td>
                                        <td>{{ $item['oke_ratio'] }}</td>
                                        <td>{{ $item['ng_ratio'] }}</td>
                                        <td>
                                            @if($item['ng_ratio_number'] >= 5)
                                                <span class="badge bg-danger">NG</span>
                                            @else
                                                <span class="badge bg-success">Good</span>
                                            @endif
                                        </td>
                                        <td>{{ $item['problem'] ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No data available</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                        {{-- <div class="chart-container" style="position: relative; height: 500px;width: 100%">
                            <canvas id="okRatioChart"></canvas>
                        </div> --}}
                    </div>
                </div>
            </div>
        </div>

        <x-form-section title="{{ $title }}">
            <form method="POST" action="{{ route('daily-check-sheet.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <div class="form-group">
                        <div class="table-responsive compact-table">
                            <table class="table table-bordered table-striped">
                                <thead class="sticky-header">
                                    <tr>
                                        <th>Part Number</th>
                                        <th>Part</th>
                                        <th>Total</th>
                                        <th>OK</th>
                                        <th>NG</th>
                                        <th class="ng-types-header">Jenis NG <span class="scroll-hint">← Scroll →</span></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($parts as $part)
                                    <tr>
                                        <!-- Fixed Columns -->
                                        <td class="identity-part"> {{ $part->part_number }}</td>
                                        <td class="identity-part">{{ $part->part_name }}</td>
                                        <td class="input-cell">
                                            <input type="number" class="form-control form-control-sm total-produced"
                                                name="total_produced[{{$part->id}}]" value="0"
                                                oninput="updateValues(this)">
                                        </td>
                                        <td class="input-cell">
                                            <input type="number" class="form-control form-control-sm ok"
                                                name="ok[{{$part->id}}]" value="0" readonly>
                                        </td>
                                        <td class="input-cell">
                                            <input type="number" class="form-control form-control-sm ng"
                                                name="ng[{{$part->id}}]" value="0"
                                                oninput="updateValues(this)" readonly>
                                        </td>

                                        <!-- Scrollable NG Types -->
                                        <td class="ng-types-container">
                                            <div class="ng-types-wrapper">
                                                @foreach($ngTypes as $ngType)
                                                <div class="ng-type-item">
                                                    <label>{{ $ngType }}</label>
                                                    <input type="number" class="form-control form-control-sm"
                                                        name="ngtype-{{ $ngType }}[{{$part->id}}]"
                                                        value="0" oninput="updateNG(this)">
                                                </div>
                                                @endforeach
                                            </div>
                                        </td>

                                        <input type="hidden" name="part_id[{{$part->id}}]" value="{{ $part->id }}">
                                        <input type="hidden" name="supplier_id[{{$part->id}}]" value="{{ auth()->user()->supplier_id }}">
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Simpan Data</button>
            </form>


            <script>
                function updateValues(input) {
                    const row = input.closest('tr');
                    const totalProduced = parseInt(row.querySelector('.total-produced').value) || 0;
                    const ng = parseInt(row.querySelector('.ng').value) || 0;
                    const ok = totalProduced - ng;

                    row.querySelector('.ng').value = ng; // Update NG value
                    row.querySelector('.ok').value = ok; // Update OK value
                }

                function updateNG(input) {
                    const row = input.closest('tr');
                    const ngInputs = row.querySelectorAll('input[name^="ngtype-"]');
                    let totalNG = 0;

                    ngInputs.forEach(ngInput => {
                        totalNG += parseInt(ngInput.value) || 0;
                    });

                    row.querySelector('.ng').value = totalNG; // Update total NG
                    updateValues(row.querySelector('.total-produced')); // Update OK value
                }
            </script>
        </x-form-section>
    </div>
@endsection

@push('js')
    <script src="{{ asset('vendor/chart-js/chart.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@1"></script>
    <script>
        document.querySelectorAll('.mobile-ng-toggle').forEach(button => {
            button.addEventListener('click', function() {
                const row = this.closest('tr');
                row.querySelector('.ng-types-container').classList.toggle('expanded');
            });
        });

        // Real-time Clock
        function updateDateTime() {
            const now = new Date();
            const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit' };

            document.getElementById('realtime-date').innerHTML = now.toLocaleDateString('id-ID', dateOptions);
            document.getElementById('realtime-clock').innerHTML = now.toLocaleTimeString('id-ID', timeOptions);
        }
        setInterval(updateDateTime, 1000);
        updateDateTime();

        document.addEventListener('DOMContentLoaded', function() {
            // Register plugin

            // Chart config
            const tableQuality = @json($data);
            const chart = document.getElementById('okRatioChart').getContext('2d');
            const parts = tableQuality.map(item => item.part_name);
            const okRatios = tableQuality.map(item => parseFloat(item.oke_ratio.replace('%', '')));


            new Chart(chart, {
                type: 'bar', // Gunakan tipe 'bar'
                data: {
                    labels: parts.map(part => part.length > 20 ? part.substring(0, 20) + '...' : part),
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
    </script>
@endpush
