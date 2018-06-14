(function ($, Drupal, drupalSettings) {

  'use strict';

  /**
   * @namespace
   */
  Drupal.MifamiliaForms = {};

  /**
   * Settings for mifamilia_forms
   *
   * @type {object}
   */
  Drupal.MifamiliaForms.settings = drupalSettings.mifamilia_forms;

  /**
   * Settings for geolocations
   *
   * @type {object}
   */
  Drupal.MifamiliaForms.settings.geolocations = Drupal.MifamiliaForms.settings.geolocations;

  /**
   * Settings for galleries
   *
   * @type {object}
   */
  Drupal.MifamiliaForms.settings.galeries = Drupal.MifamiliaForms.settings.galeries;

  /**
   * Helper function to events
   * @param  {object} context
   *   The attach context
   * @param  {object} cities
   *   The JSON cities
   * @param  {string} selector
   *   The class of select
   * @param  {string} once_id
   *   The id of once event
   */
  Drupal.MifamiliaForms.fireEvent = function(context, cities, selector, once_id) {
    var cities = _.sortBy(cities, "name"),
        $select_cities = $(context).find(selector),
        cities_option = '',
        selected = $select_cities.val();
    $.each(cities, function(index, value) {
       if (value.tid === selected) {
        cities_option += '<option value="' + value.tid + '" selected="selected">' + value.name + '</option>';
      } else {
        cities_option += '<option value="' + value.tid + '">' + value.name + '</option>';
      }
    });
    $select_cities.once(once_id)
      .empty()
      .append(cities_option)
      .on('change', function(){
        $(context).find('button.form-submit').trigger('click');
      });
  };

})(jQuery, Drupal, drupalSettings);
