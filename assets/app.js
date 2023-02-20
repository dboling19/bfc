/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/dboling.scss';
import './../node_modules/nord/src/sass/nord.scss';

// start the Stimulus application
import './bootstrap';


function getCookie(cname) {
  let name = cname + "=";
  let decodedCookie = decodeURIComponent(document.cookie);
  let ca = decodedCookie.split(';');
  for (let i = 0; i <ca.length; i++) {
    let c = ca[i];
    while (c.charAt(0) == ' ') {
      c = c.substring(1);
    }
    if (c.indexOf(name) == 0) {
      return c.substring(name.length, c.length);
    }
  }
  return "";
}


function checkCookie(cname) {
  let cookie = getCookie(cname);
  if (cookie != '') {
    return true;
  } else {
    return false;
  }
}


if (checkCookie('color-scheme') != true) {
  const theme = window.matchMedia("(prefers-color-scheme:dark)").matches ? 'dark' : 'light';
  document.cookie = 'color-scheme=' + theme;

} else {
  const theme = getCookie('color-scheme');

}