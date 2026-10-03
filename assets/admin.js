
jQuery(document).ready(function ($) {

    let mediaUploader;

    $('#spm_upload_logo').on('click', function (e) {

        e.preventDefault();

        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        mediaUploader = wp.media({
            title: 'Select Company Logo',
            button: {
                text: 'Use This Logo'
            },
            multiple: false,
            library: {
                type: 'image'
            }
        });

        mediaUploader.on('select', function () {

            const attachment = mediaUploader
                .state()
                .get('selection')
                .first()
                .toJSON();

            $('#spm_speaker_company_logo').val(attachment.id);

            $('#spm_logo_preview')
                .attr('src', attachment.url)
                .show();

            $('#spm_remove_logo').show();
        });

        mediaUploader.open();
    });

    $('#spm_remove_logo').on('click', function (e) {

        e.preventDefault();

        $('#spm_speaker_company_logo').val('');

        $('#spm_logo_preview')
            .attr('src', '')
            .hide();

        $(this).hide();
    });

});