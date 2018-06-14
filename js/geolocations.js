(function ($, Drupal, drupalSettings) {
  "use strict";

  Drupal.behaviors.mifamilia_forms_geolocations = {
    attach: function(context, settings) {
      Drupal.MifamiliaForms.fireEvent(context, Drupal.MifamiliaForms.settings.geolocations.cities, "select.geolocation", 'mifamilia-forms-change-map');
    }
  };

})(jQuery, Drupal, drupalSettings);
