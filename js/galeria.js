(function ($, Drupal, drupalSettings) {
  "use strict";

  Drupal.behaviors.mifamilia_forms_galeria = {
    attach: function(context, settings) {
      Drupal.MifamiliaForms.fireEvent(context, Drupal.MifamiliaForms.settings.galeries.cities, "select.galeria", 'mifamilia-forms-change-galerie');
    }
  };

})(jQuery, Drupal, drupalSettings);
