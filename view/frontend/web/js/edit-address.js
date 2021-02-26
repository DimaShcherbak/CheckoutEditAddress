/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'Magento_Ui/js/form/form',
    'ko',
    'Magento_Customer/js/model/customer',
    'Magento_Customer/js/model/address-list',
    'Magento_Checkout/js/model/address-converter',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/create-shipping-address',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/model/shipping-rates-validator',
    'Magento_Checkout/js/model/shipping-address/form-popup-state',
    'Magento_Checkout/js/model/shipping-service',
    'Magento_Checkout/js/action/select-shipping-method',
    'Magento_Checkout/js/model/shipping-rate-registry',
    'Magento_Checkout/js/action/set-shipping-information',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Ui/js/modal/modal',
    'Magento_Checkout/js/model/checkout-data-resolver',
    'Magento_Checkout/js/checkout-data',
    'uiRegistry',
    'mage/translate',
    'Magento_Checkout/js/model/shipping-rate-processor/customer-address',
    'Magento_Checkout/js/model/shipping-rate-service',
    'Magento_Checkout/js/view/shipping'
], function (
    $,
    _,
    Component,
    ko,
    customer,
    addressList,
    addressConverter,
    quote,
    createShippingAddress,
    selectShippingAddress,
    shippingRatesValidator,
    formPopUpState,
    shippingService,
    selectShippingMethodAction,
    rateRegistry,
    setShippingInformationAction,
    stepNavigator,
    modal,
    checkoutDataResolver,
    checkoutData,
    registry,
    $t,
    customerAddressProcessor
) {
    'use strict';

    var popUp = null;

    window.document.onload = setTimeout(function () {
        if ($('.shipping-address-item')[0].className == "shipping-address-item not-selected-item") {
            $('#custom-edit').css('display', 'none')
        } else {
            $('#custom-edit').css('display', 'block')
        }
    }, 2000);

    $(document).on("click",".action-select-shipping-item", function () {
        if ($('.shipping-address-item')[0].className == "shipping-address-item not-selected-item") {
            $('#custom-edit').css('display', 'none')
        } else {
            $('#custom-edit').css('display', 'block')
        }
    });

    return Component.extend({
        initialize: function () {
            this._super();

        },
        defaults: {
            template: 'RockLab_CheckoutEditAddress/edit-address-button',
            shippingFormTemplate: 'Magento_Checkout/shipping-address/form',
            shippingMethodListTemplate: 'Magento_Checkout/shipping-address/shipping-method-list',
            shippingMethodItemTemplate: 'Magento_Checkout/shipping-address/shipping-method-item'
        },
        visible: ko.observable(!quote.isVirtual()),
        errorValidationMessage: ko.observable(false),
        isCustomerLoggedIn: customer.isLoggedIn,
        isFormPopUpVisible: formPopUpState.isVisible,
        isFormInline: addressList().length === 0,
        isNewAddressAdded: ko.observable(false),
        saveInAddressBook: 1,
        quoteIsVirtual: quote.isVirtual(),

    /**
         * Edit address.
         */
        editAddress: function () {
            formPopUpState.isVisible(true);
            this.showPopup();
        },

        /**
         * Show popup.
         */
        showPopup: function () {
            var address = jQuery.parseJSON(window.checkoutConfig.customData);
            jQuery('[name="firstname"]').val(quote.shippingAddress().firstname)
            jQuery('[name="lastname"]').val(quote.shippingAddress().lastname)
            jQuery('[name="company"]').val(quote.shippingAddress().company)
            jQuery('[name="city"]').val(quote.shippingAddress().city)
            jQuery('[name="street[0]"]').val(quote.shippingAddress().street[0])
            jQuery('[name="street[1]"]').val(quote.shippingAddress().street[1])
            jQuery('[name="street[2]"]').val(quote.shippingAddress().street[2])
            jQuery('[name="country_id"]').val(quote.shippingAddress().countryId)
            if (quote.shippingAddress().regionId != 0) {
                jQuery('[name="region_id"]').val(quote.shippingAddress().regionId)
            } else {
                jQuery('[name="region"]').val(quote.shippingAddress().region)
            }
            jQuery('[name="postcode"]').val(quote.shippingAddress().postcode)
            jQuery('[name="telephone"]').val(quote.shippingAddress().telephone)

            $('.action-close').on('click', function () {
                $('.modal-inner-wrap').last().find('.action-save-address').css('display', 'block');
                $('.custom_btn').css('display', 'none');
            });
            $('.edit-address-link[data-bind="click: editAddress"]').on('click', function () {
                $(".modal-inner-wrap").show();
                $(".modals-overlay").show();

                $('.custom_btn').css('display', 'inline-block');
            });
            $('.amcheckout-button.-new-address').on('click', function () {
                $(".modal-inner-wrap").show();
                $(".modals-overlay").show();
            });

            $('.modal-inner-wrap').last().find('.action-save-address').css('display', 'none'); // скрываю кнопку ship here
            var btn = document.createElement("BUTTON");  // создаю кнопку
            btn.innerHTML = "SAVE";
            btn.classList.add('custom_btn') // добавляю класс для кнопеи
            if (document.querySelector('.custom_btn') === null) {
                $('.modal-inner-wrap').last().find($('.modal-footer')).append(btn);
                document.querySelector(".custom_btn").style.color = "white";
                document.querySelector(".custom_btn").style.background = "blue";
            }
            $(".custom_btn").on("click", function () {
                $.ajax({
                    url: '/checkouteditaddress/index/saveeditform',
                    method: 'post',
                    data: {
                        'form': $('#co-shipping-form').serialize(),
                        'customerAddressId': quote.shippingAddress().customerAddressId,
                        'regionCode': quote.shippingAddress().regionCode
                    },
                    success: function (data) {
                        var EditAddress = Object.assign({}, quote.shippingAddress());
                        EditAddress.firstname = data.firstname;
                        EditAddress.lastname = data.lastname;
                        EditAddress.street[0] = data.street[0];
                        EditAddress.city = data.city;
                        EditAddress.postcode = data.postcode;
                        EditAddress.countryId = data.country_id;

                        if (data.region_id != 0) {
                            EditAddress.regionId = data.region_id;
                            EditAddress.regionCode = data.region_code;
                            EditAddress.region = data.region;
                        } else {
                            EditAddress.regionId = null;
                            EditAddress.regionCode = null;
                            EditAddress.region = data.region;
                        }
                        EditAddress.telephone = data.telephone;
                        // window.location.reload(); // с перезагрузкой страницы
                        addressList().some(function (currentAddress) {
                            if (currentAddress.getKey() === EditAddress.getKey()) {
                                addressList.replace(currentAddress, EditAddress);
                            }
                        });
                        addressList.valueHasMutated();
                        Object.assign(quote.shippingAddress(), EditAddress);
                        Object.assign(quote.billingAddress(), EditAddress);
                        rateRegistry.set(quote.shippingAddress().getCacheKey(), null);
                        customerAddressProcessor.getRates(quote.shippingAddress());
                    },
                    error: function (data) { // Данные не отправлены
                        $('#co-shipping-form').html('Ошибка. Попробуйте еще раз');
                    }
                });
                $(".modal-inner-wrap").hide();
                $(".modals-overlay").hide();

            });
        },
    });
});
