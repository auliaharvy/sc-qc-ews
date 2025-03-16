@extends('layouts.administrator.master')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="fw-bold">{{ $title . ' - ' . $production_date}}</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="chart-container" style="position: relative; height: 200px; width: 100%">
                <canvas id="okRatioChart"></canvas>
            </div>
            <div class="table-responsive text-left">
                <table class="table table-bordered dataTable">
                    <thead>
                        <tr>

                                <th>No</th>
                                {{-- @if(auth()->user()->roles->contains('name', 'admin')) --}}
                                {{-- <th>Supplier</th> --}}
                                {{-- @endif --}}
                                {{-- <th>Supplier</th> --}}
                                <th width="200px">Part Number</th>
                                <th width="200px">Part Name</th>
                                <th>Total Produksi</th>
                                <th>Total Good</th>
                                <th>Total NG</th>
                                {{-- @foreach($ngTypes as $ngType)
                                    <th>{{ $ngType }}</th>
                                @endforeach
                                <th>Jam Buat</th> --}}
                                {{-- <th width="100px">Action</th> --}}

                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script type="text/javascript">
        $(function() {
            // ajax table
            var table = $('.dataTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('daily-check-sheet.detail', ['supplier_id' => $supplier_id, 'production_date' => $production_date]) }}",
                columnDefs: [{
                    "targets": "_all",
                    "className": "text-start"
                }],
                columns: [
                    {
                        data: 'id',
                        name: 'id',
                        orderable: true,
                        searchable: false,
                        render: function(data, type, full, meta) {
                            return meta.row + 1;
                        }
                    },
                    // {
                    //     data: 'supplier_name',
                    //     name: 'supplier_name'
                    // },
                    {
                        data: 'part_number',
                        name: 'part_number'
                    },
                    {
                        data: 'part_name',
                        name: 'part_name'
                    },
                    {
                        data: 'total_produced',
                        name: 'total_produced'
                    },
                    {
                        data: 'ng_ratio',
                        name: 'ng_ratio'
                    },
                    {
                        data: 'oke_ratio',
                        name: 'oke_ratio'
                    },
                    // @foreach($ngTypes as $ngType)
                    // {
                    //     data: '{{ $ngType }}',
                    //     name: '{{ $ngType }}'
                    // },
                    // @endforeach
                    // {
                    //     data: 'jam_buat',
                    //     name: 'jam_buat'
                    // },
                ],
            });

        });

        // Chart initialization
        document.addEventListener('DOMContentLoaded', function() {
            // Register plugin
            // Chart.register(ChartAnnotation);
            // Chart.register(ChartDataLabels);
            console.log(JSON.parse("{{ $dailyCheckSheetData }}"));

            // Chart config
            const tableQuality = json_encode($dailyCheckSheetData);
            const chart = document.getElementById('okRatioChart').getContext('2d');
            const suppliers = tableQuality.map(item => item.supplier_name);
            const okRatios = tableQuality.map(item => parseFloat(item.oke_ratio.replace('%', '')));

            new Chart(chart, {
                type: 'bar',
                data: {
                    labels: suppliers.map(supplier => supplier.length > 10 ? supplier.substring(0, 10) + '...' : supplier),
                    datasets: [{
                        // label: 'OK Ratio (%)',
                        data: okRatios,
                        backgroundColor: okRatios.map(ratio => ratio >= 95 ? '#4CAF50' : '#F44336'),
                        borderWidth: 1,
                        datalabels: {
                            color: '#fff',
                            anchor: 'end',
                            align: 'top',
                            formatter: (value) => value + '%'
                        }
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                stepSize: 10,
                                max: 100,
                                min: 0

                            }
                        }]
                    },
                    plugins: {
                    datalabels: {
                        color: '#fff',
                        anchor: 'end',
                        align: 'top',
                        formatter: function(value) {
                            return value + '%';
                        }
                    }
                }
                    // plugins: {
                    //     annotation: {
                    //         annotations: {
                    //             line95: {
                    //                 type: 'line',
                    //                 yMin: 95,
                    //                 yMax: 95,
                    //                 borderColor: '#FFC107',
                    //                 borderWidth: 2,
                    //                 borderDash: [5, 5],
                    //                 label: {
                    //                     content: 'Target 95%',
                    //                     display: true,
                    //                     position: 'end',
                    //                     backgroundColor: '#FFC107',
                    //                     color: '#333',
                    //                     font: {
                    //                         weight: 'bold'
                    //                     }
                    //                 }
                    //             }
                    //         }
                    //     },
                    //     legend: {
                    //         display: false
                    //     }
                    // }
                }
            });
        });
    </script>
@endpush
