// Class Definition
var Users = function() {

    var title = "LPSE";
    var table = '#tbl-data-lpse';
    var grid;

    var _initComponent = function() {

        $('[name="nav_status"]').select2({
            placeholder: 'Select Status',
            minimumResultsForSearch: Infinity
        });

        _fetchLpseOptions();
    }

    var _fetchLpseOptions = function() {
        $.ajax({
            url: '/master/proxy/lpse',  // Update the URL to match your route
            type: 'GET',
            success: function(response) {
                var select = $('#nav_name');
                select.empty(); // Clear existing options
                select.append('<option></option>'); // Add placeholder for Select2
                response.forEach(function(lpse) {
                    select.append('<option value="' + lpse.nama_lpse + '" data-kd_lpse="' + lpse.kd_lpse + '">' + lpse.nama_lpse + '</option>');
                });
                // Initialize Select2 with search functionality
                select.select2({
                    placeholder: 'Select LPSE Name',
                    allowClear: true,
                    theme: 'bootstrap-5',
                });
            },
            error: function() {
                console.error('Failed to fetch LPSE data.');
            }
        });
    }

    var _initGrid = function() {
        grid = $(table).DataTable({
            paging: true,
            responsive: true,
            processing: true,
            scrollCollapse: true,
            ajax: {
                url: $(table).attr('target') + "/list",
                type: 'post',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            },
            drawCallback: function() {
                $('[data-bs-toggle="tooltip"]').tooltip();
            },
            columns: [
                { data: null, searchable: false, orderable: false, defaultContent: "" },
                { data: 'nama_lpse' },
                { data: 'url' },
                {
                    data: 'status',
                    class: 'align-middle',
                    render: function(data, type, row) {
                        // Function to determine the badge color based on the status
                        function colorStatus(status) {
                            return status == 1 ? 'success' : 'danger';
                        }

                        // Function to determine the text based on the status
                        function statusText(status) {
                            return status == 1 ? 'Active' : 'Deactive';
                        }

                        // Return the HTML for the badge
                        return '<span class="badge bg-' + colorStatus(row.status) + '">' + statusText(row.status) + '</span>';
                    }
                },
                {
                    data: null,
                    class: 'hstack',
                    defaultContent: "",
                    render: function(data, type, row, meta) {
                        return `
                            <a href="javascript:void(0);" title="Edit" class="avatar-text avatar-md change-data">
                                <i class="feather feather-edit"></i>
                            </a>
                            <a href="javascript:void(0);" title="Delete" class="avatar-text avatar-md remove-data">
                                <i class="feather feather-trash-2"></i>
                            </a>
                        `;
                    }
                }
            ],
            order: []
        });

        grid.on('order.dt search.dt', function() {
            grid.column(0, { search: 'applied', order: 'applied' }).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1;
            });
        }).draw();

        $(table).find('tbody').on('click', '.remove-data', function () {
            var id = grid.row($(this).parents('tr')).data().id;

            $.confirm({
                title: "Delete " + title,
                content: "Are you sure want to delete selected " + title.toLowerCase() + "?",
                type: 'red',
                buttons: {
                    remove: {
                        text: "Delete",
                        btnClass: "btn-danger",
                        action: function () {
                            $.alert({
                                type: 'orange',
                                buttons: {
                                    close: {
                                        btnClass: "btn-primary"
                                    }
                                },
                                content: function () {
                                    var self = this;

                                    return $.ajax({
                                        url: $(table).attr('target') + "/delete",
                                        type: 'DELETE',
                                        headers: {
                                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                        },
                                        data: {
                                            nav_id: id
                                        },
                                        dataType: 'json'
                                    })
                                    .done(function (res) {
                                        if (res.result) {
                                            self.setContent(res.response.msg);
                                            self.setTitle("Delete Success");
                                            grid.ajax.reload(null, false); // Reload the table data
                                        } else {
                                            self.setContent(res.response.msg);
                                            self.setTitle("Delete Failed");
                                        }
                                    })
                                    .fail(function () {
                                        self.setContent('Something happened while deleting selected ' + title.toLowerCase() + '!');
                                    });
                                }
                            });
                        }
                    },
                    cancel: function () {}
                }
            });
        }).on('click', '.change-data', function() {
            var rowData = grid.row($(this).parents('tr')).data();
            $('#nav_name').val(rowData.nama_lpse).trigger('change'); // Trigger change after setting value
            $('[name="nav_url"]').val(rowData.url);
            $('[name="nav_status"]').val(rowData.status).trigger('change');
            $('[name="nav_id"]').val(rowData.id);
            $('#form-alert').hide();
            $('#add-modal-title').html('<i class="feather-edit text-secondary" aria-hidden="true"></i> Edit LPSE');
            $('#add-modal').modal('show');
        });
    }

    var _handleNewData = function() {
        const form = document.getElementById('general-form');
        $('#show-add-modal').on('click', function() {
            $('#add-modal').modal('show');
            $('#add-modal-title').html('<i class="feather-plus text-secondary" aria-hidden="true"></i> New LPSE');
            $('#form-alert').hide();
            $('#general-form input[type="text"], [name="cat_id"]').val("");
            $('[name="nav_status"]').val('1').trigger('change');
            $('[name="nav_id"]').val('');
        });
        $('#save-form').on('click', function(e) {
            _saveData();
        });
    }

    var _saveData = function() {
        $('#form-alert').hide();
        $('#save-form').html('<i class="feather-plus" aria-hidden="true"></i> Saving...').attr('disabled', 'true').addClass('disabled');

        var formData = $('#general-form').serialize();
        var url = $('#general-form').attr('action');
        var kd_lpse = $('#nav_name').find('option:selected').data('kd_lpse'); // Get kd_lpse from selected option

        if ($('[name="nav_id"]').val() !== "") {
            url += "/update"
        } else {
            url += "/store";
        }

        // Append kd_lpse to formData
        formData += '&kd_lpse=' + kd_lpse;

        $.ajax({
            type: "post",
            url: url,
            data: formData,
            dataType: "json",
            success: function (res) {
                if (res.result) {
                    //appToast('success', res.response.msg, 'feather-check-circle');
                    toastr.success(res.response.msg);
                    $('#general-form input[type="text"], [name="cat_id"]').val("");
                    $('.select-two').val([]);
                    $('#save-form').html('<i class="feather-check" aria-hidden="true"></i> Save').removeAttr('disabled').removeClass('disabled');
                    $('#add-modal').modal('hide');
                    grid.ajax.reload(); // Reload the table data
                    _getNavTree();
                } else {
                    if (res.response.type == 0) {
                        //appToast('danger', res.response.msg, 'feather-check-circle');
                        toastr.error(res.response.msg);
                    } else {
                        $('#alert-content').html(res.response.msg);
                        $('#form-alert').show();
                    }
                }
            },
            error: function() {
                //appToast('danger', 'Something wrong happened while updating data!', 'feather-check-circle');
                toastr.error('Something wrong happened while updating data!');
            },
            complete: function() {
                $('#save-form').html('<i class="feather-check" aria-hidden="true"></i> Save').removeAttr('disabled').removeClass('disabled');
            }
        });
    }

    // Public Functions
    return {
        init: function() {
            _initComponent();
            _initGrid();
            _handleNewData();
        }
    };
}();

jQuery(document).ready(function() {
    setTimeout(function() {
        Users.init();
    }, 100);
});
