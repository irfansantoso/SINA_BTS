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
                    <label class="form-label" for="basic-default-year">Year</label>                
                    <input type="text" class="form-control" id="year" name="year" placeholder="Year" autofocus="autofocus" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="basic-default-month">Month</label>
                    <input type="text" class="form-control" id="month" name="month" placeholder="Month">                    
                </div>              
                <div class="col-md-6">
                    <label class="form-label" for="basic-default-start_date">Starting Date</label>             
                    <input type="date" class="form-control" id="start_date" name="start_date">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="basic-default-end_date">End Date</label>             
                    <input type="date" class="form-control" id="end_date" name="end_date">
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
            <table id="accountingPeriodSina_dt" class="datatables-ajax table">
              <thead>
              <tr>
                <th width="15px">No.</th>
                <th>Year</th>
                <th>Month</th>
                <th>Starting Date</th>
                <th>Ending Date</th>
                <th>Status</th>
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
        table = $('#accountingPeriodSina_dt').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{!! route('accountingPeriodSina.data') !!}', // Memanggil route yang menampilkan data JSON
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'year', name: 'year' },
            { data: 'month', name: 'month' },
            { data: 'start_date', name: 'start_date' },
            { data: 'end_date', name: 'end_date' },
            {
                data: 'status_period',
                render: function (data, type, row, meta) {
                    if (row.user_acc_period != null) {
                        return `<span class="text-success">Actived</span>`;
                    } else {
                        return (
                            `<button type="button" onclick="statusAccountingPeriod(${row.id_period})" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip-danger" title="Click Me to Actived" class="btn btn-sm btn-danger">Inactive</button>`
                        );
                    }
                },
                orderable: false, searchable: false
            },
            {
                data: 'action',
                render: function (data, type, row, meta) {
                    return (
                        `<a href="javascript:;" onclick="editAccountingPeriod(${row.id_period})" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip-warning" title="Edit" class="btn btn-sm btn-icon item-edit"><i class="text-warning ti ti-pencil"></i></a>` +
                        `<a href="javascript:;" onclick="confirmDelete(${row.id_period})" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip-success" title="Delete" class="btn btn-sm btn-icon"><i class="text-success ti ti-trash"></i></a>`
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
        addAccountingPeriod(); // Call add function to handle the add operation
    });

    $('#clear').on('click', function(event) {
        resetFormAndButton();
    });

});

function addAccountingPeriod() {
    var year = $('#year').val();
    var month = $('#month').val();    
    var start_date = $('#start_date').val();
    var end_date = $('#end_date').val();

    if (year === '' || month === '' || start_date === '' || end_date === '') {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Year, Month, starting date dan ending date tidak boleh kosong!!',
            customClass: {
              confirmButton: 'btn btn-danger'
            },
            buttonsStyling: false
        });
        return;
    }        

    $.ajax({
        url: '{{ route('accountingPeriodSina.add') }}', // Route for saving data
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            year: year,
            month: month,
            start_date: start_date,
            end_date: end_date
        },
        success: function(response) {
            // Reset input fields
            // $('#year').val('');
            $('#month').val('').focus();
            $('#start_date').val('');
            $('#end_date').val('');        

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
                    resetFormAndButton_add();
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

function editAccountingPeriod(id_period) {
    $.ajax({
        url: `accountingPeriodSina/${id_period}`, // Fetch user data by ID
        type: 'GET',
        success: function(accountingPeriodSina) {
            // Populate form fields with the user's data
            $('#year').val(accountingPeriodSina.year).focus();
            $('#month').val(accountingPeriodSina.month);
            $('#start_date').val(accountingPeriodSina.start_date);
            $('#end_date').val(accountingPeriodSina.end_date);

            // Change background color to yellow
            $('#year, #month, #start_date').css('background-color', '#FFFF99');

            // Change the form action to update the user and the button text
            $('#formAuthentication').attr('action', `accountingPeriodSina/update/${id_period}`);
            $('#formTitle').text('Edit Form');
            $('#addBtn').text('Edit');

            $('#formMethod').val('PUT');

            // Change the button's click event to trigger an update instead of a create
            $('#addBtn').off('click').on('click', function(event) {
                event.preventDefault();
                updateAccountingPeriod(id_period);
            });
        },
        error: function(xhr) {
            alert('Terjadi kesalahan saat mengambil data Cost List.');
        }
    });
}

function updateAccountingPeriod(id_period) {
    var year = $('#year').val();
    var month = $('#month').val();
    var start_date = $('#start_date').val();
    var end_date = $('#end_date').val();

    $.ajax({
        url: `accountingPeriodSina/update/${id_period}`, // Update route
        type: 'POST',  // Use POST method
        data: {
            _method: 'PUT',  // Spoof method to PUT
            _token: '{{ csrf_token() }}',
            year: year,
            month: month,
            start_date: start_date,
            end_date: end_date
        },
        success: function(accountingPeriodSina) {
            // Reset form fields
            $('#year').val('');
            $('#month').val('');
            $('#start_date').val('');
            $('#end_date').val('');

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

function resetFormAndButton_add() {
    $('#month').val('').css('background-color', '').focus();
    $('#start_date').val('').css('background-color', '');
    $('#end_date').val('').css('background-color', '');

    $('#formTitle').text('Add Form');
    $('#addBtn').text('Save'); // Change button text back to "Save"
    $('#formMethod').val('POST'); // Reset form method to POST

    // Reset button click event to add new user
    $('#addBtn').off('click').on('click', function(event) {
        event.preventDefault();
        addAccountingPeriod();
    });
}

function resetFormAndButton() {
    $('#year').val('').css('background-color', '').focus();
    $('#month').val('').css('background-color', '');
    $('#start_date').val('').css('background-color', '');
    $('#end_date').val('').css('background-color', '');

    $('#formTitle').text('Add Form');
    $('#addBtn').text('Save'); // Change button text back to "Save"
    $('#formMethod').val('POST'); // Reset form method to POST

    // Reset button click event to add new user
    $('#addBtn').off('click').on('click', function(event) {
        event.preventDefault();
        addAccountingPeriod();
    });
}

function statusAccountingPeriod(id_period) {

    $.ajax({
        url: `accountingPeriodSina/updateStatus/${id_period}`, // Update route
        type: 'POST',  // Use POST method
        data: {
            _method: 'PUT',  // Spoof method to PUT
            _token: '{{ csrf_token() }}'
        },
        success: function(accountingPeriodSina) {
            // Reset form fields
            $('#year').val('');
            $('#month').val('');
            $('#start_date').val('');
            $('#end_date').val('');

            // Reload table and revert button text
            table.ajax.reload(null, false);
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Data berhasil diactive-kan!',
                customClass: {
                  confirmButton: 'btn btn-primary'
                },
                buttonsStyling: false
            });
            resetFormAndButton();
        }
        
    });
}

// Confirm delete function
function confirmDelete(id_period) {
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
            deleteAccountingPeriod(id_period);
        }
    });
}

function deleteAccountingPeriod(id_period) {
    $.ajax({
        url: `accountingPeriodSina/delete/${id_period}`,
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