// Function to validate the Contact Number field
function validateContactNumber(number) {
    var numberRegex = /^(98|97)\d{8}$/;
    return numberRegex.test(number);
}

// Function to validate the Email field
function validateEmail(email) {
    var emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z]+\.[a-zA-Z]{2,}$/;
    return emailRegex.test(email);
}

function validateFullName(name) {
    var nameRegex = /^[a-zA-Z]+( [a-zA-Z]+)*$/;
    return nameRegex.test(name);
}

function validateNickname(nickname) {
    var nicknameRegex = /^[a-zA-Z0-9 ]+$/;
    return nicknameRegex.test(nickname);
}

function validateAddress(address) {
    var addressRegex = /^(?=.*[^\s])[\w\s\/\-,]+$/;
    return addressRegex.test(address);
}

// Function to perform real-time validation for each field on key press
$(document).ready(function() {
    $("#full_name").on("keyup", function() {
        var full_name = $(this).val();
        var errorSpan = $("#full_name_error");
        if (validateFullName(full_name)) {
            errorSpan.text("");
        } else {
            errorSpan.text("Invalid Full Name.");
        }
    });

    $("#nickname").on("keyup", function() {
        var nickname = $(this).val();
        var errorSpan = $("#nickname_error");
        if (validateNickname(nickname)) {
            errorSpan.text("");
        } else {
            errorSpan.text("Invalid Nickname.");
        }
    });

    // Contact Number validation
    $("#phone_number").on("keyup", function() {
        var phone_number = $(this).val();
        var errorSpan = $("#phone_number_error");
        if (validateContactNumber(phone_number)) {
            errorSpan.text("");
        } else {
            errorSpan.text("Invalid Contact Number. Please enter 10 digits starting with 98 or 97.");
        }
    });

    // Email validation
    $("#email").on("keyup", function() {
        var email = $(this).val();
        var errorSpan = $("#email_error");
        if (validateEmail(email)) {
            errorSpan.text("");
        } else {
            errorSpan.text("Invalid Email.");
        }
    });

    // Address validation
    $("#address").on("keyup", function() {
        var address = $(this).val();
        var errorSpan = $("#address_error");
        if (validateAddress(address)) {
            errorSpan.text("");
        } else {
            errorSpan.text("Invalid Address. Please use letters, numbers, spaces, /, and -.");
        }
    });
});