@extends('layouts.administrator.master')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="fw-bold">{{ $title . ' ' . $supplierName . ' - ' . $production_date}}</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="chart-container" style="position: relative; height: 80vh; width: 100%">
                <canvas id="okRatioChart"></canvas>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="{{ asset('vendor/chart-js/chart.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@1"></script>
    <script type="text/javascript">
        // Chart initialization
        document.addEventListener('DOMContentLoaded', function() {
            // Register plugin

            // Chart config
            const tableQuality = @json($dailyCheckSheetData);
            const chart = document.getElementById('okRatioChart').getContext('2d');
            const parts = tableQuality.map(item => item.part_name);
            const okRatios = tableQuality.map(item => parseFloat(item.oke_ratio.replace('%', '')));


            new Chart(chart, {
                type: 'bar', // Gunakan tipe 'bar'
                data: {
                    labels: parts.map(part => part.length > 35 ? part.substring(0, 35) + '...' : part),
                    datasets: [{
                        label: 'OK Ratio (%)',
                        data: okRatios,
                        backgroundColor: okRatios.map(ratio => ratio > 95 ? '#4CAF50' : '#F44336'),
                        borderWidth: 1,
                    }]
                },
                options: {
                    indexAxis: 'y', // Ini yang membuat grafik horizontal
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            type: 'linear',
                            beginAtZero: true,
                            min: 0,
                            max: 100,
                            ticks: {
                                stepSize: 10
                            }
                        },
                        y: {
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
                                    xMin: 95,
                                    xMax: 95,
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
