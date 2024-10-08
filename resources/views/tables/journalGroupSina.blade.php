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
                <div class="col-md-2">
                    <label class="form-label" for="basic-default-code_jgr">Code</label>                
                    <input type="text" class="form-control" id="code_jgr" name="code_jgr" placeholder="Code" autofocus="autofocus" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="basic-default-description_jgr">Description</label>
                    <input type="text" class="form-control" id="description_jgr" name="description_jgr" placeholder="Description">                    
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="basic-default-deb_cre">Debit/Credit</label>
                    <input type="text" class="form-control" id="deb_cre" name="deb_cre" placeholder="Debit/Credit">                    
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
            <table id="journalGroupSina_dt" class="datatables-ajax table">
              <thead>
              <tr>
                <th width="15px">No.</th>
                <th>Code</th>
                <th>Description</th>
                <th>Debit/Credit</th>
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
        table = $('#journalGroupSina_dt').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{!! route('journalGroupSina.data') !!}', // Memanggil route yang menampilkan data JSON
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'code_jgr', name: 'code_jgr' },
            { data: 'description_jgr', name: 'description_jgr' },
            { data: 'deb_cre', name: 'deb_cre' },
            {
                data: 'action',
                render: function (data, type, row, meta) {
                    return (
                        `<a href="javascript:;" onclick="editJournalGroup(${row.id_jgr})" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip-warning" title="Edit" class="btn btn-sm btn-icon item-edit"><i class="text-warning ti ti-pencil"></i></a>` +
                        `<a href="javascript:;" onclick="confirmDelete(${row.id_jgr})" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip-success" title="Delete" class="btn btn-sm btn-icon"><i class="text-success ti ti-trash"></i></a>`
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
        addJournalGroup(); // Call add function to handle the add operation
    });

    $('#clear').on('click', function(event) {
        resetFormAndButton();
    });

});

function addJournalGroup() {
    var code_jgr = $('#code_jgr').val();
    var description_jgr = $('#description_jgr').val();
    var deb_cre = $('#deb_cre').val();

    if (code_jgr === '' || description_jgr === '') {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Code dan Description tidak boleh kosong!!',
            customClass: {
              confirmButton: 'btn btn-danger'
            },
            buttonsStyling: false
        });
        return;
    }        

    $.ajax({
        url: '{{ route('journalGroupSina.add') }}', // Route for saving data
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            code_jgr: code_jgr,
            description_jgr: description_jgr,
            deb_cre: deb_cre
        },
        success: function(response) {
            // Reset input fields
            $('#code_jgr').val('');
            $('#description_jgr').val('');  
            $('#deb_cre').val('');      

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

function editJournalGroup(id_jgr) {
    $.ajax({
        url: `journalGroupSina/${id_jgr}`, // Fetch user data by ID
        type: 'GET',
        success: function(journalGroupSina) {
            // Populate form fields with the user's data
            $('#code_jgr').val(journalGroupSina.code_jgr).focus();
            $('#description_jgr').val(journalGroupSina.description_jgr);
            $('#deb_cre').val(journalGroupSina.deb_cre);

            // Change background color to yellow
            $('#code_jgr, #description_jgr, #deb_cre').css('background-color', '#FFFF99');

            // Change the form action to update the user and the button text
            $('#formAuthentication').attr('action', `journalGroupSina/update/${id_jgr}`);
            $('#formTitle').text('Edit Form');
            $('#addBtn').text('Edit');

            $('#formMethod').val('PUT');

            // Change the button's click event to trigger an update instead of a create
            $('#addBtn').off('click').on('click', function(event) {
                event.preventDefault();
                updateJournalGroup(id_jgr);
            });
        },
        error: function(xhr) {
            alert('Terjadi kesalahan saat mengambil data Journal Group.');
        }
    });
}

function updateJournalGroup(id_jgr) {
    var code_jgr = $('#code_jgr').val();
    var description_jgr = $('#description_jgr').val();
    var deb_cre = $('#deb_cre').val();

    $.ajax({
        url: `journalGroupSina/update/${id_jgr}`, // Update route
        type: 'POST',  // Use POST method
        data: {
            _method: 'PUT',  // Spoof method to PUT
            _token: '{{ csrf_token() }}',
            code_jgr: code_jgr,
            description_jgr: description_jgr,
            deb_cre: deb_cre
        },
        success: function(journalGroupSina) {
            // Reset form fields
            $('#code_jgr').val('');
            $('#description_jgr').val('');
            $('#deb_cre').val('');

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
    $('#code_jgr').val('').css('background-color', '').focus();
    $('#description_jgr').val('').css('background-color', '');
    $('#deb_cre').val('').css('background-color', '');

    $('#formTitle').text('Add Form');
    $('#addBtn').text('Save'); // Change button text back to "Save"
    $('#formMethod').val('POST'); // Reset form method to POST

    // Reset button click event to add new user
    $('#addBtn').off('click').on('click', function(event) {
        event.preventDefault();
        addJournalGroup();
    });
}

// Confirm delete function
function confirmDelete(id_jgr) {
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
            deleteJournalGroup(id_jgr);
        }
    });
}

function deleteJournalGroup(id_jgr) {
    $.ajax({
        url: `journalGroupSina/delete/${id_jgr}`,
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