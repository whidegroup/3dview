define([
    'jquery',
    'uiComponent',
    'ko',
], function ($, Component, ko) {
    'use strict';

    return Component.extend({
        defaults: {
            fileInputHidden: ko.observable(25)
        }
    });
});