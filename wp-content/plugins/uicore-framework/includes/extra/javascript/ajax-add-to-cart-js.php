<?php

$js .= "
    jQuery(document).ready(function($) {
        $('.add_to_cart_button').on('click', function(e) {
        
            var button = $(this);
            var form = button.closest('form.cart');

            if (!form.length) {
                return;
            }

            e.preventDefault();
            e.stopImmediatePropagation();

            button.removeClass('added');
            button.addClass('loading');


            var product_id = button.data('product_id');
            const variation_input = form.find('input[name=\"variation_id\"]');
            if(variation_input.length){
                product_id = variation_input.val();
            }
            var quantity = form.find('input[name=\"quantity\"]').val() || 1;
            var variation = {};

            // Get all variation attribute fields
            form.find('.variations select').each(function() {
                var attribute_name = $(this).attr('name');
                var attribute_value = $(this).val();

                if (attribute_value.length > 0) {
                    variation[attribute_name] = attribute_value;
                }
            });

            // Prepare the data to send via AJAX
            var data = {
                action: 'uicore_ajax_add_to_cart',
                product_id: product_id,
                quantity: quantity,
                variation: variation
            };

            $.ajax({
                url: wc_add_to_cart_params.ajax_url,
                type: 'POST',
                data: data,
                success: function(response) {
                    if (!response.error) {
                        // Optionally, you could add a success message or update cart fragments
                        $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, button]);
                    } else {
                        const error = response.error_messages[0];
                        //can contain <a> tags, split by first adn remove the rest
                        const clean_error = error.split('<a')[0];
                        const error_message = clean_error ? error.notice : 'There was an error adding the product to the cart.';
                        alert(error_message);
                    }
                },
                error: function() {
                    alert('There was a problem adding the product to the cart.');
                }
            });
        });
    });
    ";