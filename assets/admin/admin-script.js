(function ($) {

    var l10n = wp.media.view.l10n;
    wp.media.view.MediaFrame.Select.prototype.browseRouter = function( routerView ) {
        routerView.set({
            upload: {
                text:     l10n.uploadFilesTitle,
                priority: 20
            },
            browse: {
                text:     l10n.mediaLibraryTitle,
                priority: 40
            },
            search_image: {
                text:     "Search Image",
                priority: 60
            }
        });
    };


    if ( wp.media ) {
        wp.media.view.Modal.prototype.on( "open", function() {
            if($('body').find('.media-frame-router .media-router .media-menu-item.active')[0].innerText == "Search Image")
                noorImageSearchTabContent();
        });
        $(wp.media).on('click', '.media-frame-router .media-router .media-menu-item', function(e){
            if(e.target.innerText == "Search Image")
                noorImageSearchTabContent();
        });
    }


    function noorImageSearchTabContent() {

        let html = '<div class="NoorMediaTabContent">';
        html += '<div class="image-search-area"><form>\n' +
            '    <input class="input" type="search" placeholder="Search for...">\n' +
            '    <input class="submit noorimgfnd-search-button" type="submit" value="Search Image">\n' +
            '</form></div>';

        html += '<div class="images-area">';


        // Ajax operation start
        let searchQuery = 'people';
        $.ajax({
            type: 'post',
            url: ajaxurl,
            dataType: "json", // add data type
            data: { action : 'noorimgfnd_get_images', searchQuery : searchQuery },
            beforeSend: function() {
                $('.noor-image-loader').show();
            },
            success: function( images ) {
                //console.log(images);
                if (images.length > 0) {
                    for (let i=0; i < images.length; i++){
                        html += '<div class="image-item">';
                        html += '<img src="' + images[i].medium + '" alt="">';
                        html += '<h3 class="noor-image-button noor-use-image-button-'+images[i].id+'" data-image="'+ images[i].original +'" data-imageid="'+ images[i].id +'">Use this image</h3>';
                        html += '<div class="noor-loader noor-loader-'+images[i].id+'"></div>';
                        html += '</div>';

                    }//for loop
                }
                else {
                    html += '<h2 class="no-images-found">No images found, please try again with different keywords</h2>';
                }
                html += '</div>';

                html += '</div>';
                $('body .media-modal-content .media-frame-content')[0].innerHTML = html;
            }
        });//ajax end
 

        $('body .media-modal-content .media-frame-content')[0].innerHTML = html;
    }

    function noorimgfnd_ajax_search_images() {

    }

    function noorUploadImageToWPmedia() {
        $(document).on('click', '.noor-image-button', function() {
            //let imageUrl = 'https://i.stack.imgur.com/n7Rzd.png';
            let imageUrl = $(this).data("image");
            let imageId = $(this).data("imageid");
            $.ajax({
                type: 'post',
                url: ajaxurl,
                dataType: 'json', // add data type
                data: { action : 'noorimgfnd_upload_image', imageUrl : imageUrl },
                beforeSend: function() {
                    $('.noor-loader.noor-loader-'+imageId+'').show();
                },
                success: function( response ) {
                    $('.noor-loader.noor-loader-'+imageId+'').hide();
                    //console.log(response);
                    if (response.success) {
                        $('.noor-use-image-button-'+imageId+'').text(response.success);
                        $('.noor-use-image-button-'+imageId+'').css('background', 'green');
                        if( wp.media.frame.content.get() !== null) {
                            wp.media.frame.content.get().collection.props.set({ignore: (+ new Date())});
                            wp.media.frame.content.get().options.selection.reset();
                        } else {
                            wp.media.frame.library.props.set ({ignore: (+ new Date())});
                        }
                    }
                    if (response.error) {
                        $('.noor-use-image-button-'+imageId+'').text(response.error);
                        $('.noor-use-image-button-'+imageId+'').css('background', 'red');
                    }

                }
            });//ajax end
        });
    }

    noorUploadImageToWPmedia();


})(jQuery);