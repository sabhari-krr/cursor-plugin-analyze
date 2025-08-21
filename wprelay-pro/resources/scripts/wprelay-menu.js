jQuery(document).ready(function ($) {
  const rwpa_store = window.rwpa_relay_store;
  $("#wprelay-detail-form").submit(function (e) {
    e.preventDefault();
    affclearErrors();
    wpRelayDisableSaveButton();

    let data = {};
    let details = new FormData(this);

    details.append("action", "relay_affiliate");

    details.forEach(function (value, key) {
      data[key] = value;
    });

    $.ajax(rwpa_store.ajax_url, {
      type: "POST", // http method
      data: data,
      contentType: "application/x-www-form-urlencoded",
      success: function (data, status, xhr) {
        alert("Affiliate Details updated successfully!");
      },
      error: function (jqXhr, textStatus, errorMessage) {
        if (jqXhr.status == 422) {
          affShowValidationError(jqXhr);
          alert("Validation Failed");
          return;
        }
        alert("Server Error Occurred, Unable to Update Affiliate Details");
      },
      complete: function () {
        wpRelayEnableSaveButton();
      },
    });
  });

  $("#affiliate-multi-currency-dropdown").change(function (e) {
    var currentUrl = window.location.href;
    // Parse the URL
    var url = new URL(currentUrl);

    // Add or modify query parameters
    url.searchParams.set("wprelay-currency", e.target.value);

    // Assemble the modified components into a new URL
    var newUrl = url.toString();

    window.location.href = newUrl;
  });

  $("#wprelay-referral-link-copy").click(function (e) {
    let link = $("#wprelay-referral-link").text();
    if ("clipboard" in navigator) {
      // @ts-ignore
      navigator.clipboard.writeText(link);
    } else {
      // @ts-ignore
      document.execCommand("copy", true, link);
    }

    $("#wprelay-referral-link-copy").text("copied");

    setTimeout(() => {
      $("#wprelay-referral-link-copy").html('<i class="rwp rwp-copy"></i>');
    }, 400);
  });

  $("#aff_country").change(function (e) {
    let countryCode = e.target.value;

    // let details = new FormData()

    let data = {
      country_code: countryCode,
      action: "relay_affiliate",
      method: "get_wc_states",
      _wp_nonce_key: "wprelay_state_list_nonce",
      _wp_nonce: rwpa_store.nonces.wprelay_state_list_nonce,
      store_front: true,
    };
    // details.append('country_code', countryCode)
    // details.append('action', 'relay_wp')
    // details.append('method', 'get_wc_state_list')
    // details.append('_wp_nonce_key', 'wprelay_state_list_nonce')

    $.ajax(rwpa_store.ajax_url, {
      type: "POST", // http method
      data: data,
      success: function (data, status, xhr) {
        updateSelectOptionsFromArray("aff_state", data.data);
      },
      error: function (jqXhr, textStatus, errorMessage) {
        console.log("Unable to Fetch State List");
        updateSelectOptionsFromArray("aff_state", []);
      },
    });
  });

  $(".wprelay-copy-content").click(function (e) {
    let current = this;

    $(current).html("copied");
    setTimeout(function () {
      $(current).html('<i class="rwp rwp-copy"></i>');
    }, 200);

    let coupon = $(current).attr("data-content");

    if ("clipboard" in navigator) {
      // @ts-ignore
      navigator.clipboard.writeText(coupon);
    } else {
      // @ts-ignore
      document.execCommand("copy", true, coupon);
    }
  });

  function affShowValidationError(jqXhr) {
    let errors = jqXhr.responseJSON.data;

    console.log(errors);
    for (error in errors) {
      let errorElement = $(`#aff_${error}`).next();

      if (errorElement) {
        errorElement.html(errors[error][0]);
      }
    }
  }

  function affclearErrors() {
    $(".wprelay-text-danger").html("");
  }

  function updateSelectOptionsFromArray(
    selectElementId,
    options,
    selectedValue = null,
  ) {
    let selectElement = document.getElementById(selectElementId);
    selectElement.innerHTML = ""; // Clear existing options

    let optionElement = document.createElement("option");
    optionElement.value = "";
    optionElement.textContent = "Select Option";
    selectElement.appendChild(optionElement);

    options.map((item) => {
      let label = item.label;
      optionElement = document.createElement("option");
      optionElement.value = item.value;
      optionElement.textContent = label;
      if (selectedValue !== null && item.value === selectedValue) {
        optionElement.selected = true;
      }
      selectElement.appendChild(optionElement);
    });
  }

  function wpRelayDisableSaveButton() {
    $("#wprelay-save-changes-btn").append(
      '<span class="wprelay-loader"></span>',
    );
    $("#wprelay-save-changes-btn").attr("disabled", true);
    $("#wprelay-save-changes-btn").css("opacity", 0.5);
  }

  function wpRelayEnableSaveButton() {
    $("#wprelay-save-changes-btn").attr("disabled", false);
    $("#wprelay-save-changes-btn").css("opacity", 1);
    $("#wprelay-save-changes-btn .wprelay-loader").remove();
  }
});

function openSettings(evt, name) {
  let i, tabcontent, tablinks;
  tabcontent = document.getElementsByClassName("wprelay-settings-tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }
  tablinks = document.getElementsByClassName("wprelay-settings-tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }
  document.getElementById(name).style.display = "block";
  evt.currentTarget.className += " active";
}
