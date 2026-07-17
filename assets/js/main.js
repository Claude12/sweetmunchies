import smoothScroll from './lib/smooth-scroll';
import animations from './lib/animations';
import header from './lib/header';

document.addEventListener('DOMContentLoaded', () => {
  smoothScroll();
  animations();
  header();
});
