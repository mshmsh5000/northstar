const $ = require('jquery');

/**
 * Utility script to enable routing back to the last page,
 * as long as it's within the same top-level domain.
 */

function init(element = 'back') {
  $(document).ready(() => {

    let backLink = document.getElementById(element);
    let referrerUri = document.referrer;

    if (isSpecifiedRoute('/login') && Northstar) {
      referrerUri = Northstar.referrerUri;
    }

    if (backLink && referrerUri) {
      return backLink.setAttribute('href', referrerUri);
    }

  });
}

/**
 * Determine if the specified string matches the current pathname route.
 *
 * @param  {String}  pathname
 * @return {Boolean}
 */
function isSpecifiedRoute(pathname) {
  if (!pathname) {
    console.error('Please provide a route path to check against in the isSpecifiedRoute() method.');

    return false;
  }

  if (window.location.pathname !== pathname) {
    return false;
  }

  return true;
}

export default { init };
