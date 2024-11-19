@extends('template')
@section('content')
    @if(session('success'))
      <script type="text/javascript">
      function mssge() {
        Swal.fire({
          title: "{{ session('success') }}",
          text: 'You clicked the button!',
          icon: 'success',
          customClass: {
            confirmButton: 'btn btn-primary'
          },
          buttonsStyling: false
        });
      }
      window.onload = mssge;
      </script>
    @endif

    @if (count($errors) > 0)
      @foreach ($errors->all() as $error)
        <p class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">×</button>{{ $error }}</p>
      @endforeach
    @endif
    
    <div class="row">
      <div class="col-xxl">
        <div class="card mb-4">
          <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0" id="formTitle">Form Header</h5>            
          </div>
          
            <form id="formAuthentication" class="card-body form-horizontal">
              @csrf
              <!-- Tambahkan metode spoof PUT untuk update, diatur secara dinamis dengan JS -->
              <input type="hidden" name="_method" id="formMethod" value="POST">
              <div class="row mb-3">
                <label class="col-sm-1 col-form-label" for="basic-default-name">No.Journal</label>
                <div class="col-sm-1">
                    <select class="form-control select2 form-select" name="code_jgr" id="code_jgr" data-allow-clear="true">
                        <option value="XX" selected="selected">--</option>
                        @foreach ($journalGroupSina as $jgs)
                        <option value="{{ $jgs->code_jgr }}">{{ $jgs->code_jgr }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-2">
                    <select class="form-control select2 form-select" name="code_jrc" id="code_jrc" data-allow-clear="true">
                        <option value="XX" selected="selected">--</option>
                    </select>
                </div>
                <div class="col-sm-2" style="display: flex; align-items: center;">
                    <span id="cp" style="margin-right: 10px;background-color: orange;color: black;"></span>
                    <input type="text" style="display: flex; align-items: left;" class="form-control" id="journal_jrc_no" name="journal_jrc_no" placeholder="Journal Number">
                    <input type="hidden" class="form-control" id="cpx" name="cpx">
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-1 col-form-label" for="basic-default-name">Periode</label>
                <div class="col-sm-1">
                    <input type="text" class="form-control" id="dt_periode" name="dt_periode" placeholder="Periode" disabled>
                </div>
                <label class="col-sm-1 col-form-label" for="additional_notes">Terima Dari</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="acceptedBy" name="acceptedBy" placeholder=". . . . . . . . .">
                </div>                
              </div>
              <div class="row mb-3">
                <label class="col-sm-1 col-form-label" for="basic-default-name">Tanggal</label>
                <div class="col-sm-2">
                    <input type="date" class="form-control" id="journal_date" name="journal_date">
                </div> 
                <label class="col-sm-2 col-form-label text-end" for="basic-default-name">Jatuh Tempo</label>
                <div class="col-sm-2">
                    <input type="date" class="form-control" id="due_date" name="due_date">
                </div>              
              </div>
              <div class="pt-1">
                  <button type="button" id="addBtn" class="btn btn-success">Save Header</button>
                  <button type="button" id="clear" class="btn btn-warning">Clear</button>
              </div>                                               
            </form>            
          
        </div>
      </div> 

      <div class="col-xxl" style="min-width: 100%;">
        <div class="card mb-4">
          <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0" id="formTitle">Form Detail</h5>            
          </div>
                      
            <form id="formAuthentication_detail" class="card-body form-horizontal" style="overflow-x: auto; white-space: nowrap;">
                <div class="row g-3 d-flex flex-nowrap">
                    <div class="col-md-2">
                        <label class="col-form-label text-end" for="basic-default-name">No. Perkiraan</label>             
                        <input type="text" data-bs-toggle="modal" data-bs-target="#modAccList" class="form-control" id="account_no" name="account_no" placeholder="Klik here.." readonly>
                    </div>
                    <div class="col-md-1">
                        <label class="col-form-label text-end" for="basic-default-name">Biaya</label>             
                        <input type="text" data-bs-toggle="modal" data-bs-target="#modCostList" class="form-control" id="code_cost" name="code_cost" placeholder="Klik here.." readonly>
                    </div>
                    <div class="col-md-2">
                        <label class="col-form-label text-end" for="basic-default-name">Nama Perkiraan</label>             
                        <input type="text" class="form-control" id="account_name" name="account_name" readonly>
                    </div>
                    <div class="col-md-1">
                        <label class="col-form-label text-end" for="basic-default-name">Divisi</label>             
                        <input type="text" data-bs-toggle="modal" data-bs-target="#modDivList" class="form-control" id="code" name="code" placeholder="Klik here.." readonly>
                    </div>
                    <div class="col-md-1">
                        <label class="col-form-label text-end" for="basic-default-name">Currency</label>             
                        <input type="text" data-bs-toggle="modal" data-bs-target="#modCurrList" class="form-control" id="code_currency" name="code_currency" placeholder="Klik here.." readonly>
                    </div>
                    <div class="col-md-2">
                        <label class="col-form-label text-end" for="basic-default-name">Debet</label>             
                        <input type="text" class="form-control" id="debet" name="debet">
                    </div>
                    <div class="col-md-2">
                        <label class="col-form-label text-end" for="basic-default-name">Kredit</label>             
                        <input type="text" class="form-control" id="kredit" name="kredit">
                    </div>
                    <div class="col-md-1">
                        <label class="col-form-label text-end" for="basic-default-name">Kurs</label>             
                        <input type="text" class="form-control" id="kurs" name="kurs">
                    </div>
                    <div class="col-md-2">
                        <label class="col-form-label text-end" for="basic-default-name">Jumlah Total</label>             
                        <input type="text" class="form-control" id="jumlah_total" name="jumlah_total">
                    </div>
                </div>
                <div class="pt-1">
                  <button type="button" id="addBtnDetail" class="btn btn-success">Save Detail</button>
                  <button type="button" id="clearDetail" class="btn btn-warning">Clear</button>
                </div>                
            </form>
        </div>
      </div>


      <div class="col-xxl" style="min-width: 100%;">
        <div class="card mb-4">
          <div class="card-datatable text-nowrap">
            <table id="journalSourceCodeSina_dt" class="datatables-ajax table">
              <thead>
              <tr>
                <th width="15px">No.</th>
                <th>Journal Code</th>
                <th>Journal No.</th>
                <th>Account No.</th>
                <th>Account Name</th>
                <th>Action</th>
              </tr>
              </thead>              
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal HTML -->
    <div class="modal fade" id="modAccList" tabindex="-1" aria-labelledby="modAccList" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 70%;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="accountListModalLabel">Account List</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="card">
                      <!-- /.card-header -->
                      <div class="card-datatable text-nowrap">
                        <table id="accountTable" class="datatables-ajax table-striped">
                          <thead>
                          <tr>
                            <th width="15px">No.</th>
                            <th>Account No</th>
                            <th>Account Name</th>
                            <th>Type</th>
                            <th>Action</th>
                          </tr>
                          </thead>
                        </table>
                      </div>
                    </div>                    
                </div>
            </div>
        </div>
    </div>

    <!-- Modal HTML -->
    <div class="modal fade" id="modCostList" tabindex="-1" aria-labelledby="modCostList" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 50%;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="costListModalLabel">Cost List</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="card">
                      <!-- /.card-header -->
                      <div class="card-datatable text-nowrap">
                        <table id="costTable" class="datatables-ajax table-striped">
                          <thead>
                          <tr>
                            <th width="15px">No.</th>
                            <th>Code</th>
                            <th>Code Description</th>
                            <th>Category</th>
                            <th>Action</th>
                          </tr>
                          </thead>
                        </table>
                      </div>
                    </div>                    
                </div>
            </div>
        </div>
    </div>

    <!-- Modal HTML -->
    <div class="modal fade" id="modDivList" tabindex="-1" aria-labelledby="modDivList" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 50%;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="divListModalLabel">Cost List</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="card">
                      <!-- /.card-header -->
                      <div class="card-datatable text-nowrap">
                        <table id="divTable" class="datatables-ajax table-striped">
                          <thead>
                          <tr>
                            <th width="15px">No.</th>
                            <th>Code</th>
                            <th>Division Name</th>
                            <th>Category</th>
                            <th>Action</th>
                          </tr>
                          </thead>
                        </table>
                      </div>
                    </div>                    
                </div>
            </div>
        </div>
    </div>

    <!-- Modal HTML -->
    <div class="modal fade" id="modCurrList" tabindex="-1" aria-labelledby="modCurrList" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 50%;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="currListModalLabel">Cost List</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="card">
                      <!-- /.card-header -->
                      <div class="card-datatable text-nowrap">
                        <table id="currTable" class="datatables-ajax table-striped">
                          <thead>
                          <tr>
                            <th width="15px">No.</th>
                            <th>Code Currency</th>
                            <th>Currency Description</th>
                            <th>Action</th>
                          </tr>
                          </thead>
                        </table>
                      </div>
                    </div>                    
                </div>
            </div>
        </div>
    </div>
    
@stop
@section('custom-js')
<script type="text/javascript">    
var table; // Declare table variable in global scope
$(document).ready(function() {
        table = $('#journalSourceCodeSina_dt').DataTable({
        processing: true,
        serverSide: true,
        // ajax: '{!! route('journalSourceCodeSina.data') !!}', // Memanggil route yang menampilkan data JSON
        ajax: {
            url: '{!! route('journalSourceCodeSina.data') !!}', // Memanggil route yang menampilkan data JSON
            data: function (d) {
                d.code_jgr = $('#code_jgr').val(); // Kirimkan code_jgr sebagai parameter tambahan
            }
        },

        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'code_jrc', name: 'code_jrc' },
            { data: 'journal_jrc_no', name: 'journal_jrc_no' },
            { data: 'account_no', name: 'account_no' },
            { data: 'account_name', name: 'account_name' },            
            {
                data: 'action',
                render: function (data, type, row, meta) {
                    return (
                        `<a href="javascript:;" onclick="editJournalSourceCode(${row.id_jsc})" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip-warning" title="Edit" class="btn btn-sm btn-icon item-edit"><i class="text-warning ti ti-pencil"></i></a>` +
                        `<a href="javascript:;" onclick="confirmDelete(${row.id_jsc})" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip-success" title="Delete" class="btn btn-sm btn-icon"><i class="text-success ti ti-trash"></i></a>`
                    );
                },
                orderable: false, searchable: false
            }
        ],
        drawCallback: function(settings) {
            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    customClass: tooltipTriggerEl.getAttribute('data-bs-custom-class') || ''
                });
            });
        },
        scrollX: true,
        order: [[1, 'asc']]
    });
    
    $('#addBtn').on('click', function(event) {
        event.preventDefault();
        addJournalSina();
    });

    $('#clear').on('click', function(event) {
        resetFormAndButton();
    });

    $('#addBtnDetail').on('click', function(event) {
        event.preventDefault();
        addDetailJournalSina();
    });

    $('#clearDetail').on('click', function(event) {
        resetDetailFormAndButton();
    });

    $('#code_jgr').on('change', function(event) {
        event.preventDefault();
        c_jgr = $('#code_jgr').val();
         
        showJSC(c_jgr);
        // table.ajax.reload(); // Reload data table saat dropdown berubah       
        // alert(c_jgr);
    });

    $('#code_jrc').on('change', function(event) {
        event.preventDefault();
        c_jgr = $('#code_jgr').val();
        c_jrc = $('#code_jrc').val();
         
        showJsrNo(c_jgr,c_jrc);
        // table.ajax.reload(); // Reload data table saat dropdown berubah       
        // alert(c_jgr);
    });

    $('#journal_jrc_no').on('input', function(event) {
        event.preventDefault();
        j_jrc_no = $('#journal_jrc_no').val();
        c_jgr = $('#code_jgr').val();
        c_jrc = $('#code_jrc').val();

         
        setFormByHeader(j_jrc_no,c_jgr,c_jrc);
        table.ajax.reload(); // Reload data table saat dropdown berubah       
        
    });    

    $('#modAccList').on('shown.bs.modal', function () {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
    });

    $('#modCostList').on('shown.bs.modal', function () {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
    });

    $('#modDivList').on('shown.bs.modal', function () {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
    });

});

