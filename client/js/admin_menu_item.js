/**
 * A module "active_menu_item" for m.js JavaScript Library v1.1
 * https://m-framework.com/js/modules/active_menu_item
 *
 * Copyright 2017 m-framework.com
 * Released under the MIT license
 * https://m-framework.com/js/license
 *
 * Author: mrinmiro@gmail.com
 *
 * Date: 2017-10-17T01:12Z
 */
if (typeof m == 'undefined') {
    var m = function () {};
    m.fn = m.prototype = {};
}

m.fn.admin_menu_item = function(context) {

    var
        path = window.location.pathname.replace(/\/page\/[0-9]+/, ''),
        a = this.closest('a[href]'),
        link = a.attr('href') !== null ? a.attr('href').replace('http://', '').replace('https://', '')
            .replace(window.location.host, '') : null;

    if (link === null) {
        return false;
    }

    if (path == link) {
        a.class(!a.class('collapse') && a.next('.sub-menu') === false ? 'active' : 'selected', true);

        if (a.closest('.sub-menu').length > 0 && a.closest('.sub-menu').prev('.collapse').length > 0) {
            a.closest('.sub-menu').prev('.collapse').class({active: true});
        }
    }

    if (a.next('.sub-menu').length > 0 && a.next('.sub-menu').children('a').length > 0 && a.class('collapse')) {

        var next_sub_menu = a.next('.sub-menu');

        this.active_toggle(a);

        next_sub_menu.off('click');
        next_sub_menu.on('click', function(e) {
            if (e.target == next_sub_menu.first) {
                a.class({active: null});
            }
        });
    }
};