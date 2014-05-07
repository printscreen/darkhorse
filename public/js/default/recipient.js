Darkhorse.prototype.modules.recipient = function (base, index) {
    "use strict";
    var self = this,
        methods = {},
        listeners = {},
        sort = 3,
        offset = 0,
        limit = 10,
        searchField = null,
        searchText = null,
        uploader = null,
        forceProgressTrackingStop = true;

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

    methods.getRecipients = function (batchId, search, text, s, o, l) {
        $.ajax({
            url: '/recipient/view',
            type: 'POST',
            dataType: 'json',
            data: {
                batchId: batchId,
                searchField: search || $('select[name="search-by"]').val(),
                searchText: text || $('input[name="search-text"]').val(),
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
    }

    methods.getRecipient = function (recipientId) {
        $.ajax({
            url: '/recipient/get-recipient',
            type: 'POST',
            dataType: 'json',
            data: {
                recipientId: recipientId
            },
            success: function(data) {
                methods.populateForm(data.recipient);
            },
            error: function (response, status) {
                console.log(response, status);
            }
        });
    };

    methods.populateTable = function (recipients) {
        var html = '',
            total = 0;
        $.each(recipients || [], function(key, val) {
            total = val.total;
            html += '<tr>' +
                        '<td><input type="checkbox" class="move-recipient" value="' + val.recipientId + '" /></td>' +
                        '<td>' + val.email +
                            '<a href="#" class="pull-right detail-row">' +
                                'Details <span class="glyphicon glyphicon-chevron-up"></span>' +
                            '</a>' +
                        '</td>' +
                        '<td>' + val.firstName + '</td>' +
                        '<td>' + val.lastName + '</td>' +
                        '<td>' + (val.verifiedAddress === '1' ? 'Yes' : 'No') + '</td>' +
                        '<td>' + (typeof val.shipTs === 'string' ? val.shipTs.formatFromTimestamp() : '') + '</td>' +
                        '<td>' + (typeof val.shipTs === 'string' ? val.trackingNumber : '') + '</td>' +
                        '<td><a href="#" class="edit-recipient-button" data-recipient-id="' + val.recipientId + '">Edit</a></td>' +
                    '</tr>' +
                    '<tr style="display:none;">' +
                        '<td colspan="7">' +
                            '<div class="well">' +
                                '<div class="row">' +
                                    '<div class="col-md-6">' +
                                        '<address>' +
                                            '<strong>Address</strong><br>' +
                                            val.addressLineOne + (val.addressLineOne.length > 0 ? '<br>' : '') +
                                            val.addressLineTwo + (val.addressLineTwo.length > 0 ? '<br>' : '') +
                                            val.city + ', ' + val.state + ' ' + val.postalCode + '<br>' +
                                        '</address>' +
                                    '</div>' +
                                    '<div class="col-md-6">' +
                                        '<address>' +
                                            '<strong>Order</strong><br>' +
                                            '<abbr title="Sex">Sex: </abbr>' + val.shirtSex + '<br>' +
                                            '<abbr title="Size">Size: </abbr>' + val.shirtSize + '<br>' +
                                            '<abbr title="Type">Type: </abbr>' + val.shirtType +
                                        '</address>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                        '</td>' +
                    '</tr>';
        });
        $('#recipient-form .btn-group').show();
        if(html !== '') {
            $('#recipient-table .table-tools').show();
            $('#recipient-table .pagination').paginate({
                total: total,
                limit: limit,
                offset: offset
            });
        }
        $('#recipient-table tbody').html(html);
    };

    methods.populateForm = function (recipient) {
        var form = $('#edit-recipient-form');
        form.find('input[name="recipientId"]').val(recipient.recipientId);
        form.find('input[name="batchId"]').val(recipient.batchId);
        form.find('input[name="email"]').val(recipient.email);
        form.find('input[name="firstName"]').val(recipient.firstName);
        form.find('input[name="lastName"]').val(recipient.lastName);
        form.find('input[name="addressLineOne"]').val(recipient.addressLineOne);
        form.find('input[name="addressLineTwo"]').val(recipient.addressLineTwo);
        form.find('input[name="city"]').val(recipient.city);
        form.find('select[name="state"]').val(recipient.state);
        form.find('input[name="postalCode"]').val(recipient.postalCode);
        form.find('input[name="shirtSex"]').val(recipient.shirtSex);
        form.find('input[name="shirtSize"]').val(recipient.shirtSize);
        form.find('input[name="shirtType"]').val(recipient.shirtType);
        form.find('input[name="quantity"]').val(recipient.quantity);
    };

    methods.populateCustomerDropDown = function (customers) {
        var html = '<option value="">Select A Customer</option>';
        $.each(customers, function(key, val) {
            html += '<option value="' + val.customerId + '">' +val.name + '</option>' + '</option>';
        });
        $('select[name="customer"]').html(html);
    };

    methods.populateBatchDropDown = function (batches) {
        var html = '<option value="">Select A Batch</option>';
        $.each(batches, function(key, val) {
            html += '<option value="' + val.batchId + '">' +val.name + '</option>' + '</option>';
        });
        $('select[name="batch"], select[name="toBatch"]').html(html);
    };

    methods.saveForm = function () {
        $.ajax({
            url: '/recipient/edit',
            type: 'POST',
            dataType: 'json',
            data: $('#edit-recipient-form').serialize(),
            beforeSend: function () {
                $('#save-recipient-form')
                    .prop('disabled', true)
                    .html('Saving...');
            },
            success: function(result) {
                if(result.success) {
                    methods.getRecipients(
                        $('select[name="batch"]').val(),
                        null,
                        null,
                        sort,
                        offset,
                        limit
                    );
                    $('#edit-recipient-dialog').modal('hide');
                } else {
                    base.displayFormErrors(
                        $('#edit-recipient-form'),
                        result.errors
                    );
                }
            },
            complete: function () {
                $('#save-recipient-form')
                    .prop('disabled', false)
                    .html('Save');
            },
            error: function (response, status) {
                console.log(response, status);
            }
        });
    };

    methods.moveRecipients = function () {
        $.ajax({
            url: '/recipient/move',
            type: 'POST',
            dataType: 'json',
            data: {
                who: function () {
                    return $('select[name="who"]').val();
                },
                toBatchId: function () {
                    return $('select[name="toBatch"]').val();
                },
                fromBatchId: function () {
                    return $('select[name="batch"]').val();
                },
                recipientIds: function () {
                    var ids = [];
                    $('.move-recipient:checked').each(function (key, val) {
                        ids.push($(this).val());
                    });
                    return ids;
                }()
            },
            beforeSend: function () {
                $('#move-recipeint-submit')
                    .prop('disabled', true)
                    .html('Moving...');
            },
            success: function(result) {
                if(result.success) {
                    $('#move-recipient-form').clearForm();
                    $('#recipient-table input[type="checkbox"]').prop('checked', false);
                    methods.getRecipients(
                        $('select[name="batch"]').val(),
                        null,
                        null,
                        sort,
                        offset,
                        limit
                    );
                    $('#move-recipient-dialog').modal('hide');
                } else {
                    base.displayFormErrors(
                        $('#edit-recipient-form'),
                        result.errors
                    );
                }
            },
            complete: function () {
                $('#move-recipeint-submit')
                    .prop('disabled', false)
                    .html('Move');
            },
            error: function (response, status) {
                console.log(response, status);
            }
        });
    };

    methods.trackStatus = function () {
        $.ajax({
            url: '/recipient/verify-status',
            dataType: 'json',
            data: function () {
                return $('#recipient-form select[name="batch"]').val();
            },
            success: function (response) {
                // Forcing stop, exit immediately
                if (forceProgressTrackingStop) {
                    return;
                }
                // Update UI with progress
                var status = response.status.split('|'),
                    width = (+status[0] * 100) / +status[1],
                    text = null;
                if(response.status) {
                    $('#verify-recipient-dialog')
                    .find('.progress-bar')
                        .css('width', width + '%').end()
                    .find('.progress-text')
                        .text( +status[0] + ' / ' + (+status[1]));
                }
                //methods.trackStatus();

                window.setTimeout(
                    methods.trackStatus, 500
                );
            }
        });
    };

    methods.verify = function () {
        $.ajax({
            url: '/recipient/verify',
            dataType: 'json',
            data: function () {
                return $('#recipient-form select[name="batch"]').val();
            },
            beforeSend: function () {
                forceProgressTrackingStop = false;
                $('#verify-recipeint-submit')
                    .prop('disabled', true)
                    .text('Verifying...');
            },
            success: function (response) {
                methods.getRecipients(
                    $('select[name="batch"]').val(),
                    null,
                    null,
                    sort,
                    offset,
                    limit
                );
                $('#verify-recipient-dialog').modal('hide');
            },
            complete: function () {
                $('#verify-recipeint-submit')
                    .prop('disabled', false)
                    .text('Verify');
                forceProgressTrackingStop = true;
                $('#verify-recipient-dialog')
                    .find('.progress-bar')
                        .css('width', '0%').end()
                    .find('.progress-text')
                        .text('');
            }
        });
        methods.trackStatus();
    };

    listeners.uploadFile = function() {
        uploader = new AjaxUpload($('#upload-file'), {
            action: '/recipient/upload',
            name: 'file',
            data: {
                batchId: function () {
                    return $('#recipient-form select[name="batch"]').val();
                }
            },
            onChange: function(file, extension) {
                var span = $('#upload-file').next();
                if (extension === 'csv') {
                    span.html('<span class="label label-success">File selcted: ' + file + '</span>');
                } else {
                    span.html('<span class="label label-danger"><strong>Error!</strong> You must select a CSV</span>');
                }
            },
            onSubmit: function(file, extension) {
                if (extension !== 'csv') {
                    return false;
                }
                $('#upload-csv-form')
                    .prop('disabled', true)
                    .text('Uploading...');
            },
            autoSubmit: false,
            responseType: 'json',
            timeout: 300,
            onComplete: function(file, response) {
                $('#upload-csv-form')
                    .prop('disabled', false)
                    .text('Upload');
                $('#upload-file').next().html('');
                $('#upload-csv-dialog').modal('hide');
                methods.getRecipients(
                    $('select[name="batch"]').val()
                  , null
                  , null
                  , sort = 1
                  , offset = 0
                  , limit = 20
                );
            }
        });
    };

    listeners.saveForm = function  () {
        $('#edit-recipient-form').validate({
            rules: {
                recipientId: {
                    required: function(){
                        return !$('#edit-recipient-form')
                        .find('input[name="recipientId"]')
                        .is(':disabled');
                    },
                    digits: true
                },
                batchId: {
                    required: true,
                    digits: true
                },
                email: {
                    required: false,
                    email: true
                },
                firstName: 'required',
                lastName: 'required',
                addressLineOne: 'required',
                city: 'required',
                state: 'required',
                postalCode: 'required',
                shirtSex: 'required',
                shirtSize: 'required',
                shirtType: 'required',
                quantity: {
                    required: true,
                    digits: true
                }
            },
            messages : {
                recipientId: 'Please enter a recipient id',
                batchId: 'Please enter a batch id',
                email: {
                    email: 'Not a valid email address'
                },
                firstName: 'Please enter a first name',
                lastName: 'Please enter a last name',
                addressLineOne: 'Please enter a street address',
                city: 'Please enter a city',
                state: 'Please select a state',
                postalCode: 'Please enter a postal code',
                shirtSex: 'Shirt Sex',
                shirtSize: 'Shirt Size',
                shirtType: 'Shirt Type',
                quantity: {
                    required: 'Please enter a quantity',
                    digits: 'Numbers only'
                }
            },
            showErrors: function () {},
            invalidHandler: base.displayFormErrors,
            submitHandler: methods.saveForm
        });
        $('#save-recipient-form').click(function() {
            $('#edit-recipient-form').submit();
        });
    };

    listeners.moveRecipients = function  () {
        $('#move-recipient-form').validate({
            rules: {
                who: 'required',
                toBatch: {
                    required: true,
                    digits: true
                }
            },
            messages : {
                who: 'Please select a type',
                toBatch: 'Please select a batch'
            },
            showErrors: function () {},
            invalidHandler: base.displayFormErrors,
            submitHandler: methods.moveRecipients
        });
        $('#select-all-recipients').click(function () {
            $('.move-recipient').prop('checked', $(this).is(':checked'));
        });
        $('#move-recipeint-submit').click(function () {
            $('#move-recipient-form').submit();
        });
        $('#move-recipient-dialog').on('hidden.bs.modal', function () {
            $('#move-recipient-form').clearForm();
        });
    };

    listeners.selectCustomer = function () {
        $('select[name="customer"]').change(function () {
            $('select[name="batch"]')
                .val('')
                .prop('disabled', true);
            $('select[name="batch"]').trigger('change');
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

    listeners.submitCsvForm = function () {
        $('#upload-csv-form').click(function (event) {
            uploader.submit();
            event.preventDefault();
        });
    };

    listeners.rowDetail = function () {
        $('#recipient-table tbody').on('click', '.detail-row', function () {
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

    listeners.uploadDialog = function () {
        $('#upload-csv-link').click(function (event) {
            event.preventDefault();
            if($('select[name="batch"]').val() !== '') {
                $('#upload-csv-dialog').modal('show');
            }
        });
        $('#upload-csv-dialog').on('hide.bs.modal', function (event) {
            uploader._clearInput();
            $('#upload-file').next().html('');
        });
    };

    listeners.verifyDialog = function () {
        $('#verify-recipeint-submit').click(function (event) {
            methods.verify();
        });
        $('#verify-recipient-dialog').on('hide.bs.modal', function (event) {
            $('#verify-recipient-dialog')
                .find('.progress-bar')
                    .css('width', '0%').end()
                .find('.progress-text')
                    .text('');
                forceProgressTrackingStop = true;
        });
    };

    listeners.editDialog = function () {
        $('#recipient-table').on('click', '.edit-recipient-button, #add-recipient', function (event) {
            var batchId = $('select[name="batch"]').val();
            event.preventDefault();
            if(batchId === '') {
                return;
            }
            $('#edit-recipient-form').clearForm();
            if(typeof $(this).data('recipientId') !== 'undefined') {
                $('#edit-recipient-dialog .modal-header h4').html('Edit Recipient');
                $('#edit-recipient-form').find('input[name="recipientId"]').prop('disabled', false);
                methods.getRecipient($(this).data('recipientId'));
            } else {
                $('#edit-recipient-dialog .modal-header h4').html('Add Customer');
                $('#edit-recipient-form')
                    .find('input[name="recipientId"]')
                    .prop('disabled', true)
                    .end()
                    .find('input[name="batchId"]')
                    .val(batchId);
            }
            $('#edit-recipient-dialog').modal('show');
        });
    };

    listeners.selectBatch = function () {
        $('select[name="batch"]').change(function () {
            $('#recipient-table tbody').html('');
            $('#recipient-table .table-tools')
                .hide()
                .find('form')
                .each(function (key, val) {
                    $(val).clearForm();
                });
            $('#recipient-form .btn-group').hide();
            if($(this).val() != '') {
                methods.getRecipients(
                    $(this).val()
                  , null
                  , null
                  , sort
                  , offset = 0
                  , limit
                );
            }
        });
    };

    listeners.page = function () {
        $('ul.pagination').on('click', 'a[data-offset]', function () {
            methods.getRecipients(
                $('select[name="batch"]').val()
              , null
              , null
              , sort
              , offset = $(this).data('offset')
              , limit
            );
        });
    };

    listeners.pageCount = function () {
        $('select[name="page-count"]').change(function () {
            limit = $(this).val();
            methods.getRecipients(
                $('select[name="batch"]').val()
              , null
              , null
              , sort
              , offset
              , limit
            );
        });
    };

    listeners.filterTable = function () {
        $('input[name="search-text"]').delayKeyup(function () {
            methods.getRecipients(
                $('select[name="batch"]').val()
              , null
              , null
              , sort
              , offset
              , limit
            );
        }, 500);
    };

    listeners.sortable = function () {
        $('.sortable').click(function () {
            var direction = '',
                upIcon = $(this).find('.glyphicon-arrow-up'),
                downIcon = $(this).find('.glyphicon-arrow-down'),
                firstTime = !$(this).find('span:not(.hide)').length,
                currentlyUp = !upIcon.hasClass('hide');

            if($('select[name="batch"]').val() == '') {
                return;
            }
            $('.sortable').find('.glyphicon:not(.hide)').addClass('hide');
            if(firstTime || currentlyUp) {
                downIcon.removeClass('hide');
                direction = '';
            } else {
                upIcon.removeClass('hide');
                direction = '-';
            }
            sort = direction + $(this).data('sort-id');
            methods.getRecipients(
                $('select[name="batch"]').val(),
                null,
                null,
                sort,
                offset,
                limit
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