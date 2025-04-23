@extends('layouts.template')

@section('content')
    <div class="card">
        <div class="card-header">
                <h3 class="card-title">Daftar Penjualan</h3>
                <div class="card-tools">
                    <button onclick="modalAction('{{ url('/penjualan/import') }}')" class="btn btn-info">Import Penjualan</button>
                    <a href="{{ url('/penjualan/export_excel') }}" class="btn btn-primary"><i class="fa fa-file-excel"></i>Export Penjualan</a>
                    <a href="{{ url('/penjualan/export_pdf') }}" class="btn btn-warning"><i class="fa fa-filepdf"></i> Export PDF</a>
                    <button onclick="modalAction('{{ url('/penjualan/create_ajax') }}')" class="btn btn-success">Tambah Data (Ajax)</button>
                </div>
            </div>
            <div class="card-body">
                <!-- untuk Filter data -->
                <div id="filter" class="form-horizontal filter-date p-2 border-bottom mb-2">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group form-group-sm row text-sm mb-0">
                                <label for="filter_date" class="col-md-1 col-form-label">Filter</label>
                                <div class="col-md-3">
                                    <select name="filter_user" class="form-control form-control-sm filter_user">
                                        <option value="">- Semua -</option>
                                        @foreach($user as $l)
                                            <option value="{{ $l->user_id }}">{{ $l->username }}</option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">User Penjualan</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                <table class="table table-bordered table-sm table-striped table-hover" id="table-penjualan">
                <thead>
                    <tr><th>No</th><th>Nama User</th><th>Pembeli</th><th>Kode Penjualan</th><th>Tanggal</th><th>Aksi</th></tr>
                </thead>
                <tbody></tbody>
                </table>
            </div>
        </div>
        <div id="myModal" class="modal fade animate shake" tabindex="-1" data-backdrop="static" data-keyboard="false" data-width="75%"></div>
        @endsection
        
        @push('js')
        <script>
            function modalAction(url = ''){
                $('#myModal').load(url,function(){
                    $('#myModal').modal('show');
                });
            }
        var tablePenjualan;
        $(document).ready(function(){
            tablePenjualan = $('#table-penjualan').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    "url": "{{ url('penjualan/list') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data": function (d) {
                        d.filter_user = $('.filter_user').val();
                    }
                },
                columns: [{
                    data: "DT_RowIndex",
                    className: "text-center",
                    width: "5%",
                    orderable: false,
                    searchable: false
                },{
                    data: "user.username",
                    className: "",
                    width: "10%",
                    orderable: true,
                    searchable: false
                },{
                    data: "pembeli",
                    className: "",
                    width: "14%",
                    orderable: true,
                    searchable: true,
                },{
                    data: "penjualan_kode",
                    className: "",
                    width: "16%",
                    orderable: true,
                    searchable: true
                },{
                    data: "penjualan_tanggal",
                    className: "",
                    width: "16%",
                    orderable: true,
                    searchable: true,
                },{
                    data: "aksi",
                    className: "text-center",
                    width: "14%",
                    orderable: false,
                    searchable: false
                }
            ]
        });

        $('#table-penjualan_filter input').unbind().bind().on('keyup', function(e){
            if(e.keyCode == 13){ // enter key
                tablePenjualan.search(this.value).draw();
            }
        });

        $('.filter_user').change(function(){
            tablePenjualan.draw();
        });
});
</script>
@endpush