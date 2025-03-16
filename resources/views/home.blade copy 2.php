@extends('layouts.administrator.master')

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
@endpush

@section('content')
    <div class="content-wrapper">
        <div class="row same-height">

                <div class="card">
                    <div class="card-header bg-primary text-white text-center">
                        <h6 class="text-white">Visualisasi Supplier </h6>
                        <div id="realtime-date" class="h6 text-white"></div>
                    </div>
                    <div class="card-body">
                        <div class="card-body">
                            {{-- <div class="row">
                                <h6><i class="fas fa-chart-line"></i> Top 3 of The Month</h6>
                                <div class="col-md-6">
                                    <h6 class="card-header bg-primary text-white text-center">BEST</h6>
                                    <ol class="list-group">
                                        <li class="list-group-item text-center">No Data</li>
                                    </ol>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="card-header bg-danger text-white text-center">WORST</h6>
                                    <ol class="list-group">
                                        <li class="list-group-item list-group-item-danger text-center">No Data</li>
                                    </ol>
                                </div>
                            </div> --}}

                            <div class="mt-2">
                                {{-- <h6><i class="fas fa-chart-line"></i> Visualisasi Supplier </h6> --}}
                                <div class="row text-center ">
                                    @foreach($tableQuality as $data)
                                    <div class="col-4 mt-4">
                                        <a href="{{ route('daily-check-sheet.detail', ['supplier_id' => $data->supplier_id, 'production_date' => $data->production_date]) }}" style="text-decoration: none; color: inherit;">
                                        <div id="card-statistik-hari-ini" class="card {{ $data->judgement == 'NG' ? 'bg-danger' : ($data->judgement == 'Good' ? 'bg-success' : 'bg-primary') }}">
                                            <div id="card-statistik-hari-ini-body" class="card-body p-2">
                                                {{-- <div class="text-muted small">Total</div> --}}
                                                <div class="h6 text-white">{{ $data->supplier_name }}</div>
                                            </div>
                                        </div>
                                        </a>
                                    </div>
                                    @endforeach
                                </div>

                                {{-- <div class="row text-center mt-4">
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
                                </div> --}}
                            </div>

                            {{-- <div class="mt-4">
                                <h6><i class="fas fa-chart-line"></i> Statistik Hari Ini</h6>
                                <div class="mt-4 text-center">
                                    <div id="realtime-date" class="h6 text-muted"></div>
                                    <div id="realtime-clock" class="h2 text-primary font-weight-bold"></div>
                                </div>
                                <div class="row text-center mt-4">
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
                            </div> --}}

                            {{-- <canvas id="myChart" width="1388"></canvas> --}}
                        </div>
                        <div class="card-body">
                            <div class="card-body">

                            </div>
                        </div>
                    </div>
                </div>
        </div>
        <div class="row same-height">
            <div class="card">
                <div class="card-body">
                    <div class="card-body">
                        <div class="chart-container" style="position: relative; height: 300px; width: 100%">
                            <canvas id="okRatioChart"></canvas>
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
                    <div class="table-responsive m">
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
                                    <td>{{ $data->supplier_name }}</td> <!-- Assuming supplier name is stored in supplier_name -->
                                    <td>{{ $data->oke_ratio }}</td> <!-- Assuming the correct property name is oke_ratio -->
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
                                    <th class="text-white">DESCRIPTION</th>
                                    <th class="text-white">QTY</th>
                                    <th class="text-white">Action</th>
                                </tr>
                            </thead>
                            @forelse($tableBnf as $dataBnf)
                                <tr class="{{ $dataBnf['supplier_name'] == 'NG' ? 'table-danger' : '' }}">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $dataBnf['supplier_name'] }}</td>
                                    <td>{{ $dataBnf['part_name'] }}</td>
                                    <td>{{ $dataBnf['problem'] ?? '-' }}</td>
                                    <td>{{ $dataBnf['description'] ?? '-' }}</td>
                                    <td>{{ $dataBnf['qty'] }}</td>
                                    <td><a href="{{ route('daily-check-sheet.detail', ['supplier_id' => $data->supplier_id, 'production_date' => $data->production_date]) }}" class="edit btn btn-warning btn-sm me-2"><i class="fa fa-eye"></i></a></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">No Daily Checksheet Submited</td>
                                </tr>
                                @endforelse
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <div class="content-wrapper">
        <div class="row same-height">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-info text-white text-center">
                        <h4 class="mb-0 text-white text-center">LIST PROBLEM & FOLLOW UP CHECK RECEIVING INSPECTION</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>NO</th>
                                        <th>DATE</th>
                                        <th>PART NO</th>
                                        <th>PART NAME</th>
                                        <th>PROBLEM</th>
                                        <th>SUPPLIER</th>
                                        <th>CAR</th>
                                        <th>A3 REPORT</th>
                                        <th>FINDING LOC</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="9" class="text-center py-4">No List Problem Data</td>
                                    </tr>
                                    {{-- @foreach($problems as $problem)
                                    <tr>
                                        <td>{{ $problem['no'] }}</td>
                                        <td>{{ $problem['date'] }}</td>
                                        <td>{{ $problem['part_no'] }}</td>
                                        <td>{{ $problem['part_name'] }}</td>
                                        <td>{{ $problem['problem'] }}</td>
                                        <td>{{ $problem['supplier'] }}</td>
                                        <td>{{ $problem['car'] }}</td>
                                        <td>{{ $problem['a3_report'] }}</td>
                                        <td>{{ $problem['finding_loc'] }}</td>
                                    </tr>
                                    @endforeach --}}
                                </tbody>
                            </table>
                        </div>
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
            // new Chart(chart, {
            //     type: 'bar',
            //     data: {
            //         labels: suppliers.map(supplier => supplier.length > 10 ? supplier.substring(0, 10) + '...' : supplier),
            //         datasets: [{
            //             // label: 'OK Ratio (%)',
            //             data: okRatios,
            //             backgroundColor: okRatios.map(ratio => ratio >= 95 ? '#4CAF50' : '#F44336'),
            //             borderWidth: 1,
            //             datalabels: {
            //                 color: '#fff',
            //                 anchor: 'end',
            //                 align: 'top',
            //                 formatter: (value) => value + '%'
            //             }
            //         }]
            //     },
            //     options: {
            //         responsive: true,
            //         maintainAspectRatio: false,
            //         scales: {
            //             yAxes: [{
            //                 ticks: {
            //                     beginAtZero: true,
            //                     stepSize: 10,
            //                     max: 100,
            //                     min: 0

            //                 }
            //             }]
            //         },
            //         plugins: {
            //         datalabels: {
            //             color: '#fff',
            //             anchor: 'end',
            //             align: 'top',
            //             formatter: function(value) {
            //                 return value + '%';
            //             }
            //         }
            //     }
            //         // plugins: {
            //         //     annotation: {
            //         //         annotations: {
            //         //             line95: {
            //         //                 type: 'line',
            //         //                 yMin: 95,
            //         //                 yMax: 95,
            //         //                 borderColor: '#FFC107',
            //         //                 borderWidth: 2,
            //         //                 borderDash: [5, 5],
            //         //                 label: {
            //         //                     content: 'Target 95%',
            //         //                     display: true,
            //         //                     position: 'end',
            //         //                     backgroundColor: '#FFC107',
            //         //                     color: '#333',
            //         //                     font: {
            //         //                         weight: 'bold'
            //         //                     }
            //         //                 }
            //         //             }
            //         //         }
            //         //     },
            //         //     legend: {
            //         //         display: false
            //         //     }
            //         // }
            //     }
            // });
        });

    </script>
@endpush
