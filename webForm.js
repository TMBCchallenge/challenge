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

import FormValidator from 'validator-lib';
import HttpClient from 'http-lib';

class Form {
    private $validator = new FormValidator();
    private status = false;
    private emptyForm = null;

    // Assume this is reactive (i.e. if the user updates the form fields in the UI, this object is updated accordingly)
    private responses = null;


    //use constructor to initialize with empty fields on loading the form. this ensure the form is always loads with empty form

    //#3 a reactive form ensures to update the form fields in responsive as the user fills the form,
    //this makes the reset form not to update the fields correctly

    constructor (name = '', street1 = '', street2 = '', city = '', state = '', zip = '') {
        this.emptyForm = this.responses = {
            name: name,
            address: {
                street1: street1,
                street2: street2,
                city: city,
                state: state,
                zip: zip
            }
        };
    }

    private validateForm() {
        var formRules = [
            {
                validator: "required",
                fields: [ "name" ],
                message: "Name required"
            },
            {
                validator: "required",
                fields: [ "address.street1" ],
                message: "Street address required"
            },
            {
                validator: "required",
                fields: [ "address.city" ],
                message: "City required"
            },
            {
                validator: "required",
                fields: [ "address.state" ],
                message: "State required"
            },
            {
                validator: "number",
                fields: [ "address.zip" ],
                message: "Zipcode required"
            }
        ];

        validator = new FormValidator(formRules, this.emptyForm);
        validator.validate().then(function(result) {
            if (!result.boolean) {
                let errors = '';
                for (i = 0; i < result.errors.length; i++) {
                    errors += result.errors[i] + "\n";
                }
                alert("You Have errors in your input. Please check the errors below: \n" + errors);
            } else {
                this.status = true;
            }
        }, function(error) {
            alert("There is some problem in validating the inputs. Please try later.");
        });

        return this.status;
    }

    // async always returns a promise
    // await makes JavaScript wait until that promise settles and returns its result.
    async submitForm() {
        var formSubmitted = false;
        if (this.validateForm()) {
            try {
                let response = await HttpClient.post("https://api.example.com/form/", this.responses);
                if (response) {
                    this.resetForm();
                    formSubmitted = true;
                }
            }
            catch (error) {
                alert("Sorry, please submit this form at a later time.")
            }
            return formSubmitted;
        }
    }

    private resetForm() {
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
