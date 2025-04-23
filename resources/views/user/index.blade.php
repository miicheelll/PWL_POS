@extends('layouts.template')

@section('content')
    <div class="card">
        <div class="card-header">
                <h3 class="card-title">Daftar user</h3>
                <div class="card-tools">
                    <button onclick="modalAction('{{ url('/user/import') }}')" class="btn btn-info">Import User</button>
                    <a href="{{ url('/user/export_excel') }}" class="btn btn-primary"><i class="fa fa-file-excel"></i>Export User</a>
                    <a href="{{ url('/user/export_pdf') }}" class="btn btn-warning"><i class="fa fa-filepdf"></i> Export PDF</a>
                    <button onclick="modalAction('{{ url('/user/create_ajax') }}')" class="btn btn-success">Tambah Data (Ajax)</button>
                </div>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group row">
                            <label class="col-1 control-label col-form-label">Filter:</label>
                            <div class="col-3">
                                <select class="form-control" id="level_id" name="level_id" required>
                                    <option value="">- Semua -</option>
                                    @foreach($level as $item)
                                        <option value="{{ $item->level_id }}">{{ $item->level_nama }}</option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Level Pengguna</small>
                            </div>
                        </div>
                    </div>
                </div>
                <table class="table table-bordered table-sm table-striped table-hover" id="table-user">
                <thead>
                    <tr><th>No</th><th>Username</th><th>Nama</th><th>Level Pengguna</th><th>Aksi</th></tr>
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
        var tableUser;
        $(document).ready(function(){
            tableUser = $('#table-user').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    "url": "{{ url('user/list') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data": function (d) {
                    }
                },
                columns: [{
                    data: "DT_RowIndex",
                    className: "text-center",
                    width: "5%",
                    orderable: false,
                    searchable: false
                },{
                    data: "username",
                    className: "",
                    width: "15%",
                    orderable: true,
                    searchable: true,
                },{
                    data: "nama",
                    className: "",
                    width: "10%",
                    orderable: true,
                    searchable: true,
                },{
                    data: "level_id",
                    className: "",
                    width: "10%",
                    orderable: true,
                    searchable: false,
                    render: function(data, type, row){
                        return new Intl.NumberFormat('id-ID').format(data);
                }
                },{
                    data: "aksi",
                    className: "text-center",
                    width: "14%",
                    orderable: false,
                    searchable: false
                }
            ]
        });

        $('#table-user_filter input').unbind().bind().on('keyup', function(e){
            if(e.keyCode == 13){ // enter key
                tableUser.search(this.value).draw();
            }
        });

        $('.filter_level').change(function(){
            tableUser.draw();
        });
});
</script>
@endpush