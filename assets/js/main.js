import jQuery from 'jquery';
window.$ = window.jQuery = jQuery;


import smoothScroll from './smooth-scroll';
import animations from './animations';

jQuery(() => {
  smoothScroll();
  animations();
});