var table_mod;
$(document).ready(function() {
    table_mod = $('#accountTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{!! route('accountListSina.data') !!}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'account_no', name: 'account_no' },
            { data: 'account_name', name: 'account_name' },
            { data: 'typeName', name: 'typeName' },
            {
                data: 'action',
                render: function (data, type, row, meta) {
                    return (
                        `<a href="javascript:;" onclick="getAccountDetail('${row.account_no}', '${row.account_name}')" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip-warning" title="Pick" class="btn btn-sm btn-icon item-edit"><i class="text-warning ti ti-check"></i></a>`
                    );
                },
                orderable: false, searchable: false
            }
        ],
        drawCallback: function(settings) {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    customClass: tooltipTriggerEl.getAttribute('data-bs-custom-class') || ''
                });
            });
        },
        scrollX: true,
        order: [[1, 'asc']]
    });
});

var table_modCost;
$(document).ready(function() {
    table_modCost = $('#costTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{!! route('costListSina.data') !!}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'code_cost', name: 'code_cost' },
            { data: 'cost_description', name: 'cost_description' },
            { data: 'cost_category', name: 'cost_category' },
            {
                data: 'action',
                render: function (data, type, row, meta) {
                    return (
                        `<a href="javascript:;" onclick="getCostDetail('${row.code_cost}', '${row.cost_description}')" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip-warning" title="Pick" class="btn btn-sm btn-icon item-edit"><i class="text-warning ti ti-check"></i></a>`
                    );
                },
                orderable: false, searchable: false
            }
        ],
        drawCallback: function(settings) {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    customClass: tooltipTriggerEl.getAttribute('data-bs-custom-class') || ''
                });
            });
        },
        scrollX: true,
        order: [[1, 'asc']]
    });
});

