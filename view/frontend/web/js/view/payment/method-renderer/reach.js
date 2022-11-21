/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
  [
    'Magento_Checkout/js/view/payment/default'
  ],
  function (Component) {
    'use strict';

    return Component.extend({
      defaults: {
        template: 'Reach_Payment/payment/form',
        transactionResult: ''
      },

      initObservable: function () {

        this._super()
          .observe([
            'transactionResult'
          ]);
        return this;
      },

      getCode: function() {
        return 'reach_dropin';
      },

      getData: function() {
        return {
          'method': this.item.method,
          'additional_data': {
            'transaction_result': this.transactionResult()
          }
        };
      },

      getTransactionResults: function() {
        return _.map(window.checkoutConfig.payment.reach_dropin.transactionResults, function(value, key) {
          return {
            'value': key,
            'transaction_result': value
          }
        });
      }
    });
  }
);