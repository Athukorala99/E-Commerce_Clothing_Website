/**
 * Init Elements in Elementor Frontend
 *
 */
window.addEventListener("elementor/frontend/init", function() {
  elementorFrontend.hooks.addAction('frontend/element_ready/depicter_slider.default', function ($scope) {
    Depicter.initAll();
  });

  elementorFrontend.hooks.addAction('frontend/element_ready/shortcode.default', function ($scope) {
    if ($scope.find('.depicter').length) {
      Depicter.initAll(); 
    }
  });
});
