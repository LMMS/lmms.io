// Got this from https://medium.com/@mariusc23/hide-header-on-scroll-down-show-on-scroll-up-67bbaae9a78c#.oz8xo36b7
// Thank you Marius Craciunoiu
// Hide Header on on scroll down
(function() {
  var didScroll;
  var lastScrollTop = 0;
  var delta = 5;
  var nav = document.querySelector('nav');
  var navbarHeight = nav.offsetHeight;

  window.addEventListener('scroll', function() {
      didScroll = true;
  }, { passive: true });

  setInterval(function() {
      if (didScroll) {
          hasScrolled();
          didScroll = false;
      }
  }, 100);

  function hasScrolled() {
      var st = window.scrollY;

      // Make sure they scroll more than delta
      if (Math.abs(lastScrollTop - st) <= delta)
          return;

      // If they scrolled down and are past the navbar, add class .nav-up.
      // This is necessary so you never see what is "behind" the navbar.
      if (st > lastScrollTop && st > navbarHeight) {
          // Scroll Down
          nav.classList.remove('nav-down');
          nav.classList.add('nav-up');
      } else {
          // Scroll Up
          if (st + window.innerHeight < document.documentElement.scrollHeight) {
              nav.classList.remove('nav-up');
              nav.classList.add('nav-down');
          }
      }
      lastScrollTop = st;
  }
})();
// close out the open navbar in mobile mode when we click outside of it
(function() {
  $('.navbar-collapse.collapse').on('shown.bs.collapse', function () {
    $(document).on('click', function () {
      $('.navbar-collapse.collapse').collapse('hide');
    });
  });
}).call(this);
