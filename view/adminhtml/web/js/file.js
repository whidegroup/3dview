define([
    'jquery',
    'Magento_Ui/js/form/element/media',
    'Magento_Ui/js/modal/alert',
    'ko',
    './wrapper'
], function ($, Media, alert, ko, wrapper) {
    'use strict';

    return Media.extend({
        hidden: ko.observable(wrapper.fileInputHidden),
        note: $.mage.__('Note: Supported formats: GLTF/GLB or ZIP archive with one of these format. A preview of the 3D Model will be available after saving the product.'),
        actions: {
            change: function (data, event) {
                const inputField = event.target;
                const uploadedFile = event.target.files;
                if (uploadedFile && uploadedFile?.[0]) {
                    const allowedExtensions = ['gltf', 'glb', 'fbx', 'zip', 'obj', 'stl'];
                    const fileSize = uploadedFile[0].size;
                    const maxFileSize = 100 * 1024 * 1024;
                    const fileName = uploadedFile[0].name;
                    const fileExtension = fileName.split('.').pop().toLowerCase();

                    if (fileSize > maxFileSize) {
                        alert({
                            title: $.mage.__('Warning'),
                            content: $.mage.__('The file is too large to upload. Please upload a smaller file.')
                        });
                        $(inputField).val('');
                    }

                    if (allowedExtensions.indexOf(fileExtension) === -1) {
                        alert({
                            title: $.mage.__('Warning'),
                            content: $.mage.__('Unsupported file extension. Please try another file.')
                        });
                        $(inputField).val('');
                    }
                }
            }
        },
        initialize: function () {
            return this._super();
        },

        /**
         * On value change handler.
         *
         * @param {String} value
         */
        onUpdate: function (value) {
            return this._super();
        },
    });
});