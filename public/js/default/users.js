Darkhorse.prototype.modules.users = function (base, index) {
    "use strict";
    var self = this,
        methods = {},
        listeners = {},
        sort = 1,
        offset = 0,
        limit = 20,
        active = true;

    methods.getUsers = function (s, o, l) {
        $.ajax({
            url: '/users/view',
            type: 'POST',
            dataType: 'json',
            data: {
                sort: s,
                offset: o,
                limit: l
            },
            success: function(data) {
                methods.populateTable(data.users);
            },
            error: function (response, status) {
                console.log(response, status);
            }
        });
    }

    methods.getUser = function (userId) {
        $.ajax({
            url: '/users/get-user',
            type: 'POST',
            dataType: 'json',
            data: {
                userId: userId
            },
            success: function(data) {
                methods.populateForm(data.user);
            },
            error: function (response, status) {
                console.log(response, status);
            }
        });
    };

    methods.populateTable = function (users) {
        var html = '';
        $.each(users, function(key, val) {
            html += '<tr>' +
                        '<td>' + val.firstName + '</td>' +
                        '<td>' + val.lastName + '</td>' +
                        '<td>' + val.email + '</td>' +
                        '<td>' + val.userTypeName.ucfirst() + '</td>' +
                        '<td>' + (val.active === '1' ? 'Yes' : 'No') + '</td>' +
                        '<td><a href="#" class="edit-user-button" data-user-id="' + val.userId + '">Edit</a></td>' +
                    '</tr>';
        });
        $('#user-table tbody').html(html);
    };

    methods.populateForm = function (user) {
        var form = $('#edit-user-form');
        form.find('input[name="userId"]').val(user.userId);
        form.find('input[name="firstName"]').val(user.firstName);
        form.find('input[name="lastName"]').val(user.lastName);
        form.find('input[name="email"]').val(user.email);
        form.find('select[name="active"]').val(user.active == '1' ? 'true' : 'false');
        form.find('select[name="userTypeId"]').val(user.userTypeId);
    };

    methods.saveForm = function () {
        $.ajax({
            url: '/users/edit',
            type: 'POST',
            dataType: 'json',
            data: $('#edit-user-form').serialize(),
            beforeSend: function () {
                $('#save-user-form')
                    .prop('disabled', true)
                    .html('Saving...');
            },
            success: function(result) {
                if(result.success) {
                    methods.getUsers(sort, offset, limit);
                    $('#edit-user-dialog').modal('hide');
                } else {
                    base.displayFormErrors(
                        $('#edit-user-form'),
                        result.errors
                    );
                }
            },
            complete: function () {
                $('#save-user-form')
                    .prop('disabled', false)
                    .html('Save');
            },
            error: function (response, status) {
                console.log(response, status);
            }
        });
    };

    listeners.userDialog = function () {
        $('#user-table').on('click', '.edit-user-button', function () {
            $('#edit-user-form').clearForm();
            if(typeof $(this).data('userId') !== 'undefined') {
                $('#edit-user-dialog .modal-header h4').html('Edit User');
                $('#edit-user-form input[name="password"]').closest('.form-group').hide();
                $('#edit-user-form').find('input[name="userId"]').prop('disabled', false);
                methods.getUser($(this).data('userId'));
            } else {
                $('#edit-user-form input[name="password"]').closest('.form-group').show();
                $('#edit-user-dialog .modal-header h4').html('Add User');
                $('#edit-user-form').find('input[name="userId"]').prop('disabled', true);
            }
            $('#edit-user-dialog').modal('show');
        });
    };

    listeners.saveForm = function  () {
        $('#edit-user-form').validate({
            rules: {
                userId : {
                    required: function(){
                        return !$('#edit-user-form')
                        .find('input[name="userId"]')
                        .is(':disabled');
                    },
                    digits: true
                },
                firstName: 'required',
                lastName: 'required',
                email: {
                    required: true,
                    email: true
                },
                userTypeId : {
                    required: true,
                    digits: true
                },
                active: 'required',
                password : {
                    required: function(){
                        return $('#edit-user-form')
                        .find('input[name="userId"]')
                        .is(':disabled');
                    }
                },
            },
            messages : {
                firstName: 'Please enter a first name',
                lastName: 'Please enter a last name',
                email: 'Please enter a valid email address',
                active: 'Please select an active state',
                userTypeId: 'Please select a user type',
                password: 'Please enter a password'
            },
            showErrors: function () {},
            invalidHandler: base.displayFormErrors,
            submitHandler: methods.saveForm
        });
        $('#save-user-form').click(function() {
            $('#edit-user-form').submit();
        });
    };

    this.dispatch = function () {
        methods.getUsers(sort, offset, limit);

        // Add listeners
        $.each(listeners, function (index, func) {
            func();
        });
    };
};