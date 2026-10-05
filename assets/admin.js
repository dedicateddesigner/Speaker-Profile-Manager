jQuery(function($){
    var partnerFrame = null;
    var speakerFrame = null;

    // Speaker company logo picker
    $('#spm_upload_logo').on('click', function(e){
        e.preventDefault();
        if (speakerFrame) {
            speakerFrame.open();
            return;
        }
        speakerFrame = wp.media({
            title: 'Select Company Logo',
            button: { text: 'Use Company Logo' },
            multiple: false,
            library: { type: 'image' }
        });
        speakerFrame.on('select', function(){
            var attachment = speakerFrame.state().get('selection').first().toJSON();
            $('#spm_speaker_company_logo').val(attachment.id);
            $('#spm_logo_preview').attr('src', attachment.url).show();
            $('#spm_remove_logo').show();
        });
        speakerFrame.open();
    });

    $('#spm_remove_logo').on('click', function(e){
        e.preventDefault();
        $('#spm_speaker_company_logo').val('');
        $('#spm_logo_preview').attr('src', '').hide();
        $(this).hide();
    });

    // Sponsor / Media Partner logo picker
    $('#spm_partner_upload').on('click', function(e){
        e.preventDefault();
        if (partnerFrame) {
            partnerFrame.open();
            return;
        }
        partnerFrame = wp.media({
            title: 'Select Partner Logo',
            button: { text: 'Use Logo' },
            multiple: false,
            library: { type: 'image' }
        });
        partnerFrame.on('select', function(){
            var attachment = partnerFrame.state().get('selection').first().toJSON();
            $('#spm_partner_logo').val(attachment.id);
            $('#spm_partner_logo_preview').attr('src', attachment.url).show();
        });
        partnerFrame.open();
    });

    $('#spm_partner_remove').on('click', function(e){
        e.preventDefault();
        $('#spm_partner_logo').val('');
        $('#spm_partner_logo_preview').attr('src', '').hide();
    });
});
