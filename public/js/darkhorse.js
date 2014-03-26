var Darkhorse = function (parameters) {
    "use strict";
    var self = this,
    methods = {},
    options = {};

    $.extend(options, parameters);

    methods.init = function () {
        if(typeof self.modules !== 'undefined') {
            $.each(self.modules, function (index, Module) {
                self.modules[index] = new Module(self, index);
                self.modules[index].dispatch();
            });
        }
        // Alert and redirect on session timeout
        $.ajaxPrefilter(function (options) {
            var originalSuccess = options.success,
                sessionHasExpired = function (data) {
                    var orgData = data;
                    if (typeof (orgData) === 'string') {
                        try {
                            orgData = $.parseJSON(orgData);
                        } catch (e) {
                            return false;
                        }
                    }
                    if (typeof (orgData) === 'undefined' || orgData === null || !orgData.sessionExpired) {
                        return false;
                    }
                    return true;
                },
                alertAndRedirect = function (response) {
                    var data = response;
                    if(typeof response !== "object") {
                        data = jQuery.parseJSON(response) || {};
                    }
                    if(data.url) {
                        if(data.message) {
                            alert(data.message);
                        }
                        window.location.href = data.url;
                    } else {
                        alert('Your session has expired.');
                        window.location.href = '/auth/login';
                    }
                    return false;
                };
            options.success = function (data, textStatus, jqXHR) {
                if (sessionHasExpired(data)) {
                    jqXHR.isResolved = function () { return false; };
                    return alertAndRedirect(data);
                }
                if (typeof (originalSuccess) === 'function') {
                    return originalSuccess(data, textStatus, jqXHR);
                }
            };
        });
    };

    this.displayFormErrors = function (form, errors) {
        $.each(errors.errorMap || errors, function (key, val) {
            $('[name=\"' + key + '\"]').closest('.form-group').find('.help-block').html(val);
            $('[name=\"' + key + '\"]').closest('.form-group').addClass('has-error');
        });
    };

    this.clearErrors = function(form) {
        $.each(form.find(':input'), function(key,val){
            $(val).closest('.form-group').removeClass('has-error');
            $(val).closest('.form-group').find('.help-block').html('');
        });
    };

    this.dispatch = function () {
        methods.init();
    };

};
Darkhorse.prototype.modules = {};


$(document).ready(function (){
    var darkhorse = new Darkhorse();
    darkhorse.dispatch();
});

//Extensions
$.fn.serializeObject = function () {
    var o = {};
    var a = this.serializeArray();
    $.each(a, function() {
        if (o[this.name] !== undefined) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};
$.fn.clearForm = function () {
    $(this)
    .trigger('reset')
    .find('input[type="hidden"], input[type="password"], input[type="file"], textarea')
    .val('')
    .end()
    .find('select option:first-child')
    .attr('selected', 'selected')
    .end()
    .find('input:radio, input:checkbox').removeAttr('checked').removeAttr('selected');
    $(this).clearFormErrors();
    return $(this);
};
$.fn.clearFormErrors = function () {
    $.each($(this).find(':input'), function(key,val){
        $(val).closest('.form-group').removeClass('has-error');
        $(val).closest('.form-group').find('.help-block').html('');
    });
    return $(this);
};
String.prototype.ucfirst = function () {
    return this.charAt(0).toUpperCase() + this.slice(1);
}
String.prototype.formatFromTimestamp = function () {
    var t = this.split(/[- :]/),
        date = new Date(t[0], t[1] - 1, t[2], t[3] || 0, t[4] || 0, t[5] || 0);
    return date.getMonth() + 1 + '/' + date.getDate() + '/' + date.getFullYear();
}
$.fn.delayKeyup = function(callback, ms){
    var timer = 0;
    var el = $(this);
    $(this).keyup(function(){
    clearTimeout (timer);
    timer = setTimeout(function(){
        callback(el)
        }, ms);
    });
    return $(this);
};