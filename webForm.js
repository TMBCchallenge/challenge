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
    private emptyForm = {
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
    private responses = this.emptyForm;

    private async validateForm() {
        // Implement me!
		
        //Do not return until the form validator API's request is complete
        //If the promise fails / rejects, alert custom error message and set invalid flag to true
        var errorData = await $validator.validate()
            .catch((error) => {
                alert('Sorry please submit this form at a later time');
                return {invalid:true,errors:[]}
            });

        //If error form join all error message line by line and alert them to browser
        if(!errorData.invalid && errorData.errors.length > 0){
            const errors = errorData.errors.join("\n");
            alert(errors);
        }
        return !errorData.invalid;
    }

    private submitForm() {
        if (this.validateForm()) {
			// Going to call .toPromise() assuming the HttpClient instance is similar to the one used in Angular 10
            //Do not reset form until POST request successfully completes
            const promise = HttpClient.post('https://api.example.com/form/', this.responses).toPromise();
			promise.then(
				(success) => {
					this.resetForm();
				}, 
				(error) => {
					alert('Sorry please submit this form at a later time');
				}
			);
			
        }
    }

    private resetForm() {
        const blankForm  = {
            name: '',
            address: {
                street1: '',
                street2: '',
                city: '',
                state: '',
                zip: '',
            }
        };

        //The Form will not account for the nested field 'address'
        //Blank Form could also be cached or stored as a private / static variable
        this.responses   = Object.assign(this.emptyForm,blankForm);
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
