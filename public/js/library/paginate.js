jQuery.fn.paginate = function (parameters) {
    "use strict";
    var self = this,
        methods = {},
        pages = 0,
        currentPage = 1,
        options = {
            total: 0,
            limit: 0,
            offset: 0,
            totalNodes: 5
        };

    $.extend(options, parameters);

    methods.init = function () {
        if (options.total === 0) {
            throw new Error ("Division by 0");
        }
        pages = Math.ceil(options.total / options.limit);
        currentPage = Math.ceil(options.offset / options.limit) + 1;
        methods.paginate();
    };

    methods.paginate = function () {
        var html = '',
            minPage = currentPage - Math.ceil(options.totalNodes / 2),
            minPage = minPage < 0 ? 0 : minPage,
            maxPage = minPage + options.totalNodes,
            i = 0,
            page = 0;
        if(maxPage > pages) {
            minPage = minPage - (maxPage - pages);
            maxPage = pages;
        }
        for(i = minPage; i < maxPage; i += 1) {
            page = i + 1;
            if(page < 1) {
                continue;
            }
            if(page === currentPage) {
               html += '<li class="active"><a href="#">' + currentPage + '</a></li>';
            } else {
                html += '<li><a href="#" data-offset="' + (i * options.limit) + '"' +
                '>' + page + '</a></li>';
            }
        }
        if (html !== '') {
            html = '<li><a href="#" data-offset="0">&laquo;</a></li>' +
                    html +
                    '<li><a href="#" data-offset="' + ((pages - 1) * options.limit) + '">&raquo;</a></li>';

        }
        $(self).html(html);
    };

    methods.init();
};