var table_modDiv;
$(document).ready(function() {
    table_modDiv = $('#divTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{!! route('divisionListSina.data') !!}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'code', name: 'code' },
            { data: 'division_name', name: 'division_name' },
            { data: 'category', name: 'category' },
            {
                data: 'action',
                render: function (data, type, row, meta) {
                    return (
                        `<a href="javascript:;" onclick="getDivDetail('${row.code}', '${row.division_name}')" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip-warning" title="Pick" class="btn btn-sm btn-icon item-edit"><i class="text-warning ti ti-check"></i></a>`
                    );
                },
                orderable: false, searchable: false
            }
        ],
        drawCallback: function(settings) {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    customClass: tooltipTriggerEl.getAttribute('data-bs-custom-class') || ''
                });
            });
        },
        scrollX: true,
        order: [[1, 'asc']]
    });
});

var table_modCurr;
$(document).ready(function() {
    table_modDiv = $('#currTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{!! route('currencySina.data') !!}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'code_currency', name: 'code_currency' },
            { data: 'currency_description', name: 'currency_description' },
            {
                data: 'action',
                render: function (data, type, row, meta) {
                    return (
                        `<a href="javascript:;" onclick="getCurrDetail('${row.code_currency}', '${row.currency_description}')" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip-warning" title="Pick" class="btn btn-sm btn-icon item-edit"><i class="text-warning ti ti-check"></i></a>`
                    );
                },
                orderable: false, searchable: false
            }
        ],
        drawCallback: function(settings) {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    customClass: tooltipTriggerEl.getAttribute('data-bs-custom-class') || ''
                });
            });
        },
        scrollX: true,
        order: [[1, 'asc']]
    });
});

