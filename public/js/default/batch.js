Darkhorse.prototype.modules.batch = function (base, index) {
    "use strict";
    var self = this,
        methods = {},
        listeners = {},
        sort = 1,
        offset = 0,
        limit = 20,
        active = true;

    methods.getCustomers = function (s, o, l) {
        $.ajax({
            url: '/customer/view',
            type: 'POST',
            dataType: 'json',
            data: {
                sort: s,
                offset: o,
                limit: l
            },
            success: function(data) {
                methods.populateDropDown(data.customers);
            },
            error: function (response, status) {
                console.log(response, status);
            }
        });
    }

    methods.getBatches = function (customerId, active, s, o, l) {
        $.ajax({
            url: '/batch/view',
            type: 'POST',
            dataType: 'json',
            data: {
                customerId: customerId,
                active: active,
                sort: s,
                offset: o,
                limit: l
            },
            success: function(data) {
                methods.populateTable(data.batches);
            },
            error: function (response, status) {
                console.log(response, status);
            }
        });
    }

    methods.getBatch = function (batchId) {
        $.ajax({
            url: '/batch/get-batch',
            type: 'POST',
            dataType: 'json',
            data: {
                batchId: batchId
            },
            success: function(data) {
                methods.populateForm(data.batch);
            },
            error: function (response, status) {
                console.log(response, status);
            }
        });
    };

    methods.populateDropDown = function (customers) {
        var html = '<option value="">Select A Customer</option>';
        $.each(customers, function(key, val) {
            html += '<option value="' + val.customerId + '">' +val.name + '</option>' + '</option>';
        });
        $('#select-customer').html(html);
    };

    methods.populateTable = function (batches) {
        var html = '';
        $.each(batches || [], function(key, val) {
            html += '<tr>' +
                        '<td>' + val.name +
                            '<a href="#" class="pull-right detail-row">' +
                                'Details <span class="glyphicon glyphicon-chevron-up"></span>' +
                            '</a>' +
                        '</td>' +
                        '<td>' + val.insertTs.formatFromTimestamp() + '</td>' +
                        '<td>' + (val.active === '1' ? 'Yes' : 'No') + '</td>' +
                        '<td><a href="#" class="edit-batch-button" data-batch-id="' + val.batchId + '">Edit</a></td>' +
                    '</tr>' +
                    '<tr style="display:none;">' +
                        '<td colspan="4">' +
                            '<div class="well">' +
                                '<div class="row">' +
                                    '<div class="col-md-6">' +
                                        '<address>' +
                                            '<strong>Return Address</strong><br>' +
                                            val.street + (val.street.length > 0 ? '<br>' : '') +
                                            val.suiteApt + (val.suiteApt.length > 0 ? '<br>' : '') +
                                            val.city + ', ' + val.state + ' ' + val.postalCode + '<br>' +
                                        '</address>' +
                                    '</div>' +
                                    '<div class="col-md-6">' +
                                        '<address>' +
                                            '<strong>Contact Information</strong><br>' +
                                            val.contactName + '<br>' +
                                            '<abbr title="Phone">P: </abbr>' + val.contactPhoneNumber + '<br>' +
                                            '<abbr title="Email">Email: </abbr>' + val.contactEmail +
                                        '</address>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                        '</td>' +
                    '</tr>';
        });
        $('#batch-table tbody').html(html);
    };

    methods.populateForm = function (batch) {
        var form = $('#edit-batch-form');
        form.find('input[name="batchId"]').val(batch.batchId);
        form.find('input[name="customerId"]').val(batch.customerId);
        form.find('input[name="name"]').val(batch.name);
        form.find('input[name="contactName"]').val(batch.contactName);
        form.find('input[name="contactPhoneNumber"]').val(batch.contactPhoneNumber);
        form.find('input[name="contactEmail"]').val(batch.contactEmail);
        form.find('input[name="street"]').val(batch.street);
        form.find('input[name="suiteApt"]').val(batch.suiteApt);
        form.find('input[name="city"]').val(batch.city);
        form.find('select[name="state"]').val(batch.state);
        form.find('input[name="postalCode"]').val(batch.postalCode);
        form.find('select[name="active"]').val(batch.active == '1' ? 'true' : 'false');
    };

    methods.saveForm = function () {
        $.ajax({
            url: '/batch/edit',
            type: 'POST',
            dataType: 'json',
            data: $('#edit-batch-form').serialize(),
            beforeSend: function () {
                $('#save-batch-form')
                    .prop('disabled', true)
                    .html('Saving...');
            },
            success: function(result) {
                if(result.success) {
                    methods.getBatches(
                        $('#select-customer').val(),
                        $('#show-active').is(':checked'),
                        sort,
                        offset,
                        limit
                    );
                    $('#edit-batch-dialog').modal('hide');
                } else {
                    base.displayFormErrors(
                        $('#edit-customer-form'),
                        result.errors
                    );
                }
            },
            complete: function () {
                $('#save-batch-form')
                    .prop('disabled', false)
                    .html('Save');
            },
            error: function (response, status) {
                console.log(response, status);
            }
        });
    };

    listeners.batchDialog = function () {
        $('#batch-table').on('click', '.edit-batch-button', function () {
            if($('#select-customer').val() == '') {
                alert('Please select a customer first in the drop down');
                return;
            }
            $('#edit-batch-form').clearForm();
            if(typeof $(this).data('batchId') !== 'undefined') {
                $('#edit-batch-dialog .modal-header h4').html('Edit Batch');
                $('#edit-batch-form').find('input[name="batchId"]').prop('disabled', false);
                methods.getBatch($(this).data('batchId'));
            } else {
                $('#edit-batch-dialog .modal-header h4').html('Add Batch');
                $('#edit-batch-form')
                    .find('input[name="batchId"]')
                    .prop('disabled', true);
                $('#edit-batch-form')
                    .find('input[name="customerId"]')
                    .val(
                        $('#select-customer').val()
                    );
            }
            $('#edit-batch-dialog').modal('show');
        });
    };

    listeners.saveForm = function  () {
        $('#edit-batch-form').validate({
            rules: {
                batchId: {
                    required: function(){
                        return !$('#edit-batch-form')
                        .find('input[name="batchId"]')
                        .is(':disabled');
                    },
                    digits: true
                },
                customerId: {
                    required: true,
                    digits: true
                },
                name: 'required',
                active: 'required'
            },
            messages : {
                name: 'Please enter a name',
                customerId: 'Missing customer Id',
                active: 'Please select an active state'
            },
            showErrors: function () {},
            invalidHandler: base.displayFormErrors,
            submitHandler: methods.saveForm
        });
        $('#save-batch-form').click(function() {
            $('#edit-batch-form').submit();
        });
    };

    listeners.selectCustomer = function () {
        $('#select-customer').change(function () {
            $('#batch-table tbody').html('');
            if($(this).val() !== '') {
                $('#show-active')
                    .prop('checked', true)
                    .closest('.checkbox')
                    .toggleClass('hide', false);
                methods.getBatches(
                    $(this).val(),
                    true,
                    sort,
                    offset,
                    limit
                );
            } else {
                $('#show-active')
                    .closest('.checkbox')
                    .toggleClass('hide', true);
            }
        });
    };

    listeners.showActive = function () {
        $('#show-active').click(function() {
            methods.getBatches(
                    $('#select-customer').val(),
                    $(this).is(':checked'),
                    sort,
                    offset,
                    limit
                );
        });
    };

    listeners.rowDetail = function () {
        $('#batch-table tbody').on('click', '.detail-row', function () {
            var row = $(this).closest('tr').next('tr');
            if(row.is(':hidden')) {
                $(this).html('Hide' + ' <span class="glyphicon glyphicon-chevron-down"></span>');
                row.show();
            } else {
                $(this).html('Details' + ' <span class="glyphicon glyphicon-chevron-up"></span>');
                row.hide();
            }
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