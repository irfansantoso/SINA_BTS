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
            <h5 class="mb-0" id="formTitle">Add Form</h5>            
          </div>
          
            <form id="formAuthentication" class="card-body form-horizontal">
              @csrf
              <!-- Tambahkan metode spoof PUT untuk update, diatur secara dinamis dengan JS -->
              <input type="hidden" name="_method" id="formMethod" value="POST">
              <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="basic-default-accountno">Account No.</label>                
                    <input type="text" class="form-control" id="account_no" name="account_no" placeholder="Account No." autofocus="autofocus" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="basic-default-accountname">Account Name</label>
                    <input type="text" class="form-control" id="account_name" name="account_name" placeholder="Account Name">                    
                </div>              
                <div class="col-md-6">
                    <label class="form-label" for="basic-default-type">Type</label>             
                    <select class="form-control select2 form-select" name="type" id="type" data-allow-clear="true">
                        <option value="" selected="selected">-- Type --</option>
                        @foreach ($accTypeSina as $ats)
                        <option value="{{ $ats->acc_no }}">{{ $ats->acc_no }} - {{ $ats->acc_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="basic-default-level">Level</label>
                    <input type="text" class="form-control" id="level" name="level" placeholder="Level">                    
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="basic-default-category">Category</label>             
                    <input type="text" class="form-control" id="category" name="category" placeholder="Category" autofocus="autofocus" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="basic-default-report">Report</label>
                    <input type="text" class="form-control" id="report" name="report" placeholder="Report">                    
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="basic-default-generalaccount">General Account</label>                    
                    <select class="form-control select2 form-select" name="general_account" id="general_account" data-allow-clear="true">
                        <option value="" selected="selected">-- Type --</option>
                        @foreach ($accListSina as $als)
                        <option value="{{ $als->account_no }}">{{ $als->account_no }} - {{ $als->account_name }}</option>
                        @endforeach
                    </select>                    
                </div>
              </div>              
              <div class="pt-4">
                  <button type="button" id="addBtn" class="btn btn-success">Save</button>
                  <button type="button" id="clear" class="btn btn-warning">Clear</button>
              </div>
            </form>
          
        </div>
      </div>      

      <div class="col-xxl">
        <div class="card mb-4">
          <div class="card-datatable text-nowrap">
            <table id="accountListSina_dt" class="datatables-ajax table">
              <thead>
              <tr>
                <th width="15px">No.</th>
                <th>Account No</th>
                <th>Account Name</th>
                <th>Type</th>
                <th>Level</th>
                <th>Category</th>
                <th>Report</th>
                <th>General Account</th>
                <th>Description</th>
                <th>Action</th>
              </tr>
              </thead>              
            </table>
          </div>
        </div>
      </div>
    </div>
@stop
@section('custom-js')
<script type="text/javascript">    
var table; // Declare table variable in global scope
$(document).ready(function() {
        table = $('#accountListSina_dt').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{!! route('accountListSina.data') !!}', // Memanggil route yang menampilkan data JSON
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'account_no', name: 'account_no' },
            { data: 'account_name', name: 'account_name' },
            { data: 'typeName', name: 'typeName' },
            { data: 'level', name: 'level' },
            { data: 'category', name: 'category' },
            { data: 'report', name: 'report' },
            { data: 'general_account', name: 'general_account' },
            { data: 'description', name: 'description' },
            {
                data: 'action',
                render: function (data, type, row, meta) {
                    return (
                        `<a href="javascript:;" onclick="editAccountList(${row.id_acc_list})" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip-warning" title="Edit" class="btn btn-sm btn-icon item-edit"><i class="text-warning ti ti-pencil"></i></a>` +
                        `<a href="javascript:;" onclick="confirmDelete(${row.id_acc_list})" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip-success" title="Delete" class="btn btn-sm btn-icon"><i class="text-success ti ti-trash"></i></a>`
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

    // Handle Add New User Inline
    $('#addBtn').on('click', function(event) {
        event.preventDefault(); // Prevent the form from submitting the traditional way
        addAccountList(); // Call add function to handle the add operation
    });

    $('#clear').on('click', function(event) {
        resetFormAndButton();
    });

});

function addAccountList() {
    var account_no = $('#account_no').val();
    var account_name = $('#account_name').val();
    var type = $('#type').val();
    var level = $('#level').val();
    var category = $('#category').val();
    var report = $('#report').val();
    var general_account = $('#general_account').val();

    if (account_no === '' || account_name === '') {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Account No dan Account Name tidak boleh kosong!!',
            customClass: {
              confirmButton: 'btn btn-danger'
            },
            buttonsStyling: false
        });
        return;
    }        

    $.ajax({
        url: '{{ route('accountListSina.add') }}', // Route for saving data
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            account_no: account_no,
            account_name: account_name,
            type: type,
            level: level,
            category: category,
            report: report,
            general_account: general_account
        },
        success: function(response) {
            // Reset input fields
            $('#account_no').val('');
            $('#account_name').val('');
            $('#type').val('').trigger('change');
            $('#level').val('');
            $('#category').val('');
            $('#report').val('');
            $('#general_account').val('').trigger('change');


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
                    resetFormAndButton();
                }
            }); 

            // Revert button text and function to default
            // resetFormAndButton();
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

function editAccountList(id_acc_list) {
    $.ajax({
        url: `accountListSina/${id_acc_list}`, // Fetch user data by ID
        type: 'GET',
        success: function(accountListSina) {
            // Populate form fields with the user's data
            $('#account_no').val(accountListSina.account_no).focus();
            $('#account_name').val(accountListSina.account_name);
            $('#type').val(accountListSina.type).trigger('change');
            $('#level').val(accountListSina.level);
            $('#category').val(accountListSina.category);
            $('#report').val(accountListSina.report);
            $('#general_account').val(accountListSina.general_account).trigger('change');

            // Change background color to yellow
            $('#account_no, #account_name, #type, #level, #category, #report, #general_account').css('background-color', '#FFFF99');
            $('#type, #general_account').css('background', '#FFFF99');

            // Change the form action to update the user and the button text
            $('#formAuthentication').attr('action', `accountListSina/update/${id_acc_list}`);
            $('#formTitle').text('Edit Form');
            $('#addBtn').text('Edit');

            $('#formMethod').val('PUT');

            // Change the button's click event to trigger an update instead of a create
            $('#addBtn').off('click').on('click', function(event) {
                event.preventDefault();
                updateUser(id_acc_list);
            });
        },
        error: function(xhr) {
            alert('Terjadi kesalahan saat mengambil data Account List.');
        }
    });
}

function updateUser(id_acc_list) {
    var account_no = $('#account_no').val();
    var account_name = $('#account_name').val();
    var type = $('#type').val();
    var level = $('#level').val();
    var category = $('#category').val();
    var report = $('#report').val();
    var general_account = $('#general_account').val();

    $.ajax({
        url: `accountListSina/update/${id_acc_list}`, // Update route
        type: 'POST',  // Use POST method
        data: {
            _method: 'PUT',  // Spoof method to PUT
            _token: '{{ csrf_token() }}',
            account_no: account_no,
            account_name: account_name,
            type: type,
            level: level,
            category: category,
            report: report,
            general_account: general_account
        },
        success: function(accountListSina) {
            // Reset form fields
            $('#account_no').val('');
            $('#account_name').val('');
            $('#type').val('').trigger('change');
            $('#level').val('');
            $('#category').val('');
            $('#report').val('');
            $('#general_account').val('').trigger('change');

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
    $('#account_no').val('').css('background-color', '').focus();
    $('#account_name').val('').css('background-color', '');
    $('#type').val('').css('background-color', '').trigger('change');
    $('#level').val('').css('background-color', '');
    $('#category').val('').css('background-color', '');
    $('#report').val('').css('background-color', '');
    $('#general_account').val('').css('background-color', '').trigger('change');

    $('#formTitle').text('Add Form');
    $('#addBtn').text('Save'); // Change button text back to "Save"
    $('#formMethod').val('POST'); // Reset form method to POST

    // Reset button click event to add new user
    $('#addBtn').off('click').on('click', function(event) {
        event.preventDefault();
        addAccountList();
    });
}

// Confirm delete function
function confirmDelete(id_acc_list) {
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
            deleteAccountType(id_acc_list);
        }
    });
}

function deleteAccountType(id_acc_list) {
    $.ajax({
        url: `accountListSina/delete/${id_acc_list}`,
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