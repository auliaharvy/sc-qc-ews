@extends('layouts.administrator.master')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="fw-bold">{{ $title }}</h4>

                @can('create inspeksi')
                @if(auth()->user()->roles->contains('name', 'Admin Supplier'))

                    <a href="{{ route('daily-check-sheet.create') }}" class="btn btn-primary btn-sm" id="createSuppliers">
                        <i class="ti-plus"></i>
                        Tambah Data
                    </a>
                @endif
                @endcan
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive text-left">
                <table class="table table-bordered dataTable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            @if(auth()->user()->roles->contains('name', 'admin'))
                                <th>Supplier</th>
                            @endif
                            <th>Total Produksi</th>
                            <th>OK Ratio</th>
                            <th>NG Ratio</th>
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
                ajax: "{{ route('daily-check-sheet') }}",
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
                    {
                        data: 'formated_date',
                        name: 'formated_date'
                        
                    },
                    @if(auth()->user()->roles->contains('name', 'admin'))
                    {
                        data: 'supplier_name',
                        name: 'supplier_name'
                    },
                    @endif
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
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        
                    }
                ]
            });

            // delete
            $('body').on('click', '.deleteUser', function() {
                var userId = $(this).data('id');
                Swal.fire({
                    title: 'Apakah anda yakin?',
                    text: "Data yang di hapus tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#82868',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "POST",
                            url: "{{ url('users') }}/" + userId,
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: {
                                _method: 'DELETE'
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



            // search permission
            $(document).on('input', '#searchPermission', function() {
                var searchValue = $(this).val().toLowerCase();
                var permissionItems = $('.permission-item');
                var showSelectAll = false;

                permissionItems.each(function() {
                    var label = $(this).find('.form-check-label');
                    var permissionName = label.text().toLowerCase();

                    if (permissionName.includes(searchValue)) {
                        $(this).show();
                        showSelectAll = true;
                    } else {
                        $(this).hide();
                    }
                });

                var selectAllCheckbox = $('#checkAll');
                if (selectAllCheckbox.length > 0) {
                    selectAllCheckbox.closest('.row').css('display', showSelectAll ? 'block' : 'none');
                }
            });


            $('#save-modal').click(function(e) {
                e.preventDefault();
                $(this).html('Sending..');

                $.ajax({
                    data: $('#form-modalAction').serialize(),
                    url: `{{ route('permissions.store') }}`,
                    type: "POST",
                    dataType: 'json',
                    success: function(response) {
                        $('#modalAction').modal('hide');
                        table.draw();
                        if (response.status == true) {
                            showToast('success', response.message);
                        } else {
                            showToast('error', response.message);
                        }
                        $('#save-modal').html('Save');
                    },
                    error: function(response) {
                        if (response.responseJSON && response.responseJSON.errors) {
                            var errors = response.responseJSON.errors;
                            if (errors.hasOwnProperty('permissions')) {
                                var errorMessage = errors['permissions'][0];
                                $('#permissions-error').removeClass('d-none');
                                $('#permissions-error').text(errorMessage);
                            }
                        }

                        $('#save-modal').html('Save');
                    }
                });
            });

        });
    </script>
@endpush
