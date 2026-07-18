import smoothScroll from './lib/smooth-scroll';
import animations from './lib/animations';
import header from './lib/header';
import cartToasts from './lib/toast';
import faqAccordion from './lib/faq-accordion';
import productGallery from './lib/product-gallery';
import quantityStepper from './lib/quantity-stepper';
import productTabs from './lib/product-tabs';
import productAddToCart from './lib/product-add-to-cart';
import productPoa from './lib/product-poa';
import cartPage from './lib/cart-page';

document.addEventListener('DOMContentLoaded', () => {
  smoothScroll();
  animations();
  header();
  cartToasts();
  faqAccordion();
  productGallery();
  quantityStepper();
  productTabs();
  productAddToCart();
  productPoa();
  cartPage();
});
