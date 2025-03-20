@extends('layouts.administrator.master')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="fw-bold">{{ $title }}</h4>

                @can('create parts')
                    <a href="{{ route('problems.create') }}" class="btn btn-primary btn-sm" id="createParts">
                        <i class="ti-plus"></i>
                        Tambah Data
                    </a>
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
                            <th>date</th>
                            <th>Part</th>
                            <th width="300px">Problem</th>
                            <th>Quantity</th>
                            <th>Supplier</th>
                            <th>CAR</th>
                            <th>A3 Report</th>
                            <th >Finding Location</th>
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

    {{-- Add this modal HTML before the closing body tag --}}
{{-- Update modal ID to match JavaScript --}}
<div class="modal fade" id="uploadA3ReportModal" tabindex="-1" role="dialog" aria-labelledby="uploadA3ReportModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadA3ReportModalLabel">Upload A3 Report</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="uploadA3ReportForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="problem_id" value="">
                    <div class="form-group">
                        <label for="no_a3_report">No. A3 Report</label>
                        <input type="text" class="form-control" id="no_a3_report" name="no_a3_report" required>
                    </div>
                    <div class="form-group">
                        <label for="a3_report">A3 Report File</label>
                        <input type="file" class="form-control" id="a3_report" name="a3_report" accept=".pdf,.doc,.docx,.xls,.xlsx" required>
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
@endsection

@push('js')
    <script type="text/javascript">
        $(document).ready(function() {
            // Show upload A3 modal
            $('body').on('click', '.upload-a3-report', function() {
                var problemId = $(this).data('id');
                $('#uploadA3ReportModal').find('input[name="problem_id"]').val(problemId);
                $('#uploadA3ReportModal').modal('show');
            });

            // Handle form submission
            $('#uploadA3ReportForm').on('submit', function(e) {
                e.preventDefault();
                var formData = new FormData(this);

                $.ajax({
                    url: "{{ route('problem-list.upload-a3') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Success!', response.message, 'success');
                            $('#uploadA3ReportModal').modal('hide'); // Fixed modal ID here
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
        $(function() {
            // ajax table
            var table = $('.dataTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('problems.index') }}",
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
                        data: 'formated_date',
                        name: 'formated_date',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: null,
                        name: 'part_number',
                        orderable: true,
                        searchable: true,
                        render: function(data, type, row) {
                            return row.part_number + ' - ' + row.part_name;
                        }
                    },
                    {
                        data: 'problem_description',
                        name: 'problem_description',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'quantity_affected',
                        name: 'quantity_affected',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'supplier_name',
                        name: 'supplier_name',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data:'car',
                        name:'car',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data:'a3_report',
                        name:'a3_report',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'finding_location',
                        name: 'finding_location',
                        orderable: true,
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
                        orderable: false,
                        searchable: false
                    }
                ]
            });


            // selesaikan bnf
            // $('body').on('click', '.closeBnf', function() {
            //     var bnfId = $(this).data('id');
            //     Swal.fire({
            //         title: 'Apakah anda yakin?',
            //         text: "Status BNF ini akan berubah menjadi selesai",
            //         icon: 'warning',
            //         showCancelButton: true,
            //         confirmButtonColor: '#d33',
            //         cancelButtonColor: '#82868',
            //         confirmButtonText: 'Ya, selesaikan!',
            //         cancelButtonText: 'Batal'
            //     }).then((result) => {
            //         if (result.isConfirmed) {
            //             $.ajax({
            //                 type: "POST",
            //                 url: "{{ url('bad-news-firsts') }}/close/" + bnfId,
            //                 headers: {
            //                     'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            //                 },
            //                 data: {
            //                     _method: 'POST'
            //                 },
            //                 success: function(response) {
            //                     table.draw();
            //                     showToast('success', response.message);
            //                 },
            //                 error: function(response) {
            //                     var errorMessage = response.responseJSON
            //                         .message;
            //                     showToast('error',
            //                         errorMessage);
            //                 }
            //             });
            //         }
            //     });
            // });



        });
    </script>
@endpush
