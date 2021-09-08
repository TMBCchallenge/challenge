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
 *      //A clean way to pass by value instead of by reference is just to declare a function which returns a copy of the object outside of its own scope
 */

import FormValidator from 'validator-lib';
import HttpClient from 'http-lib';

class Form {
    private $validator = new FormValidator();

    private returnEmptyForm() {
        return {
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
    private responses = this.returnEmptyForm(); //This was previously mutable by reference

    async #validateForm(): boolean {
        try {
            var result = await FormValidator.validate()
            if (!Array.isArray(result)) {
                return result //Result in this case would be the actual result validation and is bool true
            } else {
                // - If the validate() function returns with validation errors, display the errors in a browser alert
                alert(result.join("\n"))
                return false;
            }
        } catch {
            // If the validate() function returns with a failed promise (meaning the API is not available at the moment),
            // display a browser alert stating "Sorry, please submit this form at a later time."

            alert("Sorry, please submit this form at a later time.");
            return false;
        }


    }

    async #submitForm() {
        // Can you refactor submitForm() so that it waits to reset the form after we are sure the form responses have been
        // successfully received by the API?
        if (this.validateForm()) {
            try {
                const fetchResponse = await HttpClient.post('https://api.example.com/form/', this.responses)
                const data = await fetchResponse.json();
                if (data) {
                    this.resetForm();
                }
            } catch (e) {
                return e;
            }
        }


    }

    private resetForm() {
        this.responses = this.returnEmptyForm();
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
