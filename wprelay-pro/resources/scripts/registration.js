jQuery(document).ready(function ($) {
  const rwpa_store_data = window.rwpa_relay_store;

  function clearFields() {
    $(".affiliate-field").val("");
  }

  function showValidationError(jqXhr) {
    let errors = jqXhr.responseJSON.data;

    for (error in errors) {
      if (error == "recaptcha") {
        alert(errors[error][0] ?? "Google Recaptcha Failed");
      }
      let errorElement = $(`#aff_${error}`).next();

      if (errorElement) {
        errorElement.html(errors[error][0]);
      }
    }
  }

  function clearErrors() {
    $(".wprelay-text-danger").html("");
  }

  $("#wprelay-registration-form").on("submit", function (e) {
    let current = this;
    let rwpa_store_data = window.rwpa_relay_store;
    e.preventDefault();
    clearErrors();

    let details = new FormData(this);

    if (rwpa_store_data.enable_spam_verification) {
      grecaptcha.ready(function () {
        // do request for recaptcha token
        // response is promise with passed token
        try {
          grecaptcha
            .execute(rwpa_store_data.recaptcha_site_key, {
              action: "validate_captcha",
            })
            .then(function (token) {
              // add token value to form
              document.getElementById("g-recaptcha-response").value = token;

              wpRelayDisableRegisterButton();

              let data = {};

              details.append("method", "new_affiliate_registration");
              details.append(
                "g-recaptcha-response",
                $("#g-recaptcha-response").val(),
              );

              details.forEach(function (value, key) {
                data[key] = value;
              });

              $.ajax(rwpa_store_data.ajax_url, {
                type: "POST", // http method
                data: data,
                contentType: "application/x-www-form-urlencoded",
                success: function (data, status, xhr) {
                  clearFields();
                  $("#wprelay-registration-form").addClass("hide");
                  $(
                    "#wprelay-registration-block #confirmation-block",
                  ).removeClass("hide");
                },
                error: function (jqXhr, textStatus, errorMessage) {
                  if (jqXhr.status == 422) {
                    showValidationError(jqXhr);
                    alert("Validation Failed");
                  } else {
                    alert(
                      "Server Error Occurred, Unable to Capture Your Request",
                    );
                  }
                },
                complete: function () {
                  wpRelayEnableRegisterButton();
                },
              });
            });
        } catch (err) {
          console.log(err.message);
          alert(
            "Invalid Site Key Provided for Recaptcha. For further assistance, kindly contact the site owner directly.",
          );
        }
      });
    } else {
      wpRelayDisableRegisterButton();

      let data = {};

      details.append("method", "new_affiliate_registration");

      details.forEach(function (value, key) {
        data[key] = value;
      });

      $.ajax(rwpa_store_data.ajax_url, {
        type: "POST", // http method
        data: data,
        contentType: "application/x-www-form-urlencoded",
        success: function (data, status, xhr) {
          clearFields();
          $("#wprelay-registration-form").addClass("hide");
          $("#wprelay-registration-block #confirmation-block").removeClass(
            "hide",
          );
        },
        error: function (jqXhr, textStatus, errorMessage) {
          if (jqXhr.status == 422) {
            showValidationError(jqXhr);
            alert("Validation Failed");
          } else {
            alert("Server Error Occurred, Unable to Capture Your Request");
          }
        },
        complete: function () {
          wpRelayEnableRegisterButton();
        },
      });
    }
  });

  $("#aff_country").change(function (e) {
    let rwpa_store_data = window.rwpa_relay_store;
    let countryCode = e.target.value;

    // let details = new FormData()

    let data = {
      country_code: countryCode,
      action: $("#wp_relay_action_name").val(),
      method: "get_wc_states_for_store_front",
      store_front: true,
    };
    $.ajax(rwpa_store_data.ajax_url, {
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

  function wpRelayDisableRegisterButton() {
    $("#wprelay-reg-btn").append('<span class="wprelay-loader"></span>');
    $("#wprelay-reg-btn").attr("disabled", true);
    $("#wprelay-reg-btn").css("opacity", 0.5);
  }

  function wpRelayEnableRegisterButton() {
    $("#wprelay-reg-btn").attr("disabled", false);
    $("#wprelay-reg-btn").css("opacity", 1);
    $("#wprelay-reg-btn .wprelay-loader").remove();
  }
});
