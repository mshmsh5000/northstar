const Validation = require('dosomething-validation');

// @TODO: This will be removed when Neue's validation gets refactored.
function validateNotBlank(string, done, success, failure) {
  if( string !== "" ) {
    return done({
      success: true,
      message: success
    });
  } else {
    return done({
      success: false,
      message: failure
    });
  }
}

// ## First Name
// Greets the user when they enter their first name.
Validation.registerValidationFunction("first_name", function(string, done) {
  validateNotBlank(string, done,
    `Hey, ${string}`,
    'We need your first name.'
  );
});
