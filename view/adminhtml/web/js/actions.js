define(['Magento_Ui/js/form/components/button', 'jquery', 'Magento_Ui/js/modal/confirm', 'ko', "Magento_Ui/js/modal/modal", "./wrapper"],
    function (Button, $, confirm, ko, modal, wrapper) {
        'use strict';

        return Button.extend({
            noticeVisible: false,
            modalInitialized: false,
            modalElement: null,
            fileInputDisplaying: false,
            buttons: {
                options: {
                    visible: ko.observable(true)
                },
                preview: {
                    text: ko.observable($.mage.__('Preview Model')),
                    title: ko.observable($.mage.__('Preview of the 3D model')),
                    classes: ko.observable('volumetric-model-button'),
                    visible: ko.observable(true),
                    action: function (target, viewModel) {
                        this.openModal();
                    }
                },
                newModel: {
                    text: ko.observable($.mage.__('Load New Model')),
                    title: ko.observable($.mage.__('Load New 3D Model')),
                    classes: ko.observable('volumetric-model-load-new'),
                    visible: ko.observable(true),
                    action: function (target, viewModel) {
                        const newModelFileSection = $(".volumetric_models__file");
                        newModelFileSection.css('display') === "none"
                            ? this.buttons.newModel.text($.mage.__('Cancel'))
                            : this.buttons.newModel.text($.mage.__('Load New Model'))
                        this.fileInputDisplaying = true;
                        newModelFileSection.slideToggle(200);
                    }
                },
                remove: {
                    text: ko.observable($.mage.__('Delete Model')),
                    title: ko.observable($.mage.__('Remove 3D model from the product')),
                    classes: ko.observable('VolumetricModels-delete-button secondary'),
                    visible: ko.observable(true),
                    action: function (target, viewModel) {
                        const self = this;
                        confirm({
                            title: $.mage.__('Confirmation'),
                            content: $.mage.__('Are you sure you want to delete the 3D model?'),
                            actions: {
                                confirm: function () {
                                    self.sendDeleteRequest();
                                }
                            }
                        });
                    }
                }
            },
            modal: {
                productId: ko.observable(""),
                loadingText: $.mage.__('Loading 3D model...')
            },
            initialize: function () {
                this._super();
                const self = this;
                self.modal.productId(this.source.data.product['current_product_id']);
                $('body').on('click', function (e) {
                    setTimeout(() => {
                        if ($(".volume-model-modal").length > 0 && !self.modalInitialized) {
                            self.modalInitialization();
                        }
                        if (Boolean(self.source.data?.product?.volume_model) && !self.fileInputDisplaying) {
                            $(".volumetric_models__file").css('display', 'none');
                        }
                    }, 200);
                });
            },
            displayStatusMessage: function (status, color) {
                const statusMessageElement = $("#VolumetricModels-delete-status");
                statusMessageElement.css('color', color);
                statusMessageElement.text(status);
                this.noticeVisible = true;
                statusMessageElement.fadeIn(200);
                setTimeout(() => {
                    this.noticeVisible = false;
                    statusMessageElement.text("");
                }, 3000)
            },
            sendDeleteRequest: function () {
                const self = this;
                $('body').trigger('processStart');
                $.ajax({
                    url: self.postDataUrl,
                    type: 'POST',
                    data: {
                        form_key: window.FORM_KEY,
                        productId: self.source.data.product.current_product_id
                    },
                    dataType: 'json',
                    success: function (response) {
                        self.displayStatusMessage(response, '#16a34a');
                        self.buttons.remove.title($.mage.__('There is no available 3D models for the product.'));
                        self.buttons.options.visible(false);
                        self.fileInputDisplaying = true;
                        $(".volumetric_models__file").slideDown(200);
                    },
                    error: function (xhr, status, error) {
                        self.displayStatusMessage("Error while deleting 3D model. Please try again later.", '#ef4444');
                        console.error('Error:', error);
                    },
                    complete: function () {
                        $('body').trigger('processStop');
                    }
                });
            },
            modalInitialization: function () {
                const self = this;
                const options = {
                    type: 'popup',
                    responsive: true,
                    title: '3D Model',
                    class: 'volumetric-modal',
                    buttons: []
                };
                const volumeModelModal = $('.volume-model-modal');
                if (volumeModelModal) {
                    self.modalElement = volumeModelModal;
                    modal(options, volumeModelModal);
                    self.modalInitialized = true;
                }
            },
            openModal: function () {
                wrapper().fileInputHidden(wrapper().fileInputHidden() + 1);
                const self = this;
                if (!self.modalElement) {
                    return;
                }

                self.modalElement.modal('openModal');
                window.loadVolumeModel();
            }
        })
    })