function getAccountDetail(account_no, account_name) {
    // Isi field 'account_no' dan 'account_name' di form
    $('#account_no').val(account_no);
    $('#account_name').val(account_name);

    // Tutup modal menggunakan Bootstrap modal API
    $('#modAccList').modal('hide');
}

function getCostDetail(code_cost, cost_description) {
    // Isi field 'account_no' dan 'account_name' di form
    $('#code_cost').val(code_cost);
    $('#cost_description').val(cost_description);

    // Tutup modal menggunakan Bootstrap modal API
    $('#modCostList').modal('hide');
}

function getDivDetail(code, division_name) {
    // Isi field 'account_no' dan 'account_name' di form
    $('#code').val(code);
    $('#division_name').val(division_name);

    // Tutup modal menggunakan Bootstrap modal API
    $('#modDivList').modal('hide');
}

function getCurrDetail(code_currency, currency_description) {
    // Isi field 'account_no' dan 'account_name' di form
    $('#code_currency').val(code_currency);
    $('#currency_description').val(currency_description);

    // Tutup modal menggunakan Bootstrap modal API
    $('#modCurrList').modal('hide');
}

function showDetJG(c_jgr) {
    $.ajax({
        url: `journalSourceCodeSina/cjgr/${c_jgr}`, // Fetch user data by code
        type: 'GET',
        success: function(journalSourceCodeSina) {
            // Populate form fields with the user's data
            $('#description_jgr').val(journalSourceCodeSina.description_jgr);
            $('#deb_cre').val(journalSourceCodeSina.deb_cre);
        },
        error: function(xhr) {
            alert('Terjadi kesalahan saat mengambil data Account List.');
        }
    });
}

