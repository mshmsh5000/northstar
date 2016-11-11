/**
 * This is where we load and initialize components of the app.
 */

import $ from 'jquery';

// Import Forge, the DoSomething.org pattern library.
import '@dosomething/forge';

// Styles
import '../scss/app.scss';

// Utilities
import Analytics from './utilities/Analytics';

// Initialize analytics.
Analytics.init();
