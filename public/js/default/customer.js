Darkhorse.prototype.modules.customers = function (base, index) {
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
                methods.populateTable(data.customers);
            },
            error: function (response, status) {
                console.log(response, status);
            }
        });
    }

    methods.getCustomer = function (customerId) {
        $.ajax({
            url: '/customer/get-customer',
            type: 'POST',
            dataType: 'json',
            data: {
                customerId: customerId
            },
            success: function(data) {
                methods.populateForm(data.customer);
            },
            error: function (response, status) {
                console.log(response, status);
            }
        });
    };

    methods.populateTable = function (customers) {
        var html = '';
        $.each(customers, function(key, val) {
            html += '<tr>' +
                        '<td>' + val.name + '</td>' +
                        '<td>' + (val.active === '1' ? 'Yes' : 'No') + '</td>' +
                        '<td><a href="#" class="edit-customer-button" data-customer-id="' + val.customerId + '">Edit</a></td>' +
                    '</tr>';
        });
        $('#customer-table tbody').html(html);
    };

    methods.populateForm = function (customer) {
        var form = $('#edit-customer-form');
        form.find('input[name="customerId"]').val(customer.customerId);
        form.find('input[name="name"]').val(customer.name);
        form.find('select[name="active"]').val(customer.active == '1' ? 'true' : 'false');
    };

    methods.saveForm = function () {
        $.ajax({
            url: '/customer/edit',
            type: 'POST',
            dataType: 'json',
            data: $('#edit-customer-form').serialize(),
            beforeSend: function () {
                $('#save-customer-form')
                    .prop('disabled', true)
                    .html('Saving...');
            },
            success: function(result) {
                if(result.success) {
                    methods.getCustomers(sort, offset, limit);
                    $('#edit-customer-dialog').modal('hide');
                } else {
                    base.displayFormErrors(
                        $('#edit-customer-form'),
                        result.errors
                    );
                }
            },
            complete: function () {
                $('#save-customer-form')
                    .prop('disabled', false)
                    .html('Save');
            },
            error: function (response, status) {
                console.log(response, status);
            }
        });
    };

    listeners.customerDialog = function () {
        $('#customer-table').on('click', '.edit-customer-button', function () {
            $('#edit-customer-form').clearForm();
            if(typeof $(this).data('customerId') !== 'undefined') {
                $('#edit-customer-dialog .modal-header h4').html('Edit Customer');
                $('#edit-customer-form').find('input[name="customerId"]').prop('disabled', false);
                methods.getCustomer($(this).data('customerId'));
            } else {
                $('#edit-customer-dialog .modal-header h4').html('Add Customer');
                $('#edit-customer-form').find('input[name="customerId"]').prop('disabled', true);
            }
            $('#edit-customer-dialog').modal('show');
        });
    };

    listeners.saveForm = function  () {
        $('#edit-customer-form').validate({
            rules: {
                customerId : {
                    required: function(){
                        return !$('#edit-customer-form')
                        .find('input[name="customerId"]')
                        .is(':disabled');
                    },
                    digits: true
                },
                name: 'required',
                active: 'required'
            },
            messages : {
                name: 'Please enter a name',
                active: 'Please select an active state'
            },
            showErrors: function () {},
            invalidHandler: base.displayFormErrors,
            submitHandler: methods.saveForm
        });
        $('#save-customer-form').click(function() {
            $('#edit-customer-form').submit();
        });
    };

    this.dispatch = function () {
        methods.getCustomers(sort, offset, limit);

        // Add listeners
        $.each(listeners, function (index, func) {
            func();
        });
    };
};