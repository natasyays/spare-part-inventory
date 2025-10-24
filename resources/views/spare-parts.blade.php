<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spare Parts Inventory</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    
    <style>
    .status-safe { color: #198754 !important; background-color: transparent !important; }
    .status-critical { color: #dc3545 !important; background-color: transparent !important; }
    #partsTable td, #partsTable th { vertical-align: middle; text-align: center; }
    .select2-container .select2-selection--single { height: 38px; padding: 5px 10px; }
    </style>
</head>

<body class="p-4">
    <h3 class="mb-3">Spare Parts List Inventory</h3>

    <!-- modal -->
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#usageModal">
    Record Spare Part Usage
    </button>

    <!-- input search n filter field -->
    <div class="card p-3 mb-3">
        <div class="row g-2">
        <div class="col-md-4">
            <input type="text" id="searchText" class="form-control" placeholder="Search Part Name / Code">
        </div>
        <div class="col-md-3">
            <select id="categoryFilter" class="form-select">
            <option value="">All Categories</option>
            </select>
        </div>
        <div class="col-md-3">
            <select id="statusFilter" class="form-select">
            <option value="">All Status</option>
            <option value="safe">Safe</option>
            <option value="critical">Critical</option>
            </select>
        </div>
        </div>
    </div>

    <div id="loading" class="text-center my-3">
        <div class="spinner-border text-primary"></div>
        <p>Loading data</p>
    </div>

    <!-- table inventory form -->
    <table class="table table-bordered table-striped" id="partsTable">
        <thead class="table-light">
        <tr>
            <th>Part Code</th>
            <th>Part Name</th>
            <th>Current Stock</th>
            <th>Minimum Stock</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        <tr><td colspan="5" class="text-center">Tidak ada data yang masuk</td></tr>
        </tbody>
    </table>

    <!-- modal form -->
    <div class="modal fade" id="usageModal" tabindex="-1" aria-labelledby="usageModalLabel" aria-hidden="true">
        <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title" id="usageModalLabel">Record Spare Part Usage</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            <form id="usageForm">
                <div class="mb-3">
                <label for="machine_id" class="form-label">Select Machine</label>
                <select id="machine_id" class="form-select select2" required>
                    <option value="">-- Pilih Mesin --</option>
                </select>
                </div>
                <div class="mb-3">
                <label for="spare_part_id" class="form-label">Select Spare Part</label>
                <select id="spare_part_id" class="form-select select2" required>
                    <option value="">-- Pilih Spare Part --</option>
                </select>
                </div>
                <div class="mb-3">
                <label for="quantity_used" class="form-label">Quantity Used</label>
                <input type="number" id="quantity_used" class="form-control" min="1" required>
                </div>
                <div class="mb-3">
                <label for="notes" class="form-label">Notes</label>
                <textarea id="notes" class="form-control" rows="3"></textarea>
                </div>
                <div class="mb-3">
                <label for="recorded_by" class="form-label">Recorded By</label>
                <input type="text" id="recorded_by" class="form-control" required>
                </div>

                <!-- snipper tombol loading saat submit form-->
                <button type="submit" class="btn btn-primary w-100" id="saveUsageBtn">
                <span id="btnSpinner" class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                Submit Usage
                </button>
            </form>
            </div>
        </div>
        </div>
    </div>

    <script>
    $(document).ready(function () {
    let debounceTimer;
    let lastDataHash = "";

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-bottom-right",
        "timeOut": "4000"
    };

    // load dropdown kategori
    const categoryFilter = $('#categoryFilter');
    categoryFilter.prop('disabled', true).html('<option value="">Loading categories...</option>');

    $.get('/api/spare-parts/categories', function(response) {
        categoryFilter.html('<option value="">All Categories</option>');
        if (response.data && response.data.length > 0) {
        response.data.forEach(category => categoryFilter.append(`<option value="${category}">${category}</option>`));
        }
    }).always(() => categoryFilter.prop('disabled', false));

    // load data tabel
    function loadParts(showToast = false) {
        $.ajax({
        url: '/api/spare-parts',
        method: 'GET',
        data: {
            search: $('#searchText').val(),
            category: $('#categoryFilter').val(),
            status: $('#statusFilter').val()
        },
        success: function (response) {
            $('#loading').hide();
            let newDataHash = JSON.stringify(response.data);

            if (!response.data || response.data.length === 0) {
            $('#partsTable tbody').html('<tr><td colspan="5" class="text-center">No data found.</td></tr>');
            return;
            }

            let rows = '';
            response.data.forEach(part => {
            const statusClass = part.status.toLowerCase() === 'critical' ? 'status-critical' : 'status-safe';
            rows += `
                <tr>
                <td>${part.part_code}</td>
                <td>${part.part_name}</td>
                <td>${part.stock}</td>
                <td>${part.min_stock}</td>
                <td class="${statusClass}">${part.status}</td>
                </tr>`;
            });

            if (showToast && lastDataHash && newDataHash !== lastDataHash) {
            toastr.info("Inventory data updated automatically.");
            }

            lastDataHash = newDataHash;
            $('#partsTable tbody').html(rows);
        },
        error: function () {
            $('#partsTable tbody').html('<tr><td colspan="5" class="text-center text-danger">Error fetching data.</td></tr>');
        }
        });
    }

    $('#searchText').on('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => loadParts(), 500);
    });
    $('#categoryFilter, #statusFilter').on('change', () => loadParts());
    loadParts();

    // auto kerefresh tiap 15 detik
    setInterval(() => loadParts(true), 15000);

    // dropdown modal
    function loadMachines() {
        $.get('/api/machines', function(response) {
        $('#machine_id').html('<option value="">-- Pilih Mesin --</option>');
        if (response.data) {
            response.data.forEach(m => $('#machine_id').append(`<option value="${m.id}">${m.machine_name}</option>`));
        }
        });
    }
    function loadSparePartsModal() {
        $.get('/api/spare-parts', function(response) {
        $('#spare_part_id').html('<option value="">-- Pilih Spare Part --</option>');
        if (response.data) {
            response.data.forEach(p => $('#spare_part_id').append(`<option value="${p.id}">${p.part_name}</option>`));
        }
        });
    }
    loadMachines();
    loadSparePartsModal();

    // submit form
    $('#usageForm').on('submit', function(e) {
        e.preventDefault();
        const btn = $('#saveUsageBtn');
        const spinner = $('#btnSpinner');

        btn.prop('disabled', true);
        spinner.removeClass('d-none');

        $.ajax({
        url: '/api/spare-parts/usage/record',
        method: 'POST',
        data: {
            machine_id: $('#machine_id').val(),
            spare_part_id: $('#spare_part_id').val(),
            quantity_used: $('#quantity_used').val(),
            notes: $('#notes').val(),
            recorded_by: $('#recorded_by').val(),
        },
        success: function(response) {
            toastr.success(response.message || "Usage recorded successfully");
            $('#usageForm')[0].reset();
            $('#usageModal').modal('hide');
            loadParts();
        },
        error: function(xhr) {
            toastr.error(xhr.responseJSON?.message || 'Error submitting data.');
        },
        complete: function() {

            setTimeout(() => {
            btn.prop('disabled', false);
            spinner.addClass('d-none');
        }, 1500); // wkt spinner load
            
        }
        });
    });
    });
</script>

</body>
</html>
