@extends('layouts.monitoring')

@section('content')
<div class="content-wrapper" style="height: calc(100vh - 60px);"> <!-- Adjust height sesuai kebutuhan -->
    <div class="row">
        <div class="col-12 mb-3">
            <a href="{{ route('home') }}" class="link">
                <div class="d-flex align-items-center justify-content-center">
                    <img src="{{ asset('assets/images/sc-logo.png') }}" alt="Logo" height="50" class="me-3">
                    <h1 class="mb-0">Early Warning System</h1>
                </div>
            </a>
        </div>
    </div>
    <div class="row h-100 g-3"> <!-- Gunakan h-100 dan tambahkan gutter -->
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
                <div class="card-header bg-warning text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">(% / Pcs)</h5>
                    <input type="date" id="filterDate" class="form-control w-auto"
                           value="{{ now()->format('Y-m-d') }}">
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
                            <div class="table-footer">
                                <button class="btn btn-sm btn-link show-more">Show More</button>
                            </div>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kanan Bawah - List Problem -->
        <div class="col-md-6 h-50 d-flex flex-column"> <!-- 50% height -->
            <div class="card h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Problem Follow Up</h5>
                </div>
                <div class="overflow-auto" style="max-height:  100%;">
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
</div>

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

<script>
// Script untuk show more button
document.querySelectorAll('.show-more').forEach(button => {
    button.addEventListener('click', function() {
        const tableBody = this.closest('.card-body');
        tableBody.style.maxHeight = 'none';
        this.style.display = 'none';
    });
});

// Script untuk fixed header
window.addEventListener('DOMContentLoaded', () => {
    const tables = document.querySelectorAll('.table-sticky');
    tables.forEach(table => {
        const header = table.querySelector('thead');
        if(header) {
            header.style.top = '-' + table.offsetTop + 'px';
        }
    });
});
</script>
@endsection
