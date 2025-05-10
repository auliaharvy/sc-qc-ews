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
        <form method="POST" action="{{ route('request-change-data.update') }}" enctype="multipart/form-data" id="request-change-data">
            @csrf
            @method('PUT')
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
                                        <input type="number" class="form-control form-control-sm total-produced-request"
                                            name="total_produced_request[{{$part->id}}]" value="{{ isset($requestChangeData[$part->id]) ? $requestChangeData[$part->id]['total_produced'] : 0 }}" min="0"
                                            oninput="updateValues(this)" readonly>
                                        @if(isset($requestChangeData[$part->id]['daily_checksheet_id']))
                                            <input type="hidden" name="daily_checksheet_id_request[{{$part->id}}]" value="{{$requestChangeData[$part->id]['daily_checksheet_id']}}">
                                        @endif
                                        @if(isset($requestChangeData[$part->id]['id']))
                                            <input type="hidden" name="id_request[{{$part->id}}]" value="{{$requestChangeData[$part->id]['id']}}">
                                        @endif

                                    </td>
                                    <td class="input-cell">
                                        <input type="number" class="form-control form-control-sm ok_request"
                                            name="ok_request[{{$part->id}}]" value="{{ isset($requestChangeData[$part->id]) ? $requestChangeData[$part->id]['total_ok'] : 0 }}" readonly>
                                    </td>
                                    <td class="input-cell">
                                        <input type="number" class="form-control form-control-sm ng_request"
                                            name="ng_request[{{$part->id}}]" value="{{ isset($requestChangeData[$part->id]) ? $requestChangeData[$part->id]['total_ng'] : 0 }}"
                                            oninput="updateValues(this)" readonly>
                                    </td>

                                    <!-- Scrollable NG Types -->
                                    <td class="ng-types-container">
                                        <div class="ng-types-wrapper">
                                            @foreach($ngTypes as $ngType)
                                            <div class="ng-type-item">
                                                <label>{{ $ngType->name }}</label>
                                                <input type="number" class="form-control form-control-sm"
                                                    name="ngtype_request-{{ $ngType->id }}[{{$part->id}}]"
                                                    value="{{ isset($requestChangeNgType[$part->id.'_'.$ngType->id]) ? $requestChangeNgType[$part->id.'_'.$ngType->id] : 0 }}" min="0" oninput="updateNG(this)" readonly>
                                            </div>
                                            @endforeach
                                        </div>
                                    </td>

                                    <input type="hidden" name="part_id[{{$part->id}}]" value="{{ $part->id }}">
                                    <input type="hidden" name="supplier_id_request[{{$part->id}}]" value="{{$supplier_id}}">
                                    <input type="hidden" name="production_date_request[{{$part->id}}]" value="{{ $production_date }}">

                                    
                                </tr>
                                @endforeach
                            </tbody>

                        </table>
                    </div>
                </div>
            </div>

            <p class="fw-bold text-xl align-self-center mt-4">Previous Daily CheckSheet Data :</p>

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
                                            oninput="updateValues(this)" readonly>

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
                                                    value="{{ isset($dailyNgTypes[$part->id.'_'.$ngType->id]) ? $dailyNgTypes[$part->id.'_'.$ngType->id] : 0 }}" min="0" oninput="updateNG(this)" readonly>
                                            </div>
                                            @endforeach
                                        </div>
                                    </td>

    

                                    
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @php
                $firstData = reset($requestChangeData);
            @endphp


            @if(auth()->user()->roles->contains('name', 'admin') && $firstData['status'] !== 'reject')

            <div class="d-flex gap-2 mt-2">
            <button type="button" class="btn btn-danger btn-md" id="reject-button" name="action_type" value="reject">Reject Changes</button>
            <button type="submit" class="btn btn-primary" id="submit-button" name="action_type" value="accept">Accept Changes</button>

            </div>
            @endif
        </form>

        <script>

            function updateValues(input) {
                const row = input.closest('tr');
                const totalProduced = parseInt(row.querySelector('.total-produced-request').value) || 0;
                const ng = parseInt(row.querySelector('.ng_request').value) || 0;
                const ok = totalProduced - ng;

                row.querySelector('.ng_request').value = ng; // Update NG value
                row.querySelector('.ok_request').value = ok; // Update OK value
            }

            function updateNG(input) {
                const row = input.closest('tr');
                const ngInputs = row.querySelectorAll('input[name^="ngtype-"]');
                let totalNG = 0;

                ngInputs.forEach(ngInput => {
                    totalNG += parseInt(ngInput.value) || 0;
                });

                row.querySelector('.ng_request').value = totalNG; // Update total NG
                updateValues(row.querySelector('.total-produced-request')); // Update OK value
            }

            $('#request-change-data').on('submit', function(e) {
                    const button = $('#submit-button');
                    button.html(`
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Menyimpan Data...
                    `).prop('disabled', true);
                });

    

                $(function() {
                    $('#reject-button').on('click', function(e) {
                e.preventDefault();
                var ids = [];
                $("input[name^='id_request']").each(function() {
                    ids.push($(this).val());
                });
                Swal.fire({
                    title: 'Apakah anda yakin?',
                    text: "Menolak pengajuan data ini?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#82868',
                    confirmButtonText: 'Ya, reject!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "POST",
                            url: "{{ route('request-change-data.reject') }}",
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: {
                                request_change_data_id: ids
                            },
                            success: function(response) {
                                Swal.fire('Berhasil', response.message, 'success').then(() => {
                                    window.location.href = "{{ route('request-change-data') }}";
                                });
                            },
                            error: function(xhr) {
                                var errorMessage = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Terjadi kesalahan.';
                                Swal.fire('Error', errorMessage, 'error');
                            }
                        });
                    }
                });
            });
        });
        </script>

    </x-form-section>
@endsection


