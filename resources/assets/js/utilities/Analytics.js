const $ = require('jquery');
const Analytics = require('@dosomething/analytics');

function init() {
  console.log('analytics initialized...');
  Analytics.init();

}

export default { init };
