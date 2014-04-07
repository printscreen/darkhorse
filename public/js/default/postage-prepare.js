Darkhorse.prototype.modules.prepare = function (base, index) {
    "use strict";
    var self = this,
        methods = {},
        listeners = {},
        timer = 0;

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
    }

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
    }

    methods.getProducts = function (batchId) {
        $.ajax({
            url: '/postage/product',
            type: 'POST',
            dataType: 'json',
            data: {
                batchId: batchId
            },
            success: function(data) {
                methods.populateProductTable(data.products);
            },
            error: function (response, status) {
                console.log(response, status);
            }
        });
    };

    methods.populateProductTable = function (products) {
        var html = '';
        $.each(products || [], function(key, val) {
            html += '<tr data-batch-id="' + val.batchId + '">' +
                        '<td>' + val.shirtSex + '</a></td>' +
                        '<td>' + val.shirtSize + '</td>' +
                        '<td><a href="#" data-toggle="popover">' + val.shirtType + '</a></td>' +
                        '<td class="product-weight">' +
                            '<form class="form-inline">' +
                                '<div class="input-group">' +
                                    '<input type="text" size="1" readonly="readonly" class="form-control" value="' + (val.weight || 0) + '">' +
                                    '<span class="input-group-addon">oz</span>' +
                                    '<button class="btn btn-primary adjust-weight add-weight">' +
                                        '<span class="glyphicon glyphicon-white glyphicon-plus"></span>' +
                                    '</button>' +
                                    '<button class="btn btn-default adjust-weight">' +
                                        '<span class="glyphicon glyphicon-minus"></span>' +
                                    '</button>' +
                                '</div>' +
                            '</form>' +
                        '</td>' +
                    '</tr>';
        });
        $('#prepare-table tbody').html(html);
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

    methods.adjustWeight = function (batchId, sex, size, type, weight) {
        $.ajax({
            url: '/postage/set-product-weight',
            type: 'POST',
            dataType: 'json',
            data: {
                batchId: batchId,
                shirtSex: sex,
                shirtSize: size,
                shirtType: type,
                weight: weight
            },
            error: function (response, status) {
                console.log(response, status);
            }
        });
    };

    methods.adjustType = function (row, button, newValue) {
        $.ajax({
            url: '/postage/set-product-type',
            type: 'POST',
            dataType: 'json',
            data: {
                batchId: row.data('batchId'),
                shirtSex: row.find('td:eq(0)').text(),
                shirtSize: row.find('td:eq(1)').text(),
                shirtType: newValue,
                oldValue: row.find('td:eq(2)').text()
            },
            beforeSend: function () {
                button
                    .prop('disabled', true)
                    .html('<img src="/img/ajax-loader.gif" />');
            },
            success: function(data) {
                if(data.success) {
                    methods.getProducts(row.data('batchId'));
                }
            },
            error: function (response, status) {
                console.log(response, status);
            },
            complete: function () {
                button
                    .prop('disabled', false)
                    .html('<span class="glyphicon glyphicon-white glyphicon-ok"></span>');
            }
        });
    };

    methods.popoverHtml = function (batchId, sex, size, type) {
        return '<form class="form-inline" autocomplete="off">' +
                    '<div class="control-group">' +
                        '<div class="form-group">' +
                            '<input class="form-control" type="text">' +
                        '</div>' +
                        '<div class="form-group">' +
                            '<button class="btn btn-primary btn-sm edit-type-button">' +
                                '<span class="glyphicon glyphicon-white glyphicon-ok"></span>' +
                            '</button>' +
                        '</div>' +
                        '<div class="form-group">' +
                            '<button class="btn btn-default editable-cancel btn-sm" type="button">' +
                                '<span class="glyphicon glyphicon-ban-circle"></span>' +
                            '</button>' +
                        '</div>' +
                        '<span class="help-block" style="clear: both"></span>' +
                    '</div>' +
                '</form>';
    };

    listeners.selectCustomer = function () {
        $('select[name="customer"]').change(function () {
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
            methods.getProducts($(this).val());
        });
    };

    listeners.popover = function () {
        $('#prepare-table tbody').popover({
            selector: '[data-toggle="popover"]',
            placement: 'auto top',
            html: true,
            trigger: 'click',
            content: function () {
                return methods.popoverHtml(
                    $(this).closest('tr').data('batchId'),
                    $(this).text()
                );
            }
        });
        $('#prepare-table tbody').on('click', '.editable-cancel', function (e) {
            $(this).closest('.popover').prev().popover('toggle');
        });
    };

    listeners.adjustWeight = function () {
        $('#prepare-table tbody').on('click', 'button.adjust-weight', function (event) {
            event.preventDefault();
            var input = $(this).siblings('input'),
                row = $(this).closest('tr'),
                currentValue = parseInt(input.val(), 10);
            if($(this).hasClass('add-weight')) {
                currentValue = currentValue + 1;
            } else if (currentValue > 0) {
                currentValue = currentValue - 1;
            } else {
                return;
            }
            input.val(currentValue);
            clearTimeout (timer);
            timer = setTimeout(function() {
                methods.adjustWeight (
                    row.data('batchId')
                  , row.find('td:eq(0)').text()
                  , row.find('td:eq(1)').text()
                  , row.find('td:eq(2)').text()
                  , currentValue
                );
            }, 1000);
        });
    };

    listeners.adjustType = function() {
        $('#prepare-table tbody').on('click', 'button.edit-type-button', function (event) {
            var form = $(this).closest('form'),
                row = $(this).closest('tr'),
                input = form.find('input'),
                conflict = false,
                shirtSex = row.find('td:eq(0)').text(),
                shirtSize = row.find('td:eq(1)').text(),
                shirtType = row.find('td:eq(2)').text(),
                newValue = input.val().toUpperCase(),
                weight = row.find('input[readonly="readonly"]').val(),
                conflictRow = null;
            event.preventDefault();
            form.removeClass('has-error');
            if(input.val() === '') {
                form.addClass('has-error');
                return;
            }
            //Find a row with the same values
            $('#prepare-table tr:not(first-child)').not($(row)).each(function (key, val) {
                var findRow = $(val);
                if(findRow.find('td:eq(0)').text() === shirtSex &&
                   findRow.find('td:eq(1)').text() === shirtSize &&
                   findRow.find('td:eq(2)').text() === newValue &&
                   findRow.find('input[readonly="readonly"]').val() !== weight) {
                    conflictRow = findRow;
                    return false;
                }
            });
            if(conflictRow !== null) {
                alert('Please set weight to ' +
                    conflictRow.find('input[readonly="readonly"]').val() +
                    ' before setting to this name');
                return false;
            }
            methods.adjustType(row, $(this), newValue);
        });
    };

    this.dispatch = function () {
        methods.getCustomers();
        // Add listeners
        $.each(listeners, function (index, func) {
            func();
        });
    };
}