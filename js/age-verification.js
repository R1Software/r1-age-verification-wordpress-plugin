/**
 * Plugin Name: R1 Age Verification - jQuery
 * Plugin URI: https://r1software.com/age-verification
 * Description: A plugin to add an age verification overlay to your WordPress site.
 * Version: 1.0
 * Author: R1 Software
 * Author URI: https://r1software.com
 */
jQuery(document).ready(function($) {
    var currentYear = new Date().getFullYear();
    var ageVerificationCookie = "ageVerified";

    // Set the overlay and default images from the WordPress options
    $(".egiTHk").css("background-image", "url('" + ageVerificationOptions.overlayImage + "')");
    $(".r1-age-verification-item-image-image").attr("data-default-image", ageVerificationOptions.defaultImage);

    function isAgeValid(year) {
        var age = currentYear - year;
        return age >= ageVerificationOptions.thresholdAge;
    }

    function hideDivAndSetCookie() {
        $("#R1_PORTAL").hide();
        var cookieExpiration = new Date();
        cookieExpiration.setTime(cookieExpiration.getTime() + (ageVerificationOptions.cookieDuration * 24 * 60 * 60 * 1000));
        document.cookie = ageVerificationCookie + "=true; expires=" + cookieExpiration.toUTCString() + "; path=/";
    }

    function showError(message) {
        $(".r1-age-verification-item-error").text(message).addClass("r1-age-verification-item-errorShow");
        setTimeout(function() {
            $(".r1-age-verification-item-error").removeClass("r1-age-verification-item-errorShow");
        }, 3000);
    }

    $(".r1-age-verification-item-allow-year-input").on("input", function() {
        var input = $(this).val();
        var sanitizedInput = input.replace(/[^0-9]/g, '').slice(0, 4);
        if (parseInt(sanitizedInput) > currentYear) {
            sanitizedInput = currentYear.toString();
        }
        $(this).val(sanitizedInput);
    });

    $(".r1-age-verification-item-allow-year-submit").click(function(e) {
        e.preventDefault();
        var year = parseInt($(".r1-age-verification-item-allow-year-input").val());

        if (isNaN(year) || year.toString().length !== 4 || year > currentYear) {
            showError("Please enter a valid 4-digit year not greater than the current year.");
        } else if (isAgeValid(year)) {
            hideDivAndSetCookie();
        } else {
            showError("You are not old enough to view this content. Please navigate away.");
            // window.location.href = "https://google.com"; // Redirect to Google Search
        }
    });

    // Check if the cookie exists
    if (document.cookie.indexOf(ageVerificationCookie + "=true") !== -1) {
        $("#R1_PORTAL").hide();
    } else {
        $("#R1_PORTAL").show();
    }
});