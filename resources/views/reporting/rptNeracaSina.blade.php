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
            <h5 class="mb-0" id="formTitle">Neraca</h5>            
          </div>
          
            <form id="formAuthentication" class="card-body form-horizontal">
              @csrf
              <!-- Tambahkan metode spoof PUT untuk update, diatur secara dinamis dengan JS -->
              <input type="hidden" name="_method" id="formMethod" value="POST">
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label" for="basic-default-name">Year</label>
                <div class="col-sm-2 d-flex align-items-center">
                    <input type="number" class="form-control text-center" id="year" name="year" placeholder="YYYY" min="1900" max="2100" style="width: 50%;">
                </div>                
              </div>                           
              <div class="pt-1">
                  <button type="button" id="rptDisp" class="btn btn-success">Display</button>
                  <button type="button" id="rptXls" class="btn btn-warning">XLS</button>
              </div>                                               
            </form>            
          
        </div>
      </div>       
    </div>
    
    

    <!-- Modal HTML -->
    <div class="modal fade" id="modRptDisp" tabindex="-1" aria-labelledby="modRptDisp" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 90%;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rptDispLabel">Reporting Neraca</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modalBodyContent">
                    <div class="card">
                      <!-- /.card-header -->
                      <div class="card-datatable text-nowrap">
                        
                      </div>
                    </div>                    
                </div>
            </div>
        </div>
    </div>
    
@stop
@section('custom-js')
<script type="text/javascript">
$(document).ready(function() {
    // Validasi input untuk bulan
    $('#month').on('input', function () {
        const month = $(this).val();
        
        // Menghapus karakter non-angka
        $(this).val(month.replace(/[^0-9]/g, ''));

        // Batasi nilai agar tetap dalam rentang 1-12
        if (month > 12) {
            $(this).val('');
        }
    });

    // Validasi input untuk tahun
     $('#year').on('input', function () {
        let year = $(this).val();

        // Menghapus karakter non-angka
        year = year.replace(/[^0-9]/g, '');

        // Periksa apakah panjang tahun melebihi 4 digit
        if (year.length > 4) {
            year = year.substring(0, 4);
        }

        // Tetapkan kembali nilai yang sudah tervalidasi
        $(this).val(year);

        // Jika tahun melebihi range valid, biarkan pengguna selesai mengetik sebelum mengosongkannya
        if (year.length === 4 && (year < 1900 || year > 2100)) {
            alert('Year must be between 1900 and 2100');
            $(this).val('');
        }
    });    

    $('#month').on('input', function(event) {
        event.preventDefault();
        month = $('#month').val();
        year = $('#year').val();
         
        setPeriode(month,year);
        
    });

    $('#year').on('input', function(event) {
        event.preventDefault();
        month = $('#month').val();
        year = $('#year').val();
         
        setPeriode(month,year);
        
    });

    $('#rptDisp').on('click', function () {
        year = $('#year').val();
        if (!year) {
            year = '0000';
        }
        
        $.ajax({
            url: `rptNeracaSinaModal/${year}`, // Use your route name
            method: 'GET',
            success: function (data) {
                // Inject the content into the modal body
                $('#modalBodyContent').html(data);
                // Show the modal
                $('#modRptDisp').modal('show');
            },
            error: function (xhr, status, error) {
                // Handle errors (optional)
                $('#modalBodyContent').html('<p>Error loading content: ' + error + '</p>');
            }
        });
    });

    $('#rptXls').on('click', function () {
        m_date = $('#month').val();
        if (!m_date) {
            m_date = '00';
        }
        y_date = $('#year').val();
        if (!y_date) {
            y_date = '0000';
        }
        acc_no = $('#account_no').val();
        if (!acc_no) {
            acc_no = '0';
        }
        acc_no_end = $('#account_no_end').val();
        if (!acc_no_end) {
            acc_no_end = '9999.9999';
        }
        
        code_div = $('#code_div').val();
        if (!code_div) {
            code_div = '0';
        }

        $.ajax({
            url: `rptNeracaSinaXls/${year}`,
            method: 'GET',
            success: function () {
                const fileUrl = `rptNeracaSinaXls/${year}`;
                window.location = fileUrl;
            },
            error: function (xhr, status, error) {
                $('#modalBodyContent').html(`<p>Error loading content: ${error}</p>`);
            }
        });
    });

    $('#modAccList').on('shown.bs.modal', function () {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
    });

    $('#modDivList').on('shown.bs.modal', function () {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
    });

});

function setPeriode(month,year) {
    $.ajax({
        url: `rptNeracaSina/setPeriode/${month}/${year}`, // Fetch data by code
        type: 'GET',
        success: function(response) {
            if (response.status === 'success') {
                const data = response.data;

                // Isi nilai ke input form
                $('#start_date').val(data.start_date);
                $('#end_date').val(data.end_date);                

            } else {
                // Tampilkan pesan error dari respons server
                alert('Terjadi kesalahan saat mengambil data Journal Source Code.');
            }
        },
        error: function(xhr) {
            
            // alert('Terjadi kesalahan saat mengambil data Journal Source Code.');
        }
    });
}


function getAccountDetail(account_no, account_name) {
    // Isi field 'account_no' dan 'account_name' di form
    $('#account_no').val(account_no);
    $('#account_name').val(account_name);

    // Tutup modal menggunakan Bootstrap modal API
    $('#modAccList').modal('hide');
}
function getAccountDetailEnd(account_no, account_name) {
    // Isi field 'account_no' dan 'account_name' di form
    $('#account_no_end').val(account_no);

    // Tutup modal menggunakan Bootstrap modal API
    $('#modAccListEnd').modal('hide');
}

function getDivDetail(code, division_name) {
    // Isi field 'account_no' dan 'account_name' di form
    $('#code_div').val(code);
    $('#division_name').val(division_name);

    // Tutup modal menggunakan Bootstrap modal API
    $('#modDivList').modal('hide');
}

document.addEventListener('click', function (e) {
  if (e.target && e.target.classList.contains('choose-account')) {
    const accountNo = e.target.getAttribute('data-account-no');
    document.getElementById('account_no').value = accountNo; // Set the selected account number
    $('#accountListModal').modal('hide'); // Close the modal
  }
});

</script>
 @stop