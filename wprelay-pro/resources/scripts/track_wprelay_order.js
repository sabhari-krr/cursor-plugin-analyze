document.addEventListener("DOMContentLoaded", function () {
    // Function to extract a query parameter from the URL
    const store = window.rwpa_relay_store;

    let urlvariable = store.affiliate_url_variable;

    if (!urlvariable) {
        urlvariable = 'aff';
    }

    // Get the affiliate from the URL
    const affiliateReferralID = getQueryParam(urlvariable);
    const expiryDate = store.cookie_duration;

    if (affiliateReferralID) {
        // Set the affiliate in a cookie with a 90-day expiration
        let expirationDate = new Date();
        expirationDate.setDate(expirationDate.getDate() + parseInt(expiryDate));

// Format the expiration date to a GMT string
        let expires = "expires=" + expirationDate.toUTCString();

        let domain = store.cookie_host_name ?? window.location.hostname;

        document.cookie = `${urlvariable}=${affiliateReferralID}; domain=${domain}; ${expires}; path=/`;

        jQuery.ajax(store.ajax_url, {
            type: 'POST',  // http method
            data: {
                action: 'guest_apis',
                method: 'capture_customer_visit',
                landing_url: window.location.href,
                referral_code: affiliateReferralID,
            },
            contentType: 'application/x-www-form-urlencoded',
            success: function (data, status, xhr) {
                console.log(data);
            },
            error: function (jqXhr, textStatus, errorMessage) {
                console.log(errorMessage)
            }
        });
    }

    if (document.cookie.indexOf(`${urlvariable}=`) !== -1) {
        // Register an event handler for successful order completion using WooCommerce AJAX
        // Check if the order is completed
        if (jQuery('.woocommerce-order-received').length > 0) {
            // Remove the cookie when the order is completed
            let domain = store.cookie_host_name ?? window.location.hostname;
            document.cookie = `${urlvariable}=; domain=${domain}; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;`;
        }
    }

    jQuery('.checkout #billing_email').on('change', function() {
        // Trigger the update order review event
        jQuery('body').trigger('update_checkout');
    });
});

function getQueryParam(param) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(param);
}