function showJSC(c_jgr) {
    $.ajax({
        url: `journalSina/jsc/${c_jgr}`, // Fetch user data by code
        type: 'GET',
        success: function(journalSina) {
            // Populate form fields with the user's data
            $('#code_jrc').empty();

            // Add the default option
            $('#code_jrc').append('<option value="XX" selected="selected">--</option>');

            // Populate the #code_jrc dropdown with the returned data
            $.each(journalSina, function(index, value) {
                $('#code_jrc').append(`<option value="${value.code_jrc}">${value.code_jrc}</option>`);
            });
        },
        error: function(xhr) {
            alert('Terjadi kesalahan saat mengambil data Journal Source Code.');
        }
    });
}

function showJsrNo(c_jgr,c_jrc) {
    $.ajax({
        url: `journalSina/jsrNo/${c_jgr}/${c_jrc}`, // Fetch data by code
        type: 'GET',
        success: function(response) { 
            // alert(response.jsrNo);        
            $('#cp').text(response.cp);
            $('#cpx').val(response.cp);
            $('#journal_jrc_no').val(response.jsrNo).focus();
            $('#dt_periode').val(response.dt_periode);
        },
        error: function(xhr) {
            alert('Terjadi kesalahan saat mengambil data Journal Source Code.');
        }
    });
}

function setFormByHeader(j_jrc_no, c_jgr, c_jrc) {
    $.ajax({
        url: `journalSina/setFormByHeader/${j_jrc_no}/${c_jgr}/${c_jrc}`, // Fetch data by code
        type: 'GET',
        success: function(response) {
            if (response.status === 'success') {
                const data = response.data;

                // Isi nilai ke input form
                $('#journal_date').val(data.journal_date);
                $('#due_date').val(data.due_date);
                $('#acceptedBy').val(data.description);

                // Jika description tidak kosong, ubah ke mode edit
                if (data.description !== "") {
                    $('#formAuthentication').attr('action', `journalSourceCodeSina/update/${j_jrc_no}`);
                    $('#formTitle').text('Edit Form');
                    $('#addBtn').text('Edit');
                    $('#formMethod').val('PUT');

                    // Ubah event tombol untuk proses update
                    $('#addBtn').off('click').on('click', function(event) {
                        event.preventDefault();
                        updateJournalSourceCode(j_jrc_no); // Sesuaikan dengan parameter yang benar
                    });
                } else {
                    // Mode tambah
                    $('#addBtn').text('Simpan');
                }
            } else {
                // Tampilkan pesan error dari respons server
                // alert(response.message);
                $('#addBtn').text('Simpan');
            }
        },
        error: function(xhr) {
            // Tampilkan pesan error saat AJAX gagal
            alert('Terjadi kesalahan saat mengambil data Journal Source Code.');
        }
    });
}

