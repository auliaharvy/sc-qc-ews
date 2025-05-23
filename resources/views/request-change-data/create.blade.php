@extends('layouts.administrator.master')

@push('css')
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
    <x-form-section title="{{ $title }}">

        <form method="POST" action="{{ route('request-change-data.store') }}" enctype="multipart/form-data" id="checksheet-form">
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
                                            name="total_produced[{{$part->id}}]" value="{{ isset($dailyCheckSheetData[$part->id]) ? $dailyCheckSheetData[$part->id]['total_produced'] : 0 }}" min="0"
                                            oninput="updateValues(this)">
                                        @if(isset($dailyCheckSheetData[$part->id]['id']))
                                            <input type="hidden" name="daily_checksheet_id[{{$part->id}}]" value="{{$dailyCheckSheetData[$part->id]['id']}}">
                                        @endif
                                        @if(isset($dailyCheckSheetData[$part->id]['shift']))
                                            <input type="hidden" name="shift[{{$part->id}}]" value="{{ in_array($dailyCheckSheetData[$part->id]['shift'] ?? '', ['day', 'night']) ? $dailyCheckSheetData[$part->id]['shift'] : 'day' }}"
                                            >
                                        @endif

                                    </td>
                                    <td class="input-cell">
                                        <input type="number" class="form-control form-control-sm ok"
                                            name="ok[{{$part->id}}]" value="{{ isset($dailyCheckSheetData[$part->id]) ? $dailyCheckSheetData[$part->id]['total_ok'] : 0 }}" readonly>
                                    </td>
                                    <td class="input-cell">
                                        <input type="number" class="form-control form-control-sm ng"
                                            name="ng[{{$part->id}}]" value="{{ isset($dailyCheckSheetData[$part->id]) ? $dailyCheckSheetData[$part->id]['total_ng'] : 0 }}"
                                            oninput="updateValues(this)" readonly>
                                    </td>

                                    <!-- Scrollable NG Types -->
                                    <td class="ng-types-container">
                                        <div class="ng-types-wrapper">
                                            @foreach($ngTypes as $ngType)
                                            <div class="ng-type-item">
                                                <label>{{ $ngType->name }}</label>
                                                <input type="number" class="form-control form-control-sm"
                                                    name="ngtype-{{ $ngType->id }}[{{$part->id}}]"
                                                    value="{{ isset($dailyNgTypes[$part->id.'_'.$ngType->id]) ? $dailyNgTypes[$part->id.'_'.$ngType->id] : 0 }}" min="0" oninput="updateNG(this)">
                                            </div>
                                            @endforeach
                                        </div>
                                    </td>

                                    <input type="hidden" name="part_id[{{$part->id}}]" value="{{ $part->id }}">
                                    <input type="hidden" name="supplier_id[{{$part->id}}]" value="{{ auth()->user()->supplier_id }}">
                                    <input type="hidden" name="production_date[{{$part->id}}]" value="{{ $production_date }}">

                                    
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" id="submit-button">Simpan Data</button>
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

            $('#checksheet-form').on('submit', function(e) {
                    const button = $('#submit-button');
                    button.html(`
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Menyimpan Data...
                    `).prop('disabled', true);
                });
        </script>
    </x-form-section>
@endsection
