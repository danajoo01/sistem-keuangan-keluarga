"use strict";

// Class Definition
var Users = function() {

    var title = "LPSE Auction";
    var table = '#tbl-data-lpse';
    var grid;

    var _initComponent = function() {

        $('#year').select2({
            placeholder: 'Pilih Tahun',
            allowClear: true,
            theme: 'bootstrap-5',
        });

        $('#lpse').select2({
            placeholder: 'Pilih LPSE',
            allowClear: true,
            theme: 'bootstrap-5',
        });

        $('#kategori_pekerjaan').select2({
            placeholder: 'Pilih Kategori',
            allowClear: true,
            theme: 'bootstrap-5',
        });
    }

    // Fungsi untuk menyimpan data yang dipilih
    var saveData = function(data) {
        // Contoh AJAX untuk menyimpan data ke backend
        $.ajax({
            url: auctionStoretUrl,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                kode_tender: data['Kode Tender'],
                repo_id_lpse: data['Repo id LPSE'],
                detail: JSON.stringify(data),
                // Anda bisa menambahkan field lainnya dari 'data' yang dibutuhkan
            },
            success: function(response) {
                // Handle success response
                if (response.status === 'success') {
                    toastr.success(response.message, {
                        closeButton: true,
                        progressBar: true,
                        positionClass: 'toast-top-right',
                        timeOut: 5000,
                        extendedTimeOut: 1000
                    });
                } else {
                    toastr.error(response.message, {
                        closeButton: true,
                        progressBar: true,
                        positionClass: 'toast-top-right',
                        timeOut: 5000,
                        extendedTimeOut: 1000
                    });
                }
            }
            ,
            error: function(xhr, status, error) {
                // Handle error response
                console.error(xhr.responseText);
                toastr.error('Error occurred while saving data.', {
                    closeButton: true,
                    progressBar: true,
                    positionClass: 'toast-top-right',
                    timeOut: 5000,
                    extendedTimeOut: 1000
                });                
            }
        });
    };

    var _initGrid = function() {
        grid = $(table).DataTable({
            paging: true,
            processing: true,
            scrollX: true,
            ajax: {
                url: auctionListUrl,
                type: 'GET',
                data: function(d) {
                    d.year = $('#year').val();
                    d.lpse = $('#lpse').val();
                    d.kategori_pekerjaan = $('#kategori_pekerjaan').val(); // Tambahkan parameter kategori pekerjaan
                },
                dataSrc: function(json) {
                    $('#processingModal').modal('hide');
                    if ($('#year').val() && $('#lpse').val()) {
                        if (json.status === 'error') {
                            setTimeout(function () { $("#processingModal").modal('hide') }, 100);// Tambahkan ini untuk menutup modal saat error
                            toastr.info(json.message, {
                                closeButton: true,
                                progressBar: true,
                                positionClass: 'toast-top-right',
                                timeOut: 5000,
                                extendedTimeOut: 1000
                            });
                            $('#processingModal').modal('hide');
                            return [];
                            $('#processingModal').modal('hide');
                        } else {
                            fillKategoriPekerjaanDropdown(json.data);
                            if (json.data.length > 0) {
                                toastr.success('Data found and loaded successfully.', {
                                    closeButton: true,
                                    progressBar: true,
                                    positionClass: 'toast-top-right',
                                    timeOut: 5000,
                                    extendedTimeOut: 1000
                                });
                            } else {
                                setTimeout(function () { 
                                    $("#processingModal").modal('hide');
                                }, 100); // Menutup modal jika tidak ada data
                            
                                toastr.info('No data found for the selected criteria.', {
                                    closeButton: true,
                                    progressBar: true,
                                    positionClass: 'toast-top-right',
                                    timeOut: 5000,
                                    extendedTimeOut: 1000
                                });
                            }                            
                            return json.data;
                        }
                    } else {
                        return [];
                    }
                },
                // Tambahkan complete callback untuk menyembunyikan modal setelah selesai
                complete: function() {
                    $('#processingModal').modal('hide');
                }
            },
            drawCallback: function() {
                $('#processingModal').modal('hide');
                $('[data-bs-toggle="tooltip"]').tooltip();
            },
            columns: [
                { data: 'Kode Tender' },
                { data: 'Nama Paket',
                    render: function (data, type, row) {
                        return '<span style="white-space:normal">' + data + '</span>';
                    }
                },
                { data: 'Kategori Pekerjaan',
                    render: function (data, type, row) {
                        return '<span style="white-space:normal">' + data + '</span>';
                    }
                },
                { data: 'Status_Tender' },
                { data: 'HPS' },
                { data: 'tanggal paket dibuat' },
                { data: 'tanggal akhir' },
                { data: null, orderable: false, searchable: false, defaultContent: "", render: function(data, type, row) {
                    var content = '';
                    content += '<a href="javascript:;" class="avatar-text avatar-md view-data" title="View"><i class="feather-file-text"></i></a>';
                    content += '<a href="javascript:;" class="avatar-text avatar-md save-data" title="Save"><i class="feather-save"></i></a>';
                    return content;
                }}
            ],
            order: []
        });

        // Menambahkan event listener untuk tombol "View"
        $(table).on('click', '.view-data', function() {
            var data = grid.row($(this).parents('tr')).data();
            showDetailModal(data);
        });

        //grid.on('order.dt search.dt', function() {
        //    grid.column(0, { search: 'applied', order: 'applied' }).nodes().each(function(cell, i) {
        //        cell.innerHTML = i + 1;
        //    });
        //}).draw();
    };

    var fillKategoriPekerjaanDropdown = function(data) {
        var kategoriPekerjaanDropdown = $('#kategori_pekerjaan');
        kategoriPekerjaanDropdown.empty();
        kategoriPekerjaanDropdown.append('<option value="" disabled selected>Pilih Kategori</option>');

        var categories = [...new Set(data.map(item => item['Kategori Pekerjaan']))];
        categories.forEach(function(category) {
            kategoriPekerjaanDropdown.append('<option value="' + category + '">' + category + '</option>');
        });

        kategoriPekerjaanDropdown.prop('disabled', false);
    };

    var showDetailModal = function(data) {
        // Fungsi untuk memformat angka ke dalam format rupiah
        function formatRupiah(angka) {
            if (!isNaN(angka)) {
                return 'Rp ' + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            } else {
                //console.error(angka + " bukan angka yang valid!");
                return angka;
            }
        }

        // Fungsi untuk memformat tanggal ke format yang diinginkan (misal: dd MMM yyyy)
        function TanggalIndo(tanggal) {
            var date = new Date(tanggal);
            var options = { year: 'numeric', month: 'short', day: 'numeric' };
            return date.toLocaleDateString('id-ID', options);
        }

        // Fungsi ini akan menampilkan data detail dalam modal
        var modal = $('#detailModal');
        var modalBody = modal.find('.modal-body');
        modal.find('.modal-title').text('Detail Tender');

        // Bagian ini adalah bagian dari fungsi showDetailModal

        var detailContent = '<table class="ui fixed table celled compact">' +
                            '<tbody>' +
                            '<tr>' +
                            '<td width="200px">Nama Tender</td>' +
                            '<td style="font-weight: 700;">' + data['Nama Paket'] + '</td>' +
                            '</tr>' +
                            '<tr>' +
                            '<td width="200px">Kode Tender</td>' +
                            '<td style="font-weight: 700;">' + data['Kode Tender'] + '</td>' +
                            '</tr>' +
                            '<tr>' +
                            '<td>Nilai HPS</td>' +
                            '<td>' + formatRupiah(data['HPS']) + '</td>' +
                            '</tr>' +
                            '<tr>' +
                            '<td>Nilai Pagu</td>' +
                            '<td>' + formatRupiah(data['Pagu']) + '</td>' +
                            '</tr>' +
                            '<tr>' +
                            '<td>Instansi</td>' +
                            '<td>' + (data['Instansi dan Satker'].length > 0 ? data['Instansi dan Satker'][0]['nama_instansi'] : '-') + '</td>' +
                            '</tr>' +
                            '<tr>' +
                            '<td>Satuan Kerja</td>' +
                            '<td>' + (data['Instansi dan Satker'].length > 0 ? data['Instansi dan Satker'][0]['stk_nama'] : '-') + '</td>' +
                            '</tr>' +
                            '<tr>' +
                            '<td>Jenis Instansi</td>' +
                            '<td>' + (data['Instansi dan Satker'].length > 0 ? data['Instansi dan Satker'][0]['jenis_instansi'] : '-') + '</td>' +
                            '</tr>' +
                            '<tr>' +
                            '<td>Metode Pengadaan</td>' +
                            '<td>' + data['Metode Pengadaan'] + '</td>' +
                            '</tr>' +
                            '<tr>' +
                            '<td>Kategori</td>' +
                            '<td>' + data['Kategori Pekerjaan'] + '</td>' +
                            '</tr>' +
                            '<tr>' +
                            '<td>Metode Tender</td>' +
                            '<td>' + data['Metode Pemilihan'] + '</td>' +
                            '</tr>' +
                            '<tr>' +
                            '<td>Tahun Anggaran</td>' +
                            '<td>' + data['anggaran'][0]['ang_tahun'] + '</td>' +
                            '</tr>' +
                            '<tr>' +
                            '<td>Metode Evaluasi</td>' +
                            '<td>' + data['Metode Evaluasi'] + '</td>' +
                            '</tr>' +
                            '<tr>' +
                            '<td>Jenis Kontrak</td>' +
                            '<td>' +
                            '<table class="ui table definition">' +
                            '<tbody>' +
                            '<tr>' +
                            '<td width="300">Cara Pembayaran</td>' +
                            '<td>' + data['Cara Pembayaran'] + '</td>' +
                            '</tr>' +
                            '<tr>' +
                            '<td>Kode RUP</td>' +
                            '<td>' + (data['anggaran'][0]['rup_id'] !== '-' ? data['anggaran'][0]['rup_id'] : '-') + '</td>' +
                            '</tr>' +
                            '<tr>' +
                            '<td>Pembebanan Tahun Anggaran</td>' +
                            '<td>' + (data['anggaran'][0]['ang_tahun'] !== '-' ? data['anggaran'][0]['ang_tahun'] : '-') + '</td>' +
                            '</tr>' +
                            '<tr>' +
                            '<td>Sumber Dana</td>' +
                            '<td>' + (data['anggaran'][0]['sbd_id'] !== 'LAINNYA' ? data['anggaran'][0]['sbd_id'] : '-') + '</td>' +
                            '</tr>' +
                            '</tbody>' +
                            '</table>' +
                            '</td>' +
                            '</tr>' +
                            '<tr>' +
                            '<td>Lokasi Pengerjaan</td>' +
                            '<td>' +  data['lokasi_paket'][0]['lokasi']['kbp_nama'] + ' - ' + data['lokasi_paket'][0]['lokasi']['prp_nama'] + ' - ' + data['lokasi_paket'][0]['lokasi']['pkt_lokasi'] + '</td>' +
                            '</tr>' +
                            '<tr>' +
                            '<td>Detail Link LPSE</td>' +
                            '<td><a href="' + data['url'] + '/lelang/' +data['Kode Tender'] + '/pengumumanlelang" target="_blank">' + data['LPSE'] + ' <i class="icon external"></i></a></td>' +
                            '</tr>' +
                            '<tr>' +
                            '<td>Jumlah Pendaftar</td>' +
                            '<td>' + data['Jumlah Pendaftar'] + '</td>' +
                            '</tr>' +
                            '<tr>' +
                            '<td>Jumlah Penawar</td>' +
                            '<td>' + data['Jumlah Penawar'] + '</td>' +
                            '</tr>' +
                            '<tr>' +
                            '<td>Jumlah Kirim Kualifikasi</td>' +
                            '<td>' + data['jumlah_kirim_kualifikasi'] + '</td>' +
                            '</tr>' +
                            '<tr>' +
                            '<td>Durasi Tender</td>' +
                            '<td>' + data['Durasi Tender'] + '</td>' +
                            '</tr>' +
                            '<tr>' +
                            '<td>Versi SPSE Paket</td>' +
                            '<td>' + data['Versi_spse_paket'] + '</td>' +
                            '</tr>' +
                            '</tbody>' +
                            '</table>' +
                            '<div class="card-header d-flex justify-content-between align-items-center">' +
                            '<div>'+
                            '<h4 class="ui header divider horizontal section">Jadwal Pengumuman Dan Penawaran</h4>' +
                            '</div>'+
                            '<div>'+
                            '<div><a class="btn btn-primary lihat-detail" href="' + data['url'] + '/lelang/' +data['Kode Tender'] + '/jadwal" target="_blank" data-id="' + data['Kode Tender'] + '">Lihat Detail Jadwal</a></div>' +
                            //'<button class="btn btn-primary lihat-detail" data-id="' + data['Kode Tender'] + ' data-url="' + data['url'] + '">Lihat Detail Jadwal</button>' +
                            '</div>'+
                            '</div>'+
                            '<table class="ui table definition celled">' +
                            '<thead class="full-width">' +
                            '<tr>' +
                            '<th width="200px">Tahapan</th>' +
                            '<th>Mulai</th>' +
                            '<th>Akhir</th>' +
                            '</tr>' +
                            '</thead>' +
                            '<tbody>';

        data['jadwal_pengumuman'] && (detailContent += '<tr>' +
                            '<td>Pengumuman Prakualifikasi Dan Downloand Kualifikasi</td>' +
                            '<td>' + TanggalIndo(data['jadwal_pengumuman']['tanggal_mulai']) + '</td>' +
                            '<td>' + TanggalIndo(data['jadwal_pengumuman']['tanggal_akhir']) + '</td>' +
                            '</tr>');

        data['jadwal_penawaran'] && (detailContent += '<tr>' +
                            '<td>Penjelasan Dokumen Prakualifikasi Dan Kirim Persyaratan Kualifikasi</td>' +
                            '<td>' + TanggalIndo(data['jadwal_penawaran']['tanggal_mulai']) + '</td>' +
                            '<td>' + TanggalIndo(data['jadwal_penawaran']['tanggal_akhir']) + '</td>' +
                            '</tr>');

        detailContent += '</tbody>' +
                            '</table>';

        modalBody.html(detailContent);
        modal.modal('show');

    }

    // Event handler untuk form filter
    $('#filter-form').on('submit', function(e) {
        e.preventDefault();
        $('#processingModal').modal('show'); // Tampilkan modal saat klik filter
        grid.ajax.reload(null, false); // Reload DataTable tanpa mengatur ulang paging
    });

    var _initFilter = function() {
        $('#filter-form').on('submit', function(e) {
            e.preventDefault();
            if ($('#year').val() && $('#lpse').val()) {
                grid.ajax.reload();
                $('#processingModal').modal('hide');
            } else {
                alert('Please select both filters.');
            }
        });
    }

    return {
        init: function() {
            _initComponent();
            _initGrid();
            _initFilter();

            // Event listener untuk tombol "Save"
            $(table).on('click', '.save-data', function() {
                var data = grid.row($(this).parents('tr')).data();
                saveData(data); // Panggil fungsi saveData dengan data yang dipilih
            });
        }
    };
}();

