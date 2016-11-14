const $ = require('jquery');
const Analytics = require('@dosomething/analytics');
const Validation = require('dosomething-validation');

function init() {
  Analytics.init();

  // Validation Events
  Validation.Events.subscribe('Validation:InlineError', (topic, args) => {
    Analytics.analyze('Form', 'Inline Validation Error', args);
  });

  Validation.Events.subscribe('Validation:Suggestion', (topic, args) => {
    Analytics.analyze('Form', 'Suggestion', args);
  });

  Validation.Events.subscribe('Validation:SuggestionUsed', (topic, args) => {
    Analytics.analyze('Form', 'Suggestion Used', args);
  });

  Validation.Events.subscribe('Validation:Submitted', (topic, args) => {
    Analytics.analyze('Form', 'Submitted', args);
  });

  Validation.Events.subscribe('Validation:SubmitError', (topic, args) => {
    Analytics.analyze('Form', 'Validation Error on submit', args);
  });

  // Attach any custom events.
  $(document).ready(() => {
    $('#profile-login-form').on('submit', () => {
      Analytics.analyze('Form', 'Submitted', 'profile-login-form')
    });

    $('#profile-edit-form').on('submit', () => {
      Analytics.analyze('Form', 'Submitted', 'profile-edit-form')
    });
  });
}

export default { init };
