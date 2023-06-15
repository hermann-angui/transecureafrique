(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.managed_file = {
      attach: function (context, settings) {

        $(".form-type-managed-file .form-type-checkbox").find("label").once('managed_file').each(function (index) {
          $(this).click(function(e){
            e.preventDefault();
            e.stopPropagation();
            $(this).closest("div.form-type-checkbox").find('.form-checkbox').each(function () {
              $(this).prop( "checked", false );
            });
          });

          $(this).find("i.file-close").once('click-file').click(function(el){
             el.preventDefault();
             el.stopPropagation();
             $(this).closest("div.form-type-checkbox").find('.form-checkbox').each(function () {
               $(this).prop( "checked", true );
             });
             $(this).closest('.form-managed-file').find('input[name="file_remove_button"]').each(function() {
               $(this).trigger('mousedown');
             });


           });
        });

        $(".form-type-managed-file").find("div.form-managed-file > span").once('managed_single_file').each(function (index) {

          $(this).find("i.file-close").once('click-file').click(function(el){
             el.preventDefault();
             el.stopPropagation();
             $(this).closest('.form-managed-file').find('input[name="file_remove_button"]').each(function() {
               $(this).trigger('mousedown');
             });


           });
        });

      }
  }

}(jQuery, Drupal));