// Class Initialization
jQuery(document).ready(function() {
    setTimeout(function() {
        Users.init();
    }, 100);


    $('#update-data-btn').click(function(e) {
        e.preventDefault();
        if ($('#year').val() && $('#lpse').val()) {
            $('#processingLPSEModal').modal('show');

            $.ajax({
                url: auctionUpdateUrl,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    lpse: $('#lpse').val(),
                    year: $('#year').val(),
                },
                success: function(response) {
                    $('#processingLPSEModal').modal('hide');
                    if (response.status === 'success') {
                        toastr.success(response.message, {
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-top-right',
                            timeOut: 5000,
                            extendedTimeOut: 1000
                        });
                        // reload DataTable
                        $('#tbl-data-lpse').DataTable().ajax.reload();
                    } else {
                        toastr.error(response.message, {
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-top-right',
                            timeOut: 5000,
                            extendedTimeOut: 1000
                        });
                    }
                },
                error: function(xhr, status, error) {
                    $('#processingLPSEModal').modal('hide');
                    console.error(xhr.responseText);
                    toastr.error('Error occurred while updating data.', {
                        closeButton: true,
                        progressBar: true,
                        positionClass: 'toast-top-right',
                        timeOut: 5000,
                        extendedTimeOut: 1000
                    });
                }                
            });
        }else{
            alert('Please select both filters.');
        }
    });

});
