@extends('layouts.administrator.master')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="fw-bold">{{ $title }}</h4>

                @can('create parts')
                    <a href="{{ route('bad-news-firsts.create') }}" class="btn btn-primary btn-sm" id="createParts">
                        <i class="ti-plus"></i>
                        Tambah Data
                    </a>
                @endcan
            </div>
        </div>
    </div>
    @if(auth()->user()->roles->contains('name', 'admin'))

    <div class="row justify-content-end">
    <form class="col-md-8 d-flex gap-2 mt-3" style="min-width:220px;" id="filterSupplierForm">
        <p class="fw-bold text-xs align-self-center mb-0">Filter:</p>
        <select class="form-select form-select-sm" id="filterSupplier" name="supplier_id">
          <option value="">-- Semua Supplier --</option>
            @foreach(getSupplier() as $supplier)
                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
            @endforeach
        </select>
        <select class="form-select form-select-sm" id="filterPart" name="part_id">
          <option value="">-- Semua Part --</option>
            @foreach(getPart() as $part)
                <option value="{{ $part->id }}">{{ $part->part_number }} - {{ $part->part_name }}</option>
            @endforeach
        </select>
        <select class="form-select form-select-sm" id="filterProblem" name="problem">
          <option value="">-- Semua Problem --</option>
            @foreach(getNgTypes() as $ngType)
                <option value="{{ $ngType->name }}"> {{ $ngType->name }}</option>
            @endforeach
        </select>
        <select class="form-select form-select-sm" id="filterStatus" name="status">
          <option value="">-- Semua Status --</option>
          <option value="open">Belum Selesai</option>
          <option value="resolved">Selesai</option>
        </select>
        <button type="button" class="btn btn-sm ms-2 d-flex align-items-center justify-content-center" id="resetFilterBtn" style="width:32px;height:32px;padding:0;">reset</button>
    </form>
    </div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive text-left">
                <table class="table table-bordered dataTable">
                    <thead>
                        <tr>
                            <th>Supplier</th>
                            <th>Part Name</th>
                            <th>Problem</th>
                            <th width="300px">Description</th>
                            <th>Qty</th>
                            <th >Status</th>
                            <th width="100px">Action</th>
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
                order: [
                    [5, 'asc']
                ],
                ajax: {
                    url: "{{ route('bad-news-firsts.index') }}",
                    // data: function(d) {
                    //     d.supplier_id = $('#filterSupplier').val();
                    //     d.part_id = $('#filterPart').val();
                    //     d.status = $('#filterStatus').val();
                    //     d.problem = $('#filterProblem').val();
                    // }
                },
                columnDefs: [{
                    "targets": "_all",
                    "className": "text-start"
                }],
                columns: [
                    // {
                    //     data: 'id',
                    //     name: 'id',
                    //     orderable: true,
                    //     searchable: false,
                    //     render: function(data, type, full, meta) {
                    //         return meta.row + 1;
                    //     }
                    // },
                    {
                        data: 'supplier_name',
                        name: 'supplier_name',
                        // orderable: true,
                        searchable: true
                    },
                    {
                        data: 'part_name',
                        name: 'part_name',
                        // orderable: true,
                        searchable: true
                    },
                    {
                        data: 'problem',
                        name: 'problem',
                        // orderable: false,
                        searchable: false
                    },
                    {
                        data: 'description',
                        name: 'description',
                        // orderable: false,
                        searchable: false
                    },
                    {
                        data: 'qty',
                        name: 'qty',
                        // orderable: false,
                        searchable: false
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: true,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        // orderable: false,
                        searchable: false
                    }
                ]
            });

            // Filter event handlers
            $('#filterSupplier, #filterPart, #filterStatus').on('change', function() {
                table.draw();
            });
            $('#filterProblem').on('change', function() {
                table.draw();
            });
            $('#resetFilterBtn').on('click', function() {
                $('#filterSupplier').val('');
                $('#filterPart').val('');
                $('#filterStatus').val('');
                $('#filterProblem').val('');
                table.draw();
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
                                table.draw();
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



        });
    </script>
@endpush
