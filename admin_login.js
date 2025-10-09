document.forms['admin_form'].addEventListener("submit", registerform);
function registerform(event) {
    event.preventDefault(); // Stop form from submitting automatically

    //Get input values
    const username = document.forms['admin_form']['username'].value.trim();
    const password = document.forms['admin_form']['password'].value.trim();

    //Get error Elements
    const userError = document.getElementById("usernameError");
    const passError = document.getElementById("passwordError");

    //Define validation
    const usernameRegex = /^[A-Za-z0-9]{8,25}$/;
    const passwordRegex = /^[A-Za-z0-9]{8,25}$/;

    let isValid = true;

    //Validate username
    if (username === "") {
        userError.textContent = "Username cannot be empty";
        isValid = false;
    }
    else if (!usernameRegex.test(username)) {
        userError.textContent = "Username must be 8 to 25 alphanumeric characters";
        isValid = false;
    }
    else {
        userError.textContent = "";
    }

    //Validate password
    if (password === "") {
        passError.textContent = "Password cannot be empty";
        isValid = false;
    }
    else if (!passwordRegex.test(password)) {
        passError.textContent = "Password must be 8 to 25 alphanumeric characters";
        isValid = false;
    }
    else {
        passError.textContent = "";
    }

    // If both are valid, submit to PHP for login verification
    if (isValid) {
        document.forms['admin_form'].submit();
    }


}