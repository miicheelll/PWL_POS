@extends('layouts.template')

@section('content')
    <div class="card">
        <div class="card-header">
                <h3 class="card-title">Daftar Stok</h3>
                <div class="card-tools">
                    <button onclick="modalAction('{{ url('/stok/import') }}')" class="btn btn-info">Import Stok</button>
                    <a href="{{ url('/stok/export_excel') }}" class="btn btn-primary"><i class="fa fa-file-excel"></i>Export Stok</a>
                    <a href="{{ url('/stok/export_pdf') }}" class="btn btn-warning"><i class="fa fa-filepdf"></i> Export PDF</a>
                    <button onclick="modalAction('{{ url('/stok/create_ajax') }}')" class="btn btn-success">Tambah Data (Ajax)</button>
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
                                    <select name="filter_supplier" class="form-control form-control-sm filter_supplier">
                                        <option value="">- Semua -</option>
                                        @foreach($supplier as $l)
                                            <option value="{{ $l->supplier_id }}">{{ $l->supplier_nama }}</option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">Supplier</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="filter" class="form-horizontal filter-date p-2 border-bottom mb-2">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group form-group-sm row text-sm mb-0">
                                <label for="filter_date" class="col-md-1 col-form-label">Filter</label>
                                <div class="col-md-3">
                                    <select name="filter_barang" class="form-control form-control-sm filter_barang">
                                        <option value="">- Semua -</option>
                                        @foreach($barang as $l)
                                            <option value="{{ $l->barang_id }}">{{ $l->barang_nama }}</option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">Nama Barang</small>
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
                <table class="table table-bordered table-sm table-striped table-hover" id="table-stok">
                <thead>
                    <tr><th>No</th><th>Supplier</th><th>Nama Barang</th><th>Nama User</th><th>Tanggal</th><th>Jumlah Stok</th><th>Aksi</th></tr>
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
        var tableStok;
        $(document).ready(function(){
            tableStok = $('#table-stok').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    "url": "{{ url('stok/list') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data": function (d) {
                        d.filter_supplier = $('.filter_supplier').val();
                        d.filter_barang = $('.filter_barang').val();
                    }
                },
                columns: [{
                    data: "DT_RowIndex",
                    className: "text-center",
                    width: "5%",
                    orderable: false,
                    searchable: false
                },{
                    data: "supplier.supplier_nama",
                    className: "",
                    width: "14%",
                    orderable: true,
                    searchable: true
                },{
                    data: "barang.barang_nama",
                    className: "",
                    width: "14%",
                    orderable: true,
                    searchable: false
                },{
                    data: "user.username",
                    className: "",
                    width: "14%",
                    orderable: true,
                    searchable: false
                },{
                    data: "stok_tanggal",
                    className: "",
                    width: "14%",
                    orderable: false,
                    searchable: false
                },{
                    data: "stok_jumlah",
                    className: "",
                    width: "14%",
                    orderable: false,
                    searchable: false
                },{
                    data: "aksi",
                    className: "text-center",
                    width: "14%",
                    orderable: false,
                    searchable: false
                }
            ]
        });

        $('#table-stok_filter input').unbind().bind().on('keyup', function(e){
            if(e.keyCode == 13){ // enter key
                tableStok.search(this.value).draw();
            }
        });

        $('.filter_supplier').change(function(){
            tableStok.draw();
        });
        $('.filter_barang').change(function(){
            tableStok.draw();
        });
});
</script>
@endpush