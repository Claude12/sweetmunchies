function productGallery() {
  document.querySelectorAll('.product-page__gallery').forEach((gallery) => {
    const mainImage = gallery.querySelector('.product-page__main-image img');
    const thumbs = gallery.querySelectorAll('.product-page__thumb');

    if (!mainImage || !thumbs.length) {
      return;
    }

    thumbs.forEach((thumb) => {
      thumb.addEventListener('click', () => {
        const fullSrc = thumb.dataset.fullSrc;

        if (!fullSrc) {
          return;
        }

        mainImage.src = fullSrc;
        mainImage.removeAttribute('srcset');

        thumbs.forEach((other) => other.classList.remove('is-active'));
        thumb.classList.add('is-active');
      });
    });
  });
}

export default productGallery;
