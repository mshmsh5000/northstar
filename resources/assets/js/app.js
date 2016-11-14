/**
 * This is where we load and initialize components of the app.
 */

// Import Forge, the DoSomething.org pattern library.
import '@dosomething/forge';

// Styles
import '../scss/app.scss';

// Temporarily needed since old version of Validation package included
// some hardcoded calls to the window.Drupal object and t().
window.Drupal = {
  t: function(value) {
    return value;
  }
};

// Utilities
import Analytics from './utilities/Analytics';

// Register validation rules.
import './validators/auth';

// Initialize analytics.
Analytics.init();