document.addEventListener('click', function (e) {
  if (e.target && e.target.classList.contains('choose-account')) {
    const accountNo = e.target.getAttribute('data-account-no');
    document.getElementById('account_no').value = accountNo; // Set the selected account number
    $('#accountListModal').modal('hide'); // Close the modal
  }
});

function addJournalSina() {
    var code_jgr = $('#code_jgr').val();
    var code_jrc = $('#code_jrc').val();
    var journal_jrc_no = $('#journal_jrc_no').val();
    var cpx = $('#cpx').val();
    var acceptedBy = $('#acceptedBy').val();
    var journal_date = $('#journal_date').val();
    var due_date = $('#due_date').val();

    if (code_jgr === '' || code_jrc === '' || journal_jrc_no === '') {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Journal Group dan Year tidak boleh kosong!!',
            customClass: {
              confirmButton: 'btn btn-danger'
            },
            buttonsStyling: false
        });
        return;
    }        

    $.ajax({
        url: '{{ route('journalSina.add') }}', // Route for saving data
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            code_jgr: code_jgr,
            code_jrc: code_jrc,
            journal_jrc_no: journal_jrc_no,
            cpx: cpx,
            acceptedBy: acceptedBy,
            journal_date: journal_date,
            due_date: due_date
        },
        success: function(response) {            

            // $('#journal_jrc_no').val(response.journal_jrc_no);
            // Reload table without resetting pagination
            table.ajax.reload(null, false);

            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Data berhasil disimpan!',
                showCancelButton: false,
                confirmButtonText: 'OK',
                customClass: {
                  confirmButton: 'btn btn-primary'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    // resetFormAndButton();
                }
            }); 
        },
        error: function(xhr) {
            var errors = xhr.responseJSON.errors; // Ambil pesan error dari response JSON
            var errorMessages = '';

            // Loop melalui setiap error dan gabungkan menjadi satu pesan
            $.each(errors, function(key, value) {
                errorMessages += value[0] + '<br>';
            });

            Swal.fire({
                icon: 'error',
                title: 'Error',
                html: errorMessages, // Menampilkan pesan error dalam HTML
                customClass: {
                  confirmButton: 'btn btn-danger'
                },
                buttonsStyling: false
            });
        }
    });
} 

function editJournalSourceCode(id_jsc) {
    $.ajax({
        url: `journalSourceCodeSina/${id_jsc}`, // Fetch user data by ID
        type: 'GET',
        success: function(journalSourceCodeSina) {
            // Populate form fields with the user's data
            $('#code_jgr').val(journalSourceCodeSina.code_jgr).trigger('change');
            $('#deb_cre').val(journalSourceCodeSina.deb_cre);
            $('#year').val(journalSourceCodeSina.year);
            $('#code_jrc').val(journalSourceCodeSina.code_jrc).focus();
            $('#journal_jrc_no').val(journalSourceCodeSina.journal_jrc_no);
            $('#account_no').val(journalSourceCodeSina.account_no);
            $('#account_name').val(journalSourceCodeSina.account_name);

            // Change background color to yellow
            $('#deb_cre, #year, #code_jrc, #journal_jrc_no, #account_no, #account_name').css('background-color', '#FFFF99');
            $('#code_jgr').css('background', '#FFFF99');

            // Change the form action to update the user and the button text
            $('#formAuthentication').attr('action', `journalSourceCodeSina/update/${id_jsc}`);
            $('#formTitle').text('Edit Form');
            $('#addBtn').text('Edit');

            $('#formMethod').val('PUT');

            // Change the button's click event to trigger an update instead of a create
            $('#addBtn').off('click').on('click', function(event) {
                event.preventDefault();
                updateJournalSourceCode(id_jsc);
            });
        },
        error: function(xhr) {
            alert('Terjadi kesalahan saat mengambil data Journal Source Code.');
        }
    });
}

