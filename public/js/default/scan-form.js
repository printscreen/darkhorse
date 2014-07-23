Darkhorse.prototype.modules.scanForm = function (base, index) {
    "use strict";
    var self = this,
        methods = {},
        listeners = {},
        sort = 3,
        offset = 0,
        limit = 10;

    methods.getCustomers = function () {
        $.ajax({
            url: '/customer/view',
            type: 'POST',
            dataType: 'json',
            success: function(data) {
                methods.populateCustomerDropDown(data.customers);
            },
            error: function (response, status) {
                console.log(response, status);
            }
        });
    };

    methods.getBatches = function (customerId, active) {
        $.ajax({
            url: '/batch/view',
            type: 'POST',
            dataType: 'json',
            data: {
                customerId: customerId,
                active: active
            },
            success: function(data) {
                methods.populateBatchDropDown(data.batches);
            },
            error: function (response, status) {
                console.log(response, status);
            }
        });
    };

    methods.getScanForms = function (batchId, s, o, l) {
        if(batchId === '') {
            methods.clearTable();
            return;
        }
        $.ajax({
            url: '/scanform/view',
            type: 'POST',
            dataType: 'json',
            data: {
                batchId: batchId,
                sort: s,
                offset: o,
                limit: l
            },
            success: function(data) {
                methods.populateTable(data.scanForms);
            },
            error: function (response, status) {
                console.log(response, status);
            }
        });
    };

    methods.getScanFormRecipients = function (scanFormId) {
        $.ajax({
            url: '/scanform/get-recipient',
            type: 'POST',
            dataType: 'json',
            data: {
                scanFormId: scanFormId
            },
            success: function(data) {
                methods.populateRecipients(data.availableRecipients, data.recipients);
            },
            error: function (response, status) {
                console.log(response, status);
            }
        });
    };

    methods.moveRecipients = function (moveFrom, moveTo) {
        var from = $('#manage-recipients').find('select[name="' + moveFrom + '[]"]'),
            to = $('#manage-recipients').find('select[name="' + moveTo + '[]"]'),
            options = from.find('option:selected');
            $.ajax({
                url: '/scanform/edit-recipient',
                type: 'POST',
                dataType: 'json',
                data: $('#manage-recipients-form').serialize(),
                success: function(data) {
                    to.append(options);
                    $('#manage-recipients select[name="' + moveFrom + '[]"] option:selected').remove();
                },
                error: function (response, status) {
                    console.log(response, status);
                }
            });
    };

    methods.generateScanForm = function (scanFormId) {
        $.ajax({
            url: '/scanform/generate-form',
            type: 'POST',
            dataType: 'json',
            data: {
                scanFormId: scanFormId
            },
            beforeSend: function () {
                $('#generate-submit')
                    .prop('disabled', true)
                    .html('Generating...');
            },
            success: function(data) {
                $('select[name="batch"]').trigger('change');
                $('#generate-dialog').modal('hide');
            },
            error: function (response, status) {
                console.log(response, status);
            },
            complete: function () {
                $('#generate-submit')
                    .prop('disabled', false)
                    .html('Generate');
            }
        });
    };

    methods.populateCustomerDropDown = function (customers) {
        var html = '<option value="">Select A Customer</option>';
        $.each(customers || [], function(key, val) {
            html += '<option value="' + val.customerId + '">' +val.name + '</option>' + '</option>';
        });
        $('select[name="customer"]').html(html);
    };

    methods.populateBatchDropDown = function (batches) {
        var html = '<option value="">Select A Batch</option>';
        $.each(batches || [], function(key, val) {
            html += '<option value="' + val.batchId + '">' +val.name + '</option>' + '</option>';
        });
        $('select[name="batch"]').html(html);
    };

    methods.populateTable = function (scanForms) {
        var html = '';
        $.each(scanForms.scanForms || [], function(key, val) {
            html += '<tr>' +
                        '<td>' + val.name +'</td>' +
                        '<td>' + val.insertTs.formatFromTimestamp() + '</td>' +

                        (val.isGenerated != 1 ?
                            ('<td><a href="#" class="generate-scanform" data-scan-form-id="' + val.scanFormId + '">Generate</a></td>' +
                             '<td><a href="#" class="manage-recipients" data-scan-form-id="' + val.scanFormId + '">Manage Recipients</a></td>' +
                             '<td><a href="#" class="edit-scanform pull-right" data-scan-form-id="' + val.scanFormId + '">Edit</a></td>'
                            ) :
                            '<td colspan="3"><a href="#" class="print-scanform pull-right" data-scan-form-id="' + val.scanFormId + '">Print</a></td>'
                        )
                        '</td>' +
                    '</tr>';
        });
        $('.edit-scanform').toggleClass('hide', false);
        $('#scanform-table tbody').html(html);
    };

    methods.populateRecipients = function (availableRecipients, recipients) {
        var availableSelect = $('#manage-recipients-form').find('select[name="add[]"]'),
            currentSelect = $('#manage-recipients-form').find('select[name="remove[]"]');
        $.each(availableRecipients || [], function (key, val) {
            availableSelect.append(
                '<option value="' + val.recipientId + '">' +
                    val.trackingNumber + ': ' + val.lastName + ', ' + val.firstName +
                '</option>'
            );
        });
        $.each(recipients || [], function (key, val) {
            currentSelect.append(
                '<option value="' + val.recipientId + '">' +
                    val.trackingNumber + ': ' + val.lastName + ', ' + val.firstName +
                '</option>'
            );
        });
    };

    methods.clearTable = function () {
        $('.edit-scanform').toggleClass('hide', true);
        $('#scanform-table tbody').html('');
    };

    methods.toggleRecipientsForm = function (show) {
        $('#manage-scanforms').toggleClass('hide', show);
        $('#manage-recipients').toggleClass('hide', !show);
        if(!show) {
            $('#manage-recipients-form').clearForm();
            $('#manage-recipients-form').find('select').html('');
        }
    };

    methods.saveForm = function () {
        $.ajax({
            url: '/scanform/edit',
            type: 'POST',
            dataType: 'json',
            data: $('#edit-scanform-form').serialize(),
            beforeSend: function () {
                $('#save-scanform-form')
                    .prop('disabled', true)
                    .html('Saving...');
            },
            success: function(result) {
                if(result.success) {
                    methods.getScanForms(
                        $('select[name="batch"]').val(),
                        sort,
                        offset,
                        limit
                    );
                    $('#edit-scanform-dialog').modal('hide');
                } else {
                    base.displayFormErrors(
                        $('#edit-scanform-form'),
                        result.errors
                    );
                }
            },
            complete: function () {
                $('#save-scanform-form')
                    .prop('disabled', false)
                    .html('Save');
            },
            error: function (response, status) {
                console.log(response, status);
            }
        });
    };

    listeners.saveForm = function  () {
        $('#edit-scanform-form').validate({
            rules: {
                scanFormId: {
                    required: function(){
                        return !$('#edit-scanform-form')
                        .find('input[name="scanFromId"]')
                        .is(':disabled');
                    },
                    digits: true
                },
                batchId: {
                    required: true,
                    digits: true
                },
                name: 'required'
            },
            messages : {
                scanFormId: 'Please enter a scan form id',
                batchId: 'Please enter a batch id',
                name: 'Please enter a name'
            },
            showErrors: function () {},
            invalidHandler: base.displayFormErrors,
            submitHandler: methods.saveForm
        });
        $('#save-scanform-form').click(function() {
            $('#edit-scanform-form').submit();
        });
    };

    listeners.selectCustomer = function () {
        $('select[name="customer"]').change(function () {
            methods.clearTable();
            $('select[name="batch"]')
                .val('')
                .prop('disabled', true);
            if ($(this).val() !== '') {
                $('select[name="batch"]')
                    .prop('disabled', false);
                methods.getBatches(
                    $(this).val(),
                    true
                );
            }
        });
    };

    listeners.selectBatch = function () {
        $('select[name="batch"]').change(function () {
            methods.clearTable();
            methods.getScanForms(
                $(this).val()
              , sort
              , offset
              , limit
            );
        });
    };

    listeners.editScanform = function () {
        $('#scanform-table').on('click', '.edit-scanform', function () {
            if($(this).data('scan-form-id')) {
                $('#edit-scanform-dialog').find('.modal-title').html('Edit Scan Form');
                $('#edit-scanform-form')
                    .find('input[name="scanFormId"]')
                    .val($(this).data('scan-form-id'))
                    .prop('disabled', false)
                    .end()
                    .find('input[name="name"]')
                    .val($('td:first', $(this).parents('tr')).text())
                    .end()
                    .find('input[name="batchId"]')
                    .val($('select[name="batch"]').val());
            } else {
                $('#edit-scanform-dialog').find('.modal-title').html('Add New Scan Form');
                $('#edit-scanform-form')
                    .find('input[name="scanFormId"]')
                    .val('')
                    .prop('disabled', true)
                    .end()
                    .find('input[name="batchId"]')
                    .val($('select[name="batch"]').val());;
            }
            $('#edit-scanform-dialog').modal('show');
        });
        $('#edit-scanform-dialog').on('hide.bs.modal', function (e) {
            $('#edit-scanform-form').clearForm();
        });
    };

    listeners.generateScanForm = function () {
        $('#scanform-table').on('click', '.generate-scanform', function () {
            $('#generate-dialog').find('.modal-title').html($('td:first', $(this).parents('tr')).text());
            $('#generate-submit').data('scan-form-id', $(this).data('scan-form-id'));
            $('#generate-dialog').modal('show');
        });
        $('#generate-submit').click(function () {
            methods.generateScanForm($(this).data('scan-form-id'));
        });
    };

    listeners.manageRecipients = function () {
        $('#scanform-table').on('click', '.manage-recipients', function () {
            var scanFormId = $(this).data('scan-form-id');
            if(scanFormId.length === 0 || isNaN(scanFormId)) {
                return;
            }
            methods.getScanFormRecipients(scanFormId);
            $('#manage-recipients-form').find('input[name="scanFormId"]').val(scanFormId);
            methods.toggleRecipientsForm(true);

        });
        $('#cancel-manage-recipients').click(function () {
            methods.toggleRecipientsForm(false);
        });
        $('.move-recipients').click(function () {
            methods.moveRecipients(
                $(this).data('move-from')
              , $(this).data('move-to')
            );
        });
    };

    this.dispatch = function () {
        methods.getCustomers();
        // Add listeners
        $.each(listeners, function (index, func) {
            func();
        });
    };
};