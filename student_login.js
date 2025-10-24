document.forms['student_form'].addEventListener("submit", studentLogin);
function studentLogin(event) {
    event.preventDefault(); // Stop form from submitting automatically

    //Get input values
    const roll_num = document.forms['student_form']['roll_num'].value.trim();
    const semester = document.forms['student_form']['semester'].value.trim();

    //Get error Elements
    const rollError = document.getElementById("roll_num_Error");
    const semError = document.getElementById("semError");

    //Define validation
    // const rollRegex = /^[100-115]$/;
    const rollRegex = /^(BCA10[1-9]|BCA11[0-2])$/i;

    let isValid = true;

    //Validate roll_id
    if (roll_num === "") {
        rollError.textContent = "Roll Number cannot be empty";
        isValid = false;
    }
    else if (!rollRegex.test(roll_num)) {
        rollError.textContent = "Enter a valid roll e.g. BCA101–BCA112";
        isValid = false;
    }
    else {
        rollError.textContent = "";
    }

    //Validate semsester
    if (semester === "") {
        semError.textContent = "Please select your semester!";
        isValid = false;
    }
    else {
        semError.textContent = "";
    }

    // If both are valid, submit to PHP for login verification
    if (isValid) {
        document.forms['student_form'].submit();
    }


}