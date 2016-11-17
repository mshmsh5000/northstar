/**
 * Utility script to enable routing back to the last page,
 * as long as it's within the same top-level domain.
 */

function init(element = 'back') {
  let backLink = document.getElementById(element);
  let originUri = document.referrer;

  if (backLink && originUri) {
    backLink.setAttribute('href', originUri);
  }
}

export default { init };
