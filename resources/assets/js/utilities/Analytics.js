const $ = require('jquery');
const Analytics = require('@dosomething/analytics');

function init() {
  Analytics.init();

  // Attach custom form submit events.
  $(document).delegate('form', 'submit', (event) => {
    Analytics.analyze('Form', 'Submitted', event.target.id);
  });
}

export default { init };
