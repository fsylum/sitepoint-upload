(function($) {
    $(document).ready(function() {
        var $formNotice = $('.form-notice');
        var $imgForm    = $('.image-form');
        var $imgNotice  = $imgForm.find('.image-notice');
        var $imgPreview = $imgForm.find('.image-preview');
        var $imgFile    = $imgForm.find('.image-file');
        var $imgId      = $imgForm.find('[name="image_id"]');

        if ( $imgForm.length ) {
            $imgFile.on('click', function() {
                $(this).val('');
                $imgId.val('');
            });

            $imgForm.on( 'click', '.btn-change-image', function(e) {
                e.preventDefault();
                $imgNotice.empty().hide();
                $imgFile.val('').show();
                $imgId.val('');
                $imgPreview.empty().hide();
            });

            $imgFile.on('change', function(e) {
                e.preventDefault();

                var formData = new FormData();

                formData.append('action', 'upload-attachment');
                formData.append('async-upload', $imgFile[0].files[0]);
                formData.append('name', $imgFile[0].files[0].name);
                formData.append('_wpnonce', su_config.nonce);

                $.ajax({
                    url: su_config.upload_url,
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    xhr: function() {
                        var myXhr = $.ajaxSettings.xhr();

                        if ( myXhr.upload ) {
                            myXhr.upload.addEventListener( 'progress', function(e) {
                                if ( e.lengthComputable ) {
                                    var perc = ( e.loaded / e.total ) * 100;
                                    perc = perc.toFixed(2);
                                    $imgNotice.html('Uploading&hellip;(' + perc + '%)');
                                }
                            }, false );
                        }

                        return myXhr;
                    },
                    type: 'POST',
                    beforeSend: function() {
                        $imgFile.hide();
                        $imgNotice.html('Uploading&hellip;').show();
                    },
                    success: function(resp) {
                        if ( resp.success ) {
                            $imgNotice.html('Successfully uploaded. <a href="#" class="btn-change-image">Change?</a>');

                            var img = $('<img>', {
                                src: resp.data.url
                            });

                            $imgId.val( resp.data.id );
                            $imgPreview.html( img ).show();

                        } else {
                            $imgNotice.html('Fail to upload image. Please try again.');
                            $imgFile.show();
                            $imgId.val('');
                        }
                    }
                });
            });

            $imgForm.on('submit', function(e) {
                e.preventDefault();

                var data = $(this).serialize();

                $.post( su_config.ajax_url, data, function(resp) {
                    if ( resp.success ) {
                        $formNotice.css('color', 'green');
                        $imgForm[0].reset();
                        $imgNotice.empty().hide();
                        $imgPreview.empty().hide();
                        $imgId.val('');
                        $imgFile.val('').show();
                    } else {
                        $formNotice.css('color', 'red');
                    }

                    $formNotice.html( resp.data.msg );
                });
            });
        }
    });
})(jQuery);
