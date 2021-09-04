/**
 * This a JavaScript class that manages a web form, including functionality for validating fields and submitting form values.
 * The template of the web form is not included, nor is it needed to complete this activity.
 *
 * Instructions:
 *
 * (1) Implement the validateForm() function.
 *      - Should return a boolean: return true if the form has valid responses, false otherwise
 *      - Should utilize FormValidator.validate() to validate the form fields
 *          (documentation on FormValidator can be found at the bottom of this file)
 *      - If the validate() function returns with validation errors, display the errors in a browser alert
 *      - If the validate() function returns with a failed promise (meaning the API is not available at the moment),
 *          display a browser alert stating "Sorry, please submit this form at a later time."
 *
 *  (2) Can you refactor submitForm() so that it waits to reset the form after we are sure the form responses have been
 *      successfully received by the API?
 *
 *  (3) What is wrong with how we initially set "responses" to the default values of "emptyForm" as well as the implementation
 *      of resetForm()? Can you refactor the inital setting of responses and/or the resetForm() function to achieve the
 *      desired behavior?
 */

// import FormValidator from 'validator-lib';
// import HttpClient from 'http-lib';

class FormValidator {
    validate(fields) {
        // validation here
        var response = {
            success: false,
        };

        // name required
        if (fields.name == '') {
            response.errormessage = 'Name is missing';
            return response;
        }

        response.success = true;
        return response;
    }
}

class Form {
    constructor() {
        this.validator = new FormValidator();
        this.emptyForm = {
            name: '',
            address: {
                street1: '',
                street2: '',
                city: '',
                state: '',
                zip: '',
            },
        };
    }

    // Assume this is reactive (i.e. if the user updates the form fields in the UI, this object is updated accordingly)
    // var responses = this.emptyForm;

    validateForm() {
        // Implement me!
        var fields = this.getFields();
        var response = this.validator.validate(fields);

        if (response.success == 'false') {
            alert(response.errormessage);
        }

        return response.success;
    }

    getFields() {
        // Assume this is reactive (i.e. if the user updates the form fields in the UI, this object is updated accordingly)
        // return {
        //     name: 'Ronn',
        //     address: {
        //         street1: 'd',
        //         street2: 'aaa',
        //         city: 'LA',
        //         state: 'CA',
        //         zip: '12345',
        //     },
        // };
        return this.emptyForm();
    }

    submitForm() {
        if (this.validateForm()) {
            var form = this;
            HttpClient.post('https://api.example.com/form/', function (response) {
                if (response.success) {
                    form.resetForm();
                }
            });
        }
    }

    resetForm() {
        this.responses = Object.assign({}, this.emptyForm);
    }
}

/**
 * FormValidator class
 *
 * Methods:
 *  validate()
 *
 *  - Makes a call to the API to ensure that the form responses are valid
 *  - Returns a Promise that, on resolve, returns an object of the structure:
 *      {
 *          valid: boolean;
 *          errors: string[];
 *      }
 *  - Note: Potentially can return in a Promise reject in the case the API is not available in that moment
 */
