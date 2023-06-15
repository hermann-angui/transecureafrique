(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.smile_modal_toolbox_context = {
    attach: function (context, settings) {
      var diag = null;
      var diag_data = null;

      if ($.cookie("Drupal.visitor.diagnostic_state")) {
        diag = jQuery.parseJSON($.cookie("Drupal.visitor.diagnostic_state"));
      }

      $('.container-flap-open', context).each(function() {
        $(this, context).find(".flap-open-ajax-link").each(function(){
          if (diag) {
            if (diag.cible == 'b2c') {
              if ($(this, context).hasClass("tool-box-btc")) {
                  $(this, context).show();
              } else {
                  $(this, context).hide();
              }
            }
            else if (diag.cible != 'b2c') {
                if ($(this, context).hasClass("tool-box-btb")) {
                    $(this, context).show();
                } else {
                    $(this, context).hide();
                }
            }
          }
          else if (drupalSettings.target == 'B2C') {
            if ($(this, context).hasClass("tool-box-btc")) {
              $(this, context).show();
            }else{
              $(this, context).hide();
            }
          } else if (drupalSettings.target == 'B2B') {
            if ($(this, context).hasClass("tool-box-btb")) {
              $(this, context).show();
            }else{
              $(this, context).hide();
            }
          }
          else {
            if ($(this, context).hasClass("tool-box-contact")) {
              $(this, context).show();
            }else{
              $(this, context).hide();
            }
          }
        });
      });
    }
  }

})(jQuery, Drupal, drupalSettings);
