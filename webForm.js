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
    /* rules for the form to be passed into validator */
    private rules = [
        {
            'input':'name',
            'message':'Name is a required field',
            'required': true
        },
        {
            'input':'address.street1',
            'message':'Street1 is a required field',
            'required': true
        },
        {
            'input':'address.street2',
            'message':'',
            'required': false
        },
        {
            'input':'address.city',
            'message':'City is a required field',
            'required': true
        },
        {
            'input':'address.state',
            'message':'State is a required field',
            'required': true
        },
        {
            'input':'address.zip',
            'message':'Zip is a required field',
            'required': true
        },
    ];

    //private $validator = new FormValidator();
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

    private validateForm() {
        // we assume that FormValidator will process the fields based on the rules we pass into it
        let validator = new FormValidator(this.rules, this.responses);

        // now we try to validate
        validator.validate().then(obj => {
            // check obj
            if (obj.valid) {
                return true;
            } else {
                alert(obj.errors);
                return false;
            }
        }).catch(err => {
            // if we get an error return false
            alert("Sorry, please submit this form at a later time.");
            return false;
        });
    }

    private submitForm() {
        //(2) Can you refactor submitForm() so that it waits to reset the form after we are sure the form responses have been
        //successfully received by the API?
        if (this.validateForm()) {
            //HttpClient.post('https://api.example.com/form/', this.responses).then();
            // we could use the jquery post function and listen for the callback
            $.post('https://api.example.com/form/', this.responses).done(function() {
                this.resetForm();
            })

            // we could also modify the HttpClient class to return a similar response as FormValidator and then we could do this
            HttpClient.post('https://api.example.com/form/', this.responses).then(obj => {
                if (obj.valid) {
                    this.resetForm();
                } else {
                    alert(obj.errors);
                }
            }).catch(err => {
                alert("Sorry, please submit this form at a later time.");
            });
        }
    }

    private resetForm() {
        //this.responses = Object.assign({}, this.emptyForm);
        /* issue with the code above is that since this.emptyForm is reactive meaning that its changing as the user inputs text into the form
         * then when we reset the form its just going to re-copy the same values instead of clearing out the fields */
        $this.responses = $.each(emptyForm, function(index,value){
            if (index == 'address') {
                          $.each(value, function(ind,val){
                  console.log(ind);
                  emptyForm[index][ind] = '';
                  });
          } else {
            emptyForm[index] = '';
          }    
        });
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
