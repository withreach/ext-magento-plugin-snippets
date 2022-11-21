/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
  [
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/model/url-builder',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/redirect-on-success',
    'Magento_Checkout/js/action/set-payment-information',
    'mage/storage',
  ],
  function (
    Component,
    urlBuilder,
    quote,
    redirectOnSuccessAction,
    setPaymentInformationAction,
    storage
    ) {
    'use strict';

    return Component.extend({
      defaults: {
        template: 'Reach_Payment/payment/form',
        transactionResult: ''
      },

      rchInstance: null,

      initObservable: function () {

        this._super()
          .observe([
            'transactionResult',
            'sessionId'
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
            'transaction_result': this.transactionResult(),
            'session_id': this.sessionId()
          }
        };
      },

      loadDropIn: function(){
        var self = this;
        console.log('loaded the drop in');
        var payload = JSON.stringify({
          cartId: quote.getQuoteId(),
          guestEmail: quote.guestEmail
        });

        // Might be able to replace this by using the ConfigProvider
        let sessionUrl = urlBuilder.createUrl('/reach/session',{});
        storage.post(sessionUrl, payload)
          .done(
            function (response) {
              // provide session id
              const id = response.session_id;

              // define target element
              // var target = document.querySelector("#rch-cc");
              // - or - pass the document selector only
              var target = "#rch-cc";
              // // define callback method that will be executed on payment success
              const onSuccess = () => {
                self.redirectAfterPlaceOrder = true;
                redirectOnSuccessAction.execute();

                console.log("great success 👍");
              };
              //
              const onFailure = error => {
                console.log("failure occurred ❌ Error code: " +
                  error.errorCode + ", Error message: " +
                  error.errorMessage + " ❌");
              };

              console.log('Session ID: ' + id);

              // initialize library
              const rchInstance = RCH_CC.rchInit({ target, id, onSuccess, onFailure, hideSubmitButton: true });
              self.rchInstance = rchInstance;
              self.sessionId(id);
              setPaymentInformationAction(self.messageContainer, self.getData());
            }
          ).fail(
          function (response) {
            console.log(response);
          }
        );
      },

      afterPlaceOrder: function() {
        this.redirectAfterPlaceOrder = false;
        this.rchInstance.submitPayment();
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