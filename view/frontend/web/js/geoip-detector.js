define([
    'jquery',
    'mage/url'
], function ($, urlBuilder) {
    'use strict';

    return function (config, element) {
        // Сюди підставте ваш шлях до контролера
        let ajaxUrl = urlBuilder.build('geoip');

        console.log('GeoIP detector initialized', ajaxUrl);

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            dataType: 'json',
            showLoader: true, // показує Magento loader
            data: {
                // будь-які параметри, які хочете передати
            },
            success: function (response) {
                if (response.success) {
                    location.href = response.data.redirect_url;
                }
            },
            error: function (xhr, status, error) {
                console.error(error);
            }
        });
    };
});
