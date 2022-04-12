/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'ko',
        'Magento_Checkout/js/view/payment/default'
    ],
    function (ko, Component) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Ynote_Lydia/payment/lydia_standard'
            },
            /**
             * Get value of instruction field.
             * @returns {String}
             */
            getInstructions: function () {

                window.lydiaRedirectUrl = 'lydia/payment/redirect';
                this.isChecked.subscribe(function (code) {
                    if(code === 'lydia_standard'){
                        window.lydiaRedirectUrl = 'lydia/payment/redirect';
                    }else {
                        window.lydiaRedirectUrl = false;
                    }
                });

            }
        });
    }
);
