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
                    <label class="form-label" for="basic-default-code">Code</label>                
                    <input type="text" class="form-control" id="code" name="code" placeholder="Code" autofocus="autofocus" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="basic-default-division">Division</label>
                    <input type="text" class="form-control" id="division_name" name="division_name" placeholder="Division Name">                    
                </div>              
                <div class="col-md-6">
                    <label class="form-label" for="basic-default-category">Category</label>             
                    <input type="text" class="form-control" id="category" name="category" placeholder="Category" autofocus="autofocus" required>
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
            <table id="divisionListSina_dt" class="datatables-ajax table">
              <thead>
              <tr>
                <th width="15px">No.</th>
                <th>Code</th>
                <th>Division</th>
                <th>Category</th>
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
        table = $('#divisionListSina_dt').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{!! route('divisionListSina.data') !!}', // Memanggil route yang menampilkan data JSON
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'code', name: 'code' },
            { data: 'division_name', name: 'division_name' },
            { data: 'category', name: 'category' },
            {
                data: 'action',
                render: function (data, type, row, meta) {
                    return (
                        `<a href="javascript:;" onclick="editDivisionList(${row.id_division})" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip-warning" title="Edit" class="btn btn-sm btn-icon item-edit"><i class="text-warning ti ti-pencil"></i></a>` +
                        `<a href="javascript:;" onclick="confirmDelete(${row.id_division})" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip-success" title="Delete" class="btn btn-sm btn-icon"><i class="text-success ti ti-trash"></i></a>`
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
        addDivisionList(); // Call add function to handle the add operation
    });

    $('#clear').on('click', function(event) {
        resetFormAndButton();
    });

});

function addDivisionList() {
    var code = $('#code').val();
    var division_name = $('#division_name').val();    
    var category = $('#category').val();

    if (code === '' || division_name === '') {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Code dan Division Name tidak boleh kosong!!',
            customClass: {
              confirmButton: 'btn btn-danger'
            },
            buttonsStyling: false
        });
        return;
    }        

    $.ajax({
        url: '{{ route('divisionListSina.add') }}', // Route for saving data
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            code: code,
            division_name: division_name,
            category: category
        },
        success: function(response) {
            // Reset input fields
            $('#code').val('');
            $('#division_name').val('');
            $('#category').val('');            

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

function editDivisionList(id_division) {
    $.ajax({
        url: `divisionListSina/${id_division}`, // Fetch user data by ID
        type: 'GET',
        success: function(divisionListSina) {
            // Populate form fields with the user's data
            $('#code').val(divisionListSina.code).focus();
            $('#division_name').val(divisionListSina.division_name);
            $('#category').val(divisionListSina.category);

            // Change background color to yellow
            $('#code, #division_name, #category').css('background-color', '#FFFF99');

            // Change the form action to update the user and the button text
            $('#formAuthentication').attr('action', `divisionListSina/update/${id_division}`);
            $('#formTitle').text('Edit Form');
            $('#addBtn').text('Edit');

            $('#formMethod').val('PUT');

            // Change the button's click event to trigger an update instead of a create
            $('#addBtn').off('click').on('click', function(event) {
                event.preventDefault();
                updateDivision(id_division);
            });
        },
        error: function(xhr) {
            alert('Terjadi kesalahan saat mengambil data Division List.');
        }
    });
}

function updateDivision(id_division) {
    var code = $('#code').val();
    var division_name = $('#division_name').val();
    var category = $('#category').val();

    $.ajax({
        url: `divisionListSina/update/${id_division}`, // Update route
        type: 'POST',  // Use POST method
        data: {
            _method: 'PUT',  // Spoof method to PUT
            _token: '{{ csrf_token() }}',
            code: code,
            division_name: division_name,
            category: category
        },
        success: function(divisionListSina) {
            // Reset form fields
            $('#code').val('');
            $('#division_name').val('');
            $('#category').val('');

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
    $('#code').val('').css('background-color', '').focus();
    $('#division_name').val('').css('background-color', '');
    $('#category').val('').css('background-color', '');

    $('#formTitle').text('Add Form');
    $('#addBtn').text('Save'); // Change button text back to "Save"
    $('#formMethod').val('POST'); // Reset form method to POST

    // Reset button click event to add new user
    $('#addBtn').off('click').on('click', function(event) {
        event.preventDefault();
        addDivisionList();
    });
}

// Confirm delete function
function confirmDelete(id_division) {
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
            deleteDivisionType(id_division);
        }
    });
}

function deleteDivisionType(id_division) {
    $.ajax({
        url: `divisionListSina/delete/${id_division}`,
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