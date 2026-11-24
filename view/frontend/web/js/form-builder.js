define([
    'jquery',
    'underscore',
    'mage/template'
], function ($, _, mageTemplate) {
        'use strict';

        // Template used to generate the form, based on controller JSON data
        let template =
            '<form action="<%= data.action %>" method="POST" hidden enctype="application/x-www-form-urlencoded">' +
            '<% _.each(data.fields, function(val, key){ %>' +
            '<input value="<%= val %>" name="<%= key %>" type="hidden">' +
            '<% }); %>' +
            '</form>';

        return function (response) {
            let t = mageTemplate(template);
            let formHtml = t({
                data: {
                    action: response.action, // notice the "action" key is the form action you return from controller
                    fields: response.fields // fields are inputs of the form returned from controller
                }
            });
            
            return $(formHtml).appendTo($('[data-container="body"]')); // Finally, append the form to the <body> element (or more accurately to the [data-container="body"] element)
        };
    }
);