function updateJournalSourceCode(id_jsc) {
    var code_jgr = $('#code_jgr').val();
    var deb_cre = $('#deb_cre').val();
    var year = $('#year').val();
    var code_jrc = $('#code_jrc').val();
    var journal_jrc_no = $('#journal_jrc_no').val();
    var account_no = $('#account_no').val();
    var account_name = $('#account_name').val();

    $.ajax({
        url: `journalSourceCodeSina/update/${id_jsc}`, // Update route
        type: 'POST',  // Use POST method
        data: {
            _method: 'PUT',  // Spoof method to PUT
            _token: '{{ csrf_token() }}',
            code_jgr: code_jgr,
            deb_cre: deb_cre,
            year: year,
            code_jrc: code_jrc,
            journal_jrc_no: journal_jrc_no,
            account_no: account_no,
            account_name: account_name
            
        },
        success: function(journalSourceCodeSina) {
            // Reset form fields
            $('#code_jrc').val('').focus();
            $('#journal_jrc_no').val('');
            $('#account_no').val('');
            $('#account_name').val('');            

            // Reload table and revert button text
            table.ajax.reload(null, false);
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Data berhasil diubah!',
                customClass: {
                  confirmButton: 'btn btn-primary'
                },
                buttonsStyling: false
            });
            resetFormAndButton();
        }
        
    });
}

function resetFormAndButton() {
    $('#code_jgr').css('background-color', '');
    $('#deb_cre').css('background-color', '');
    $('#year').css('background-color', '');
    $('#code_jrc').val('').css('background-color', '').focus();
    $('#journal_jrc_no').val('').css('background-color', '');
    $('#account_no').val('').css('background-color', '');
    $('#account_name').val('').css('background-color', '');    

    $('#formTitle').text('Add Form');
    $('#addBtn').text('Save'); // Change button text back to "Save"
    $('#formMethod').val('POST'); // Reset form method to POST

    // Reset button click event to add new user
    $('#addBtn').off('click').on('click', function(event) {
        event.preventDefault();
        addJournalSina();
    });
}

// Confirm delete function
function confirmDelete(id_jsc) {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: 'Data ini akan dihapus!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Tutup',
        customClass: {
            confirmButton: 'btn btn-danger',
            cancelButton: 'btn btn-secondary'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            deleteAccountType(id_jsc);
        }
    });
}

function deleteAccountType(id_jsc) {
    $.ajax({
        url: `journalSourceCodeSina/delete/${id_jsc}`,
        type: 'DELETE',
        data: {
            _token: '{{ csrf_token() }}',
        },
        success: function(response) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Data berhasil dihapus.',
                customClass: {
                    confirmButton: 'btn btn-primary'
                },
                buttonsStyling: false
            });
            table.ajax.reload(null, false); // Reload the table
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Terjadi kesalahan saat menghapus data.',
                customClass: {
                    confirmButton: 'btn btn-danger'
                },
                buttonsStyling: false
            });
        }
    });
}    

// Event listener untuk mendeteksi shortcut keyboard ( menggunakan shorcut keyboard ctrl+K)
// document.addEventListener('keydown', function(event) {
//     // Contoh: tekan Ctrl + K untuk klik tombol
//     if (event.ctrlKey && event.key === 'k') {
//         event.preventDefault(); // Mencegah aksi default Ctrl + K di browser
//         resetFormAndButton();
//     }
// });
</script>
 @stop