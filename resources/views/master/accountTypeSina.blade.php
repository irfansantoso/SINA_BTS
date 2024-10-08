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
          <div class="card-body">
            <form id="formAuthentication" class="form-horizontal">
              @csrf
              <!-- Tambahkan metode spoof PUT untuk update, diatur secara dinamis dengan JS -->
              <input type="hidden" name="_method" id="formMethod" value="POST">
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label" for="basic-default-accno">Account No.</label>
                <div class="col-sm-2">
                  <input type="text" class="form-control" id="acc_no" name="acc_no" placeholder="Account No." autofocus="autofocus" required>
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label" for="basic-default-accname">Account Name</label>
                <div class="col-sm-5">
                  <input type="text" class="form-control" id="acc_name" name="acc_name" placeholder="Account Name">
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label" for="basic-default-acctype">Account Type</label>
                <div class="col-sm-5">
                  <input type="text" class="form-control" id="acc_type" name="acc_type" placeholder="Account Type">
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label" for="basic-default-accdesc">Account Desc</label>
                <div class="col-sm-5">
                  <input type="text" class="form-control" id="acc_desc" name="acc_desc" placeholder="Account Desc">
                </div>
              </div>
              <div class="row justify-content-end">
                <div class="col-sm-10">
                  <button type="button" id="addBtn" class="btn btn-success">Simpan</button>
                  <button type="button" id="clear" class="btn btn-warning">Clear</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>      

      <div class="col-xxl">
        <div class="card mb-4">
          <div class="card-datatable table-responsive">
            <table id="accountTypeSina_dt" class="datatables table border-top">
              <thead>
              <tr>
                <th width="15px">No.</th>
                <th>Account No</th>
                <th>Account Name</th>
                <th>Type</th>
                <th>Desc</th>
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
        table = $('#accountTypeSina_dt').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{!! route('accountTypeSina.data') !!}', // Memanggil route yang menampilkan data JSON
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex' },
            { data: 'acc_no', name: 'acc_no' },
            { data: 'acc_name', name: 'acc_name' },
            { data: 'acc_type', name: 'acc_type' },
            { data: 'acc_desc', name: 'acc_desc' },
            {
                data: 'action',
                render: function (data, type, row, meta) {
                    return (
                        `<a href="javascript:;" onclick="editAccountType(${row.id})" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip-warning" title="Edit" class="btn btn-sm btn-icon item-edit"><i class="text-warning ti ti-pencil"></i></a>` +
                        `<a href="javascript:;" onclick="confirmDelete(${row.id})" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip-success" title="Delete" class="btn btn-sm btn-icon"><i class="text-success ti ti-trash"></i></a>`
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
    });

    // Handle Add New User Inline
    $('#addBtn').on('click', function(event) {
        event.preventDefault(); // Prevent the form from submitting the traditional way
        addAccountType(); // Call add function to handle the add operation
    });

    $('#clear').on('click', function(event) {
        resetFormAndButton();
    });

});

function addAccountType() {
    var acc_no = $('#acc_no').val();
    var acc_name = $('#acc_name').val();
    var acc_type = $('#acc_type').val();
    var acc_desc = $('#acc_desc').val();

    if (acc_no === '' || acc_name === '') {
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
        url: '{{ route('accountTypeSina.add') }}', // Route for saving data
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            acc_no: acc_no,
            acc_name: acc_name,
            acc_type: acc_type,
            acc_desc: acc_desc
        },
        success: function(user) {
            // Reset input fields
            $('#acc_no').val('');
            $('#acc_name').val('');
            $('#acc_type').val('');
            $('#acc_desc').val('');

            // Reload table without resetting pagination
            table.ajax.reload(null, false);

            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Data berhasil disimpan!',
                customClass: {
                  confirmButton: 'btn btn-primary'
                },
                buttonsStyling: false
            });

            // Revert button text and function to default
            resetFormAndButton();
        },
        error: function(xhr) {
            alert('Terjadi kesalahan saat menambahkan data.');
        }
    });
} 

function editAccountType(id) {
    $.ajax({
        url: `accountTypeSina/${id}`, // Fetch user data by ID
        type: 'GET',
        success: function(accountTypeSina) {
            // Populate form fields with the user's data
            $('#acc_no').val(accountTypeSina.acc_no);
            $('#acc_name').val(accountTypeSina.acc_name);
            $('#acc_type').val(accountTypeSina.acc_type);
            $('#acc_desc').val(accountTypeSina.acc_desc);

            // Change the form action to update the user and the button text
            $('#formAuthentication').attr('action', `accountTypeSina/update/${id}`);
            $('#formTitle').text('Edit Form');
            $('#addBtn').text('Edit');

            $('#formMethod').val('PUT');

            // Change the button's click event to trigger an update instead of a create
            $('#addBtn').off('click').on('click', function(event) {
                event.preventDefault();
                updateUser(id);
            });
        },
        error: function(xhr) {
            alert('Terjadi kesalahan saat mengambil data Account Type.');
        }
    });
}

function updateUser(id) {
    var acc_no = $('#acc_no').val();
    var acc_name = $('#acc_name').val();
    var acc_type = $('#acc_type').val();
    var acc_desc = $('#acc_desc').val();

    $.ajax({
        url: `accountTypeSina/update/${id}`, // Update route
        type: 'POST',  // Use POST method
        data: {
            _method: 'PUT',  // Spoof method to PUT
            _token: '{{ csrf_token() }}',
            acc_no: acc_no,
            acc_name: acc_name,
            acc_type: acc_type,
            acc_desc: acc_desc
        },
        success: function(accountTypeSina) {
            // Reset form fields
            $('#acc_no').val('');
            $('#acc_name').val('');
            $('#acc_type').val('');
            $('#acc_desc').val('');

            // Reload table and revert button text
            table.ajax.reload(null, false);
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Data berhasil disimpan!',
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
    $('#acc_no').val('');
    $('#acc_name').val('');
    $('#acc_type').val('');
    $('#acc_desc').val('');

    $('#formTitle').text('Add Form');
    $('#addBtn').text('Simpan'); // Change button text back to "Simpan"
    $('#formMethod').val('POST'); // Reset form method to POST

    // Reset button click event to add new user
    $('#addBtn').off('click').on('click', function(event) {
        event.preventDefault();
        addAccountType();
    });
}

// Confirm delete function
function confirmDelete(id) {
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
            deleteAccountType(id);
        }
    });
}

function deleteAccountType(id) {
    $.ajax({
        url: `accountTypeSina/delete/${id}`,
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
</script>
 @stop