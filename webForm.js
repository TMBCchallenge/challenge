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
 *      In this way of initializing the "responses", a copy of "emptyForm" is actually made.
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
    private responses = this.resetForm();

    private validateForm() {
        var word = /^[a-zA-Z\s]+$/;
        var st1 = /^(?=.*\d)[a-zA-Z\s\d\/]+$/;
        var st2 = /^[a-zA-Z\s\d\/]*$/;
        var digit  = /^\d{5}(-\d{4})?$/;

        if(!word.test(this.name.value)) {
            alert("Please enter a name with only letters and whitespace!");
            this.name.focus();
            return false;
          }
        
          if(!st1.test(this.street1.value)) {
            alert("Please enter a valid street address!");
            this.street1.focus();
            return false;
          }

          if(!st2.test(this.street2.value)) {
            alert("Please enter a valid second street address!");
            this.street2.focus();
            return false;
          }

          if(!word.test(this.city.value)) {
            alert("Please enter a valid city!");
            this.city.focus();
            return false;
          }

          if(!word.test(this.state.value)) {
            alert("Please enter a valid State!");
            this.state.focus();
            return false;
          }

          if(!digit.test(this.zip.value)) {
            alert("Please enter a 5-digit zip code!");
            this.zip.focus();
            return false;
          }
      
          return true;

    }

   /* private submitForm() {
        if (this.validateForm()) {
            HttpClient.post('https://api.example.com/form/', this.responses);
            this.resetForm();
        }
    } */

    private submitForm() {
        if (this.validateForm()) {
            
            fetch('https://api.example.com/form/', this.responses)
            .then((response) => {
              if (!response.ok) {
                alert('Sorry, please submit this form at a later time.', error);
              }
              return response.json();
            })
            .then((result) => {
              return {
                        valid: response.ok,
                        errors: error
                    }
            })
            .catch((error) => {
              console.log('Sorry, please submit this form at a later time.', error);
            });

            this.resetForm();
        }
    }

    private resetForm() {
        //this.responses = Object.assign({}, this.emptyForm);
        this.name = '';
        this.address.street1 = '';
        this.address.street2 = '';
        this.address.city = '';
        this.address.state = '';
        this.address.zip = '';
        
        return this.responses;
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
