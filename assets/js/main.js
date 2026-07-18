import smoothScroll from './lib/smooth-scroll';
import animations from './lib/animations';
import header from './lib/header';
import cartToasts from './lib/toast';
import faqAccordion from './lib/faq-accordion';

document.addEventListener('DOMContentLoaded', () => {
  smoothScroll();
  animations();
  header();
  cartToasts();
  faqAccordion();
});
