<form action="{{ url('/penjualan/ajax') }}" method="POST" id="form-tambah"> 
    @csrf 
    <div id="modal-master" class="modal-dialog modal-lg" role="document"> 
        <div class="modal-content"> 
            <div class="modal-header"> 
                <h5 class="modal-title" id="exampleModalLabel">Tambah Data Penjualan</h5> 
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span 
                    aria-hidden="true">&times;</span></button> 
            </div> 
            <div class="modal-body"> 
                <div class="form-group"> 
                    <label>Nama User</label> 
                    <select name="user_id" id="user_id" class="form-control" required> 
                        <option value="">- Pilih User -</option> 
                        @foreach($user as $l) 
                            <option value="{{ $l->user_id }}">{{ $l->username }}</option> 
                        @endforeach 
                    </select> 
                    <small id="error-user_id" class="error-text form-text text-danger"></small> 
                </div> 
                <div class="form-group"> 
                    <label>Pembeli</label> 
                    <input value="" type="text" name="pembeli" id="pembeli" class="form-control" required> 
                    <small id="error-pembeli" class="error-text form-text text-danger"></small> 
                </div> 
                <div class="form-group"> 
                    <label>Kode Penjualan</label> 
                    <input value="" type="text" name="penjualan_kode" id="penjualan_kode" class="form-control" required> 
                    <small id="error-penjualan_kode" class="error-text form-text text-danger"></small> 
                </div> 
                <div class="form-group"> 
                    <label>Tanggal</label> 
                    <input value="" type="date" name="penjualan_tanggal" id="penjualan_tanggal" class="form-control" required> 
                    <small id="error-penjualan_tanggal" class="error-text form-text text-danger"></small> 
                </div> 
                <label>Detail Penjualan</label>
                <div id="penjualan-detail-container">
                    <div class="form-group">
                        <div id="penjualan-detail">
                            <div class="penjualan-detail-item">
                                <select name="penjualan_detail[0][barang_id]" class="form-control" required>
                                    <option value="">- Pilih Barang -</option>
                                    @foreach($barang as $l)
                                        <option value="{{ $l->barang_id }}">{{ $l->barang_nama }}</option>
                                    @endforeach
                                </select>
                                <br>
                                <input type="number" name="penjualan_detail[0][jumlah]" class="form-control" placeholder="Jumlah" required>
                                <br>
                                <span id="harga-0" class="form-text text-muted">Harga akan dihitung secara otomatis</span>
                                <br>
                                <button type="button" class="btn btn-danger remove-detail">Hapus</button>
                                <br>
                            </div>
                        </div>
                        <br>
                        <button type="button" class="btn btn-primary add-detail">Tambah Detail</button>
                    </div>
                </div>
            </div> 
        <div class="modal-footer"> 
            <button type="button" data-dismiss="modal" class="btn btn-warning">Batal</button> 
            <button type="submit" class="btn btn-primary">Simpan</button> 
        </div> 
    </div> 
</div> 
</form> 
<script> 
    $(document).ready(function() { 
        $("#form-tambah").validate({ 
            rules: { 
                user_id: {required: true, number: true}, 
                pembeli: {required: true, maxlength: 50}, 
                penjualan_kode: {required: true, maxlength: 20}, 
                penjualan_tanggal: {required: true, date: true},
                'penjualan_detail[0][barang_id]': {required: true, number: true}, 
                'penjualan_detail[0][jumlah]': {required: true, number: true}, 
            }, 
            submitHandler: function(form) { 
                $.ajax({ 
                    url: form.action, 
                    type: form.method, 
                    data: $(form).serialize(), 
                    success: function(response) { 
                        if(response.status){ 
                            $('#myModal').modal('hide'); 
                            Swal.fire({ 
                                icon: 'success', 
                                title: 'Berhasil', 
                                text: response.message 
                            }); 
                            dataPenjualan.ajax.reload(); 
                        }else{ 
                            $('.error-text').text(''); 
                            $.each(response.msgField, function(prefix, val) { 
                                $('#error-'+prefix).text(val[0]); 
                            }); 
                            Swal.fire({ 
                                icon: 'error', 
                                title: 'Terjadi Kesalahan', 
                                text: response.message 
                            }); 
                        } 
                    }             
                }); 
                return false; 
            }, 
            errorElement: 'span', 
            errorPlacement: function (error, element) { 
                error.addClass('invalid-feedback'); 
                element.closest('.form-group').append(error); 
            }, 
            highlight: function (element, errorClass, validClass) { 
                $(element).addClass('is-invalid'); 
            }, 
            unhighlight: function (element, errorClass, validClass) { 
                $(element).removeClass('is-invalid'); 
            } 
        }); 

        // Tambah detail penjualan
        $('.add-detail').click(function() {
            var index = $('.penjualan-detail-item').length;
            $('#penjualan-detail').append(`
                <div class="penjualan-detail-item">
                    <br>
                    <select name="penjualan_detail[${index}][barang_id]" class="form-control" required>
                        <option value="">- Pilih Barang -</option>
                        @foreach($barang as $l)
                            <option value="{{ $l->barang_id }}">{{ $l->barang_nama }}</option>
                        @endforeach
                    </select>
                    <br>
                    <input type="number" name="penjualan_detail[${index}][jumlah]" class="form-control" placeholder="Jumlah" required>
                    <br>
                    <span id="harga-${index}" class="form-text text-muted">Harga akan dihitung secara otomatis</span>
                    <br>
                    <button type="button" class="btn btn-danger remove-detail">Hapus</button>
                    <br>
                </div>
            `);
        });

        // Hapus detail penjualan
        $('#penjualan-detail').on('click', '.remove-detail', function() {
            $(this).closest('.penjualan-detail-item').remove();
        });
    }); 
</script>