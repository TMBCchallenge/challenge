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
 *
 * BH Notes:
 *  (3) Because responses says that it is reactively updated from the UI, I'm assuming there's some sort of an "updateState"
 *      method that is used to accomplish that.  I'm going to put a placeholder method of that name.
 *      (a) When the constructor of class Form is run, it should force an "updateState" to get the current status of the form
 *          rather then just blasting an empty set of values in there.  updateState will then render the form with that state to
 *          insure things are in sync from the start.
 *      (b) We also need some kind of "render" method which will re-render the entire form, or if we have subcomponents for
 *          the form fields, etc., we should be able to re-render a portion of the form if  we change the state such as when
 *          we do a "resetForm" where we have changed the state data.
 *
 *      Of course a framework like React handles a lot of those details for us so we don't have to re-invent the wheel.
 */

import FormValidator from 'validator-lib';
import HttpClient from 'http-lib';

class Form {
    #validator = new FormValidator();
    #emptyForm = {
        name: '',
        address: {
            street1: '',
            street2: '',
            city: '',
            state: '',
            zip: '',
        },
    };

    // Assume this is reactive (i.e. if the user updates the form fields in the UI, this object is updated accordingly)
    // #responses = this.emptyForm;

    constructor() {
        this.updateState();
    }

    #validateForm() {
        let bReturn = false;
        const validator = new FormValidator();
        let validationPromise = validator.validate(responses);

        validationPromise.then(
            responseObj => {
                if (responseObj.valid) {
                    bReturn = true;
                } else {
                    alert("The following errors were found in the form\n" + implode("\n", responeObj.errors));
                }
            },
            rejectObj => alert("Sorry, please submit this form at a later time.")
        );
        return bReturn;
   }

   #postFormSubmission() {
       return new Promise(function(resolve, reject) {
           let responsObj = HttpClient.post('https://api.example.com/form/', this.responses);
           if (responseObj.status == 200) {
               resolve("OK");
           } else {
               reject(new Error("Bad status from API " + responseObj.status));
           }
       });
   }

    #submitForm() {
        if (this.validateForm()) {
            let promise = this.postFormSubmission()
                .then(
                    result => this.resetForm(),
                    error => alert("Error submitting form. Please try again.")
                );
        }
    }

    #resetForm() {
        this.responses = Object.assign({}, this.emptyForm);
        this.renderForm();
    }

    #updateState() {
        // Grabs the current form field data and populates the "responses" object.
        // Could also accept the data as a paramter which would be better for testing.
        this.renderForm();
    }

    #renderForm() {
        // re-renders the entire form based on the current state in the "responses" object.
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
