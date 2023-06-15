// Fichier commenté pour voir si il est toujours nécessaire ou pas

(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.stickyHeader = {
    attach: function (context, settings) {
      $(window).scroll(function (event) {
        var height = ($(window).height()/4)*3;
        var scroll = $(window).scrollTop();
        if(scroll >= height){
          $('.sticky.new-header.megamenu').addClass('sticked');
        }
        else{
          $('.sticky.new-header.megamenu').removeClass('sticked');
        }
      });
    }
  };

  var heightExperience = $('.experience .colonne1 img').height();
  $('.experience .row1').css('height', heightExperience);
  $('.paragraph__service_slider > div').removeAttr('class');

  Drupal.behaviors.stickyDiagnostic = {
    attach: function(context, settings) {
      $(document).on('stickydiagnostic', function() {
        
        //$('.sticky-diagnostic').addClass('sticked');
        //$('.overlay-grey').addClass('stickyOpen');
        //if (window.matchMedia("(min-width: 1025px)").matches) {
          //$('.sticky.new-header.megamenu').removeClass('sticked');
        //}
        //if (window.matchMedia("(max-width: 768px)").matches) {
          //$('.sticky-diagnostic [class^=diagnostic-form] div[id*=edit-niveau]>div:first-of-type ul').show();
          //$('.sticky-diagnostic [class^=diagnostic-form] div[id*=edit-niveau]>div:first-of-type .placeholder').hide();
        //}

        window.location.href = "/#diagnostic-form1";
      });

      $('.diagnostic-open').once().click(function(e) {
        $(document).trigger('stickydiagnostic');
        e.preventDefault();
      });

      $('.sticky-diagnostic .close').once().click(function() {
        $('.sticky-diagnostic').removeClass('sticked');
        if (window.matchMedia("(min-width: 1025px)").matches) {
          $('.sticky.new-header.megamenu').addClass('sticked');
        }
        $('.overlay-grey').removeClass('stickyOpen');
      });
    }
  }
})(jQuery, Drupal, drupalSettings);
