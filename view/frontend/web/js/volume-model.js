define([
    'jquery',
    'Magento_Ui/js/modal/modal'
], function ($, modal) {

    return function (config) {
        const options = {
            type: 'popup',
            responsive: true,
            title: '3D Model',
            class: 'volumetric-modal',
            buttons: []
        };
        const volumeModelModal = $('.volume-model-modal');
        const displayingMode = config.displayingMode;
        modal(options, volumeModelModal);

        function buildButton(width, height) {
            return `<button style="width:${width}px; height:${height}px;" class="volumetric-model-button volumetric-model-button-gallery"><img src="${config.viewFileUrl}"/></span></button>`;
        }

        function removeButton() {
            const fotoramaThumbs = $('.fotorama__nav__shaft');
            const dots = $(".fotorama__nav--dots>.fotorama__nav__shaft");
            if (fotoramaThumbs && fotoramaThumbs.find(".volumetric-model-button").length !== 0) {
                fotoramaThumbs.find(".volumetric-model-button").remove();
            }
            if (dots && dots.find(".volumetric-model-button").length !== 0) {
                fotoramaThumbs.find(".volumetric-model-button").remove();
            }
        }

        function renderButton() {
            const windowWidth = window.innerWidth;
            const fotoramaThumbs = $('.fotorama__nav__shaft');
            const dots = $(".fotorama__nav--dots>.fotorama__nav__shaft");
            if (windowWidth > 768) {
                let buttonWidth = 100;
                let buttonHeight = 100;

                if (fotoramaThumbs.children().length > 1) {
                    const lastThumb = fotoramaThumbs.find(".fotorama__nav__frame:last-child");
                    if (lastThumb.width() && lastThumb.height()) {
                        buttonWidth = lastThumb.width();
                        buttonHeight = lastThumb.height();
                    }
                    removeButton();
                    if (fotoramaThumbs.find(".volumetric-model-button").length === 0) {
                        const html = buildButton(buttonWidth, buttonHeight);
                        fotoramaThumbs.append(html);
                    }
                }
            } else {
                removeButton();
                if (dots.find(".volumetric-model-button").length === 0) {
                    const buttons = buildButton(75, 75);
                    dots.append(buttons);
                }
                console.log(dots);
            }
            $(".volumetric-model-button").click(function () {
                volumeModelModal.modal('openModal');
                window.loadVolumeModel();
            });
        }

        $('[data-gallery-role=gallery-placeholder]').on('fotorama:ready', function () {
            if (!window?.loadVolumeModel) {
                console.error('Error while loading volumetric model script');
                return;
            }

            if (displayingMode !== "fotoramaGallery" && displayingMode !== "both") {
                $(".volumetric-model-button").click(function () {
                    volumeModelModal.modal('openModal');
                    window.loadVolumeModel();
                });
                return;
            }

            setTimeout(() => {
                renderButton();
            }, 1000);
        });

        $(window).on('resize', function() {
            renderButton();
        })
    };
});
