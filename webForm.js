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

class Form {
  constructor() {
    // Assume this is reactive (i.e. if the user updates the form fields in the UI, this object is updated accordingly)
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
    this.responses = JSON.parse(JSON.stringify(this.emptyForm));
    this.validator = new FormValidator();
    this.httpClient = new HttpClient();
  }

  /**
   *      - Should return a boolean: return true if the form has valid responses, false otherwise
   *      - Should utilize FormValidator.validate() to validate the form fields
   *          (documentation on FormValidator can be found at the bottom of this file)
   *      - If the validate() function returns with validation errors, display the errors in a browser alert
   *      - If the validate() function returns with a failed promise (meaning the API is not available at the moment),
   *          display a browser alert stating "Sorry, please submit this form at a later time."
   */
  async validateForm() {
    try {
      let response = await this.validator.validate(this.responses);
      response.errors.forEach(function (error) {
        alert(error);
      });

      return response.valid;
    } catch (err) {
      alert("Sorry, please submit this form at a later time.");
    }
  }

  submitForm() {
    if (this.validateForm()) {
      this.httpClient.send('http://api.posttestserver.com/post', 'POST', JSON.stringify(this.responses))
        .then(function (response) {
            this.resetForm();
          }
        ).catch(function (error) {
        alert(error);
      });
    }
  }

  resetForm() {
    this.responses = JSON.parse(JSON.stringify(this.emptyForm));
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

class FormValidator {
  constructor() {
    this.httpClient = new HttpClient();
  }

  validate(fields) {
    return this.httpClient.send('POST', "https://somevalidationurl", JSON.stringify(fields))
      .then(function (response) {
        //im assuming this return a json object with validation errors
        return {
          valid: response.errors.length === 0,
          errors: response.errors
        };
      });
  }
}

// promise based http client from https://developers.google.com/web/fundamentals/primers/promises#promisifying_xmlhttprequest
class HttpClient {
  send(url, method, body) {
    return new Promise(function (resolve, reject) {
      let req = new XMLHttpRequest();
      req.open(method, url);

      req.onload = function () {
        if (req.status == 200) {
          resolve(req.response);
        }
        else {
          reject(Error(req.statusText));
        }
      };

      req.onerror = function () {
        reject(Error("Network Error"));
      };

      req.send(body);
    });
  }
}