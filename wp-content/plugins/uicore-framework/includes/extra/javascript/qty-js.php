<?php
defined("ABSPATH") || exit();
//INCLUDED IN CLASS JS

$toggle = $settings["header_sd_toggle"];

$js .= '
document.addEventListener("DOMContentLoaded", function () {
    window.uicore_add_quantity_input_buttons = () => {
        // Add plus and minus buttons to each quantity input element
        document.querySelectorAll(".cart .quantity input:not([type=\'hidden\'])").forEach(function(input) {
            // Avoid adding buttons multiple times
            if (!input.previousElementSibling || !input.previousElementSibling.classList.contains("plus")) {
                const plus = document.createElement("div");
                plus.className = "plus";
                plus.textContent = "+";
                input.parentNode.insertBefore(plus, input);
            }
            if (!input.nextElementSibling || !input.nextElementSibling.classList.contains("minus")) {
                const minus = document.createElement("div");
                minus.className = "minus";
                minus.textContent = "-";
                input.parentNode.insertBefore(minus, input.nextSibling);
            }
        });

        // Add click event listeners to the plus buttons
        document.querySelectorAll(".cart .plus").forEach(function(plusBtn) {
            plusBtn.addEventListener("click", function () {
                const input = plusBtn.parentNode.querySelector("input:not([type=\'hidden\'])");
                if (!input) return;
                let currentVal = parseFloat(input.value);
                const max = parseFloat(input.getAttribute("max"));
                const step = parseFloat(input.getAttribute("step")) || 1;

                if (!isNaN(currentVal)) {
                    if (!isNaN(max) && currentVal + step > max) {
                        input.value = max;
                    } else {
                        input.value = (currentVal + step).toFixed(0);
                    }
                }
                if (isNaN(currentVal)) {
                    input.value = "1";
                }
                input.dispatchEvent(new Event("change", { bubbles: true }));
            });
        });

        // Add click event listeners to the minus buttons
        document.querySelectorAll(".cart .minus").forEach(function(minusBtn) {
            minusBtn.addEventListener("click", function () {
                const input = minusBtn.parentNode.querySelector("input:not([type=\'hidden\'])");
                if (!input) return;
                let currentVal = parseFloat(input.value);
                const min = parseFloat(input.getAttribute("min")) || 0;
                const step = parseFloat(input.getAttribute("step")) || 1;

                if (!isNaN(currentVal) && currentVal > min) {
                    input.value = (currentVal - step).toFixed(0);
                    input.dispatchEvent(new Event("change", { bubbles: true }));
                }
                if (isNaN(currentVal)) {
                    input.value = "0";
                    input.dispatchEvent(new Event("change", { bubbles: true }));
                }
            });
        });
    };

    // Run the function
    window.uicore_add_quantity_input_buttons();
});
';