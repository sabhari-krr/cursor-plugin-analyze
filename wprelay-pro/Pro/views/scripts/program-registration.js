jQuery(document).ready(function ($) {
  const rwpa_store_data = window.rwpa_relay_store;

  function clearPBAFFields() {
    $(".wprelay-pbar-affiliate-field").val("");
  }

  function showValidationErrorForPBAF(jqXhr) {
    let errors = jqXhr.responseJSON.data;

    for (error in errors) {
      if (error == "recaptcha") {
        alert(errors[error][0] ?? "Google Recaptcha Failed");
      }
      let errorElement = $(`#aff_${error}`).next(".wprelay-text-danger");

      if (errorElement) {
        errorElement.html(errors[error][0]);
      }
    }
  }

  function clearErrorsForPBAF() {
    $(".wprelay-text-danger").html("");
  }

  $("#wprelay-pbaf-reg-btn").on("click", function (e) {
    $("#wprelay-pbar-form").submit();
  });

  $("#wprelay-pbar-form").on("submit", function (e) {
    let rwpa_store_data = window.rwpa_relay_store;
    e.preventDefault();
    clearErrorsForPBAF();

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

              wpRelayDisableRegisterButtonForPBAF();

              let data = {};

              details.append(
                "method",
                "new_affiliate_registration_for_specific_program",
              );
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
                  clearPBAFFields();
                  let confirmation_html = data.data.confirmation_html;
                  $(".wprelay-pbar-fields-container").addClass("hide");
                  $("#wprelay-pbar-block").append(confirmation_html);
                },
                error: function (jqXhr, textStatus, errorMessage) {
                  if (jqXhr.status == 422) {
                    showValidationErrorForPBAF(jqXhr);
                    alert("Validation Failed");
                  } else {
                    alert(
                      "Server Error Occurred, Unable to Capture Your Request",
                    );
                  }
                },
                complete: function () {
                  wpRelayEnableRegisterButtonForPBAF();
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
      wpRelayDisableRegisterButtonForPBAF();

      let data = {};

      details.append(
        "method",
        "new_affiliate_registration_for_specific_program",
      );

      details.forEach(function (value, key) {
        data[key] = value;
      });

      $.ajax(rwpa_store_data.ajax_url, {
        type: "POST", // http method
        data: data,
        contentType: "application/x-www-form-urlencoded",
        success: function (data, status, xhr) {
          clearPBAFFields();
          let confirmation_html = data.data.confirmation_html;
          $(".wprelay-pbar-fields-container").addClass("hide");
          $("#wprelay-pbar-block").append(confirmation_html);
        },
        error: function (jqXhr, textStatus, errorMessage) {
          if (jqXhr.status == 422) {
            showValidationErrorForPBAF(jqXhr);
            alert("Validation Failed");
          } else {
            alert("Server Error Occurred, Unable to Capture Your Request");
          }
        },
        complete: function () {
          wpRelayEnableRegisterButtonForPBAF();
        },
      });
    }
  });

  function wpRelayDisableRegisterButtonForPBAF() {
    $("#wprelay-pbaf-reg-btn").append('<span class="wprelay-loader"></span>');
    $("#wprelay-pbaf-reg-btn").attr("disabled", true);
    $("#wprelay-pbaf-reg-btn").css("opacity", 0.5);
  }

  function wpRelayEnableRegisterButtonForPBAF() {
    $("#wprelay-pbaf-reg-btn").attr("disabled", false);
    $("#wprelay-pbaf-reg-btn").css("opacity", 1);
    $("#wprelay-pbaf-reg-btn .wprelay-loader").remove();
  }
});
