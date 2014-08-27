Darkhorse.prototype.modules.print = function (base, index) {
    "use strict";
    var self = this,
        methods = {},
        listeners = {},
        sort = 3,
        offset = 0,
        limit = 10,
        run = false,
        pause = false,
        qz = document.getElementById('qz');

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

    methods.getRecipients = function (batchId, s, o, l) {
        $.ajax({
            url: '/recipient/view',
            type: 'POST',
            dataType: 'json',
            data: {
                batchId: batchId,
                sort: s,
                offset: o,
                limit: l
            },
            success: function(data) {
                var name = $('select[name="batch"] option:selected')
                                .text()
                                .split(' ')
                                .join('');
                methods.populateTable(data.recipients);
                //Handle export links
                $('#export-recipients').attr('href', '/recipient/export?batchId='+batchId+'&name='+name);
                $('#stats').attr('href', '/recipient/stats?batchId='+batchId+'&name='+name);
                //Clear checkboxes
                $('input[type="checkbox"]').prop('checked', false);
            },
            error: function (response, status) {
                console.log(response, status);
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

    methods.populateTable = function (recipients) {
        var html = '',
            total = 0;
        $.each(recipients || [], function(key, val) {
            total = val.total;
            html += '<tr data-recipient-id="' + val.recipientId + '">' +
                        '<td>' + val.email +'</td>' +
                        '<td>' + val.firstName + '</td>' +
                        '<td>' + val.lastName + '</td>' +
                        '<td>' + (val.verifiedAddress === '1' ? 'Yes' : 'No') + '</td>' +
                        '<td>' + (typeof val.shipTs === 'string' ? val.shipTs.formatFromTimestamp() : '') + '</td>' +
                        '<td>' +
                            (typeof val.shipTs === 'string' ? val.trackingNumber : '') +
                            ((isNaN(val.scanFromId) && val.trackingNumber !== null) ?
                                '<a href="#" class="pull-right cancel-recipient-button" data-recipient-id="' + val.recipientId + '">Cancel</a>'
                                : ''
                            ) +
                        '</td>' +
                        '<td><a href="#" class="print-recipient-button" data-recipient-id="' + val.recipientId + '">Print</a></td>' +
                    '</tr>';
        });
        if(html !== '') {
            $('#print-table .table-tools').show();
            $('#print-table .pagination').paginate({
                total: total,
                limit: limit,
                offset: offset
            });
        }
        $('#print-table tbody').html(html);
    };

    methods.printStamp = function (recipientId, callback) {
        callback(); return;
        window.qzDoneAppending = function() {
            // Tell the applet to print PostScript.
            methods.getPrinter().printPS();
            window.qzDonePrinting = function() {
                if(typeof callback === 'function') {
                    callback();
                }
                window.qzDonePrinting = null;
            };
            // Remove reference to this function
            window.qzDoneAppending = null;
        };
        methods.getPrinter().appendImage('http://www.darkhorse.com/postage/print?recipientId='+recipientId);
    };

    methods.generateStamp = function (recipientId, callback) {
        $.ajax({
            url: '/postage/generate-stamp',
            dataType: 'json',
            async: false,
            data: {
                recipientId: recipientId
            },
            success: function (response) {
                if(typeof callback === 'function') {
                    callback();
                }
            }
        });
    };

    methods.massPrint = function (printtype, batchId) {
        var recipients = [],
            total = 0,
            printStamps = function () {
                var recipientId = recipients.pop(),
                    position = total - recipients.length;
                if(isNaN(recipientId) || !run) {
                    return false;
                }
                methods.updateProgressBar('#print-dialog', position, total);
                methods.generateStamp(recipientId, function () {
                    methods.printStamp(recipientId, function () {
                        printStamps();
                    })
                });
        };

        $.ajax({
            url: '/postage/get-recipient',
            dataType: 'json',
            async: false,
            data: {
                printtype: printtype
              , batchId: batchId
            },
            success: function (response) {
                if(response.recipients.length) {
                    $.each(response.recipients || [], function (key, val) {
                        recipients.push(val.recipientId);
                    });
                    total = recipients.length;
                    methods.updateProgressBar('#print-dialog', 0, total);
                    run = true;
                    printStamps();
                }
            }
        });
    };

    methods.updateProgressBar = function (selector, position, total) {
        var percentage = (total === 0) ? 0 : parseInt((position/ total) * 100, 10);
        $(selector)
            .find('.progress-bar')
                .css('width', percentage + '%').end()
            .find('.progress-text')
                .text(position + ' / ' + total);
    };

    methods.getPrinter = function () {
        return qz;
    };

    methods.massCancel = function (batchId) {
        var recipients = [],
            total = 0,
            cancelStamps = function () {
                var recipientId = recipients.pop(),
                    position = total - recipients.length;
                if(isNaN(recipientId) || !run) {
                    run = false;
                    return true;
                }
                methods.updateProgressBar('#cancel-dialog', position, total);
                methods.cancelPostage(recipientId, cancelStamps);
            };

        $.ajax({
            url: '/postage/get-recipient',
            dataType: 'json',
            async: false,
            data: {
                printtype: 'cancel'
              , batchId: batchId
            },
            success: function (response) {
                if(response.recipients.length) {
                    $.each(response.recipients || [], function (key, val) {
                        recipients.push(val.recipientId);
                    });
                    total = recipients.length;
                    methods.updateProgressBar('#cancel-dialog', 0, total);
                    run = true;
                    cancelStamps();
                }
            }
        });

    };

    methods.cancelPostage = function (recipientId, callback) {
        $.ajax({
            url: '/postage/cancel-stamp',
            dataType: 'json',
            async: true,
            data: {
                recipientId: recipientId
            },
            success: function (response) {
                if(typeof callback === 'function') {
                    callback();
                }
            }
        });
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
            methods.getRecipients(
                $(this).val()
              , sort
              , offset
              , limit
            );
        });
    };

    listeners.printDialog = function () {
        $('.print-selection').on('click', 'a', function () {
            if($(this).data('printtype') == 'cancel') {
                $('#cancel-dialog').modal('show');
                return;
            }
            $('#print-dialog').find('h4').html(
                $(this).text()
            );
            $('#print-submit').data('printtype', $(this).data('printtype'));
            $('#print-dialog').modal('show');
        });
        $('#print-dialog').on('hide.bs.modal', function (event) {
            $('#print-submit').prop('disabled', false);
            methods.updateProgressBar('#print-dialog', 0, 0);
            run = false;
            $('select[name="batch"]').trigger('change');
        });
    };

    listeners.print = function () {
        $('#print-submit').click(function() {
            $(this).prop('disabled', true);
            methods.massPrint(
                $(this).data('printtype')
              , $('select[name="batch"]').val()
            );
        });
        $('#print-table').on('click', '.print-recipient-button', function () {
            var recipientId = $(this).data('recipient-id');
            methods.generateStamp(recipientId, function () {
                methods.printStamp(recipientId, function () {
                    $('select[name="batch"]').trigger('change');
                });
            });
        });
    };

    listeners.cancelPostage = function () {
        $('#print-table').on('click', '.cancel-recipient-button', function () {
            var recipientId = $(this).data('recipient-id'),
                element = $(this);
            element.text('Canceling...').prop('disabled', true);
            methods.cancelPostage(recipientId, function () {
                element.closest('td').html('').prev().html('');
            });
        });
        $('#cancel-submit').click(function () {
            $(this).prop('disabled', true);
            methods.massCancel(
                $('select[name="batch"]').val()
            );
        });
        $('#cancel-dialog').on('hide.bs.modal', function (event) {
            $('#cancel-submit').prop('disabled', false);
            methods.updateProgressBar('#cancel-dialog', 0, 0);
            run = false;
            $('select[name="batch"]').trigger('change');
        });
    };

    this.dispatch = function () {
        methods.getCustomers();
        // Add listeners
        $.each(listeners, function (index, func) {
            func();
        });
        methods.getPrinter().setPaperSize("3.8in", "6.0in");
        methods.getPrinter().setAutoSize(true);
    };
};
