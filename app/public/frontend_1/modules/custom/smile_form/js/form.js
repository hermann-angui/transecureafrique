(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.form = {
    attach: function (context, settings) {
      Drupal.form.init();
    }
  };

  Drupal.form = {
    'init': function() {
      Drupal.form.textField();
      Drupal.form.select();
      Drupal.form.textarea();
      Drupal.form.checkboxes();
      Drupal.form.radios();
      Drupal.form.search();
      Drupal.form.datepicker();
      Drupal.form.file();
      Drupal.form.telField();
      Drupal.form.numberField();
    },
    'ama_convertDate': function (dateToConvert) {
      var data = dateToConvert.split('/');
      return [data[2], data[1], data[0]].join('-');
    },
    'ama_getAge': function (selectedDate) {
      selectedDate = Drupal.form.ama_convertDate(selectedDate);
      var ageDifMs = Date.now() - new Date(selectedDate);
      var ageDate = new Date(ageDifMs);
      return Math.abs(ageDate.getUTCFullYear() - 1970);
    },

    'ama_showAlertMessage': function (input) {
      var html = '<div aria-label="Message d\'erreur" class="max-age-alert alert alert-error alert-danger alert-dismissible fade show col-12 offset-xl-1 col-xl-10 p-3 p-lg-4" role="alert">\n' +
        '<h2 class="visually-hidden">Message d\'erreur</h2>\n' +
        '<div class="item-list--comma-list item-list">Cette solution est réservée aux personnes de moins de 75 ans.</div>\n' +
        '</div>';
      $(input).parent().before(html);
      Drupal.form.ama_disableSubmit(input);
    },

    'ama_enableSubmit': function (input) {
      $('#edit-submit').prop('disabled', false);
    },

    'ama_disableSubmit': function (input) {
      $('#edit-submit').prop('disabled', true);
    },

    'alertAge': function (input, selectedDate) {
      $('.max-age-alert').remove();
      Drupal.form.ama_enableSubmit(input);
      var max_age = input.getAttribute('max-age');
      if (max_age !== null) {
        var ageYears = Drupal.form.ama_getAge(selectedDate);
        if (ageYears > max_age) {
          Drupal.form.ama_showAlertMessage(input);
        }
      }
    },
    'textField': function () {
      /**
       * Input text, add focus
       */
      $(".form-item").find(".form-text").once('form-textField').each(function (index) {
        $(this).focusin(function () {
          $(this).closest(".form-item").addClass("is-focused");
        });
        $(this).focusout(function () {
          if ($(this).val().length <= 0) {
            $(this).closest(".form-item").removeClass("is-focused");
          }
        });
        if ($(this).val().length > 0) {
          $(this).closest(".form-item").addClass("is-focused");
        }
      });
    },
    'select': function () {
      $(".form-item").find("select").once('form-select').each(function (index) {
        var label = $(this).parent('.form-item').find('label').first().text();
        var placeholder = $(this).parent('.form-item').find('.chosen-single span');

        if ($(this).val() != '') {
          $(this).trigger("chosen:updated");
        } else {
          placeholder.text(label);
        }
        $('select[name="county"]').chosen('destroy').chosen({disable_search:false, placeholder_text_single: "Département du siège social"});
        $('select[name="county"]').parent('.form-item').find('.chosen-search').addClass('form-type-textfield');
      });

      $( window ).resize(function() {
        $(".form-item").find("select").each(function (index) {
          var width = $(this).parent().width();
          $(this).parent().find('.chosen-container').css('width', width+"px");
        });
      });
    },
    'textarea': function () {
      $(".form-item")
        .find(".form-textarea")
        .once('form-textarea')
        .each(function (index) {
          $(this).focusin(function () {
            $(this).closest(".form-item").addClass("is-focused");
          });
          $(this).focusout(function () {
            if ($(this).val().length <= 0) {
              $(this).closest(".form-item").removeClass("is-focused");
            }
          });
          if ($(this).val().length > 0) {
            $(this).closest(".form-item").addClass("is-focused");
          }
        });
    },
    'checkboxes': function () {
      $(".form-item")
        .find(".diagnostic-checkbox")
        .each(function (index) {
          $(this).change(function () {
            if ($(this).prop('checked')) {
              $(this).parent().addClass('form-item-active');
            } else {
              $(this).parent().removeClass('form-item-active');
            }
          });

        });
    },
    'radios': function () {
      $(".form-item .form-radio").change(function (e) {
        $(".form-item .form-radio").parent().removeClass('form-item-active');
        if ($(this).prop('checked')) {
          $(this).parent().addClass('form-item-active');
        }
      });
    },
    'search': function () {
      /**
       * ajout d'un span autour de l'input search pour lui faire acquérir les
       * pseudos éléments
       */
      $(".form-item").find(".form-search").once('form-search').each(function (index) {
        var inputId = $(this).attr("id");
        $(this).wrap('<span id="span-' + inputId + '" for="' + inputId + '"></span>');
        $("<i class='icon-Search'></i>").appendTo($('#span-' + inputId));

        $($(this).parents(".form-type-search")).hover(
          function () {
            $(this).find("i").addClass("hover");
          },
          function () {
            $(this).find("i").removeClass("hover");
          }
        );
        $($(this).parents(".form-type-search")).focusin(function () {
          $(this).find("i").addClass("focus").closest(".form-item").addClass("is-focused");
        });
        $($(this).parents(".form-type-search")).focusout(function () {
          if ($(this).find(".form-search").val().length <= 0) {
            $(this).find("i").removeClass("focus").closest(".form-item").removeClass("is-focused");
          }
        });
      });

      var $erreur = $('div.alert-error, div.alert-success');
      var $form = $('form > fieldset.js-form-item:nth-of-type(1)');
      if ( $erreur.length && $form.length){
        $erreur.show();
        $form.prepend($erreur.removeClass('offset-xl-1'));
      }

      /**
       * ajout d'un span autour de l'input search pour lui faire acquérir les
       * pseudos éléments specifique pour le formulaire caisse de retraite
       */
      $("form.views-exposed-form .form-item").find(".form-text").once('form-views-exposed-form').each(function (index) {
        var inputId = $(this).attr("id");
        $(this).wrap('<span id="span-' + inputId + '" for="' + inputId + '"></span>');
        $("<i class='icon-Search'></i>").appendTo($('#span-' + inputId));
        $(this).attr("autocomplete", "off");

        $($(this).parents(".form-type-textfield")).hover(
          function () {
            $(this).find("i").addClass("hover");
          },
          function () {
            $(this).find("i").removeClass("hover");
          }
        );

        $($(this).parents(".form-type-textfield")).focusin(function () {
          $(this).find("i").addClass("focus").closest(".form-item").addClass("is-focused");
        });
        $($(this).parents(".form-type-textfield")).focusout(function () {
          if ($(this).find(".form-text").val().length <= 0) {
            $(this).find("i").removeClass("focus").closest(".form-item").removeClass("is-focused");
          }
        });

        $('#span-'+inputId+' i').click( function(e) {
          e.preventDefault();
          e.stopPropagation();

          var event = $.Event("keyup");
          event.which = 13;
          $(this).trigger(event);
        });
      });

      $("form.with-search .form-item").find(".form-text").once('form-views-exposed-form').each(function (index) {
        var inputId = $(this).attr("id");
        $(this).wrap('<span id="span-' + inputId + '" for="' + inputId +'"></span>');

        if (!inputId.startsWith('edit-code-postal')) {
            $("<i class='icon icon-Search'></i>").appendTo($('#span-' + inputId));
        }
        $(this).attr("autocomplete", "off");

        $($(this).parents(".form-type-textfield")).hover(
          function () {
            $(this).find("i").addClass("hover");
          },
          function () {
            $(this).find("i").removeClass("hover");
          }
        );

        $($(this).parents(".form-type-textfield")).focusin(function () {
          $(this).find("i").addClass("focus").closest(".form-item").addClass("is-focused");
        });
        $($(this).parents(".form-type-textfield")).focusout(function () {
          if ($(this).find(".form-text").val().length <= 0) {
            $(this).find("i").removeClass("focus").closest(".form-item").removeClass("is-focused");
          }
        });

        $('.input__ccn_propositions_search').keyup(function(e){
          e.preventDefault();
          e.stopPropagation();

          if(e.keyCode == 13) {
            $('.submit__ccn_propositions_search').click();
          }
        });

        $('#span-'+inputId+' i').click( function (e) {
          e.preventDefault();
          e.stopPropagation();

          if ($(e.currentTarget).parent().find('.input__ccn_propositions_search').length) {
            $('.submit__ccn_propositions_search').click();
          } else {
            $(this).parents('.with-search').find('.js-form-submit').click();
          }
        });
      });

      /**
       * au focus sur l'input search, on enleve le label, on le remet a sa perte
       */
      $(".form-item").find(".form-search").once('form-focus-search').each(function (index) {
        $(this).focusin(function () {
          $(this).parent().find("label").hide();
        });
        $(this).focusout(function () {
          if ($(this).val().length <= 0) {
            $(this).parent().find("label").show();
          }
        });
      });

      /**
       * au focus sur l'input search, on enleve le label, on le remet a sa perte
       * specifique pour le formulaire caisse de retraite
       */
      $("form.views-exposed-form .form-item").find(".form-text").once('form-search-views-exposed-form').each(function (index) {
        $(this).focusin(function () {
          $(this).parent().find("label").hide();
        });
        $(this).focusout(function () {
          if ($(this).val().length <= 0) {
            $(this).parent().find("label").show();
          }
        });
      });

      $("form.with-search .form-item").find(".form-text").once('form-search-views-exposed-form').each(function (index) {
        $(this).focusin(function () {
          $(this).parent().find("label").hide();
        });
        $(this).focusout(function () {
          if ($(this).val().length <= 0) {
            $(this).parent().find("label").show();
          }
        });
      });
    },
    'datepicker': function () {
      $(".form-item").find(".input-datepicker").once('form-datepicker').each(function (index) {
        $(this).focus(function() {
          $( this ).attr( 'autocomplete', 'off' );
        });
        if ($(this).attr('name') === 'birthday' || $(this).attr('name') === 'conjoint_birthday') {
          $(this).datepicker({
            dateFormat: "dd/mm/yy",
            beforeShowDay: function (date) {
              return [true, (date.getDate() < 10 ? "zero" : "")];
            },
            prevText: "",
            nextText: "",
            yearRange: "-120:-0",
            changeMonth: true,
            changeYear: true,
            showButtonPanel: true,
            maxDate: "0",
            onClose: function (selectedDate) {
              Drupal.form.alertAge(this, selectedDate);
            },
            onSelect: function (selectedDate) {
              Drupal.form.alertAge(this, selectedDate);
              $(this).closest(".form-item").addClass("is-focused");
            }
          }).bind('click',function (e) {
            if($('.block-flap').is(':visible')){
              $("#ui-datepicker-div").appendTo($(e.currentTarget).parent());
              $("#ui-datepicker-div").css("position", "static");
            }
          });
        }else if($(this).hasClass('offre-date-depart-datepicker')) {
          $(this).datepicker({
            dateFormat: "dd/mm/yy",
            prevText: "",
            nextText: "",
            yearRange: "-120:+1",
            onSelect: function () {
              $(this).closest(".form-item").addClass("is-focused");
            }
          }).bind('click',function (e) {
            if($('.block-flap').is(':visible')){
              $("#ui-datepicker-div").appendTo($(e.currentTarget).parent());
              $("#ui-datepicker-div").css("position", "static");
            }
          });
        } else if ($(this).hasClass('store-datepicker')) {
          $(this).datepicker({
            dateFormat: "dd/mm/yy",
            minDate: 0,
            beforeShowDay: $.datepicker.noWeekends,
            prevText: "",
            nextText: "",
            yearRange: "-0:+1",
            onSelect: function () {
              $(this).closest(".form-item").addClass("is-focused");
              $('.store-datepicker').trigger('change');
            }
          }).bind('click',function (e) {
            if($('.block-flap').is(':visible')){
              $("#ui-datepicker-div").appendTo($(e.currentTarget).parent());
              $("#ui-datepicker-div").css("position", "static");
            }
          });
        } else {
          $(this).datepicker({
            dateFormat: "dd/mm/yy",
            beforeShowDay: function (date) {
              return [true, (date.getDate() < 10 ? "zero" : "")];
            },
            prevText: "",
            nextText: "",
            yearRange: "-120:-0",
            onSelect: function () {
              $(this).closest(".form-item").addClass("is-focused");
            }
          }).bind('click',function (e) {
            if($('.block-flap').is(':visible')){
              $("#ui-datepicker-div").appendTo($(e.currentTarget).parent());
              $("#ui-datepicker-div").css("position", "static");
            }
          });
        }
      });
    },
    'file': function () {
    },
    'telField': function () {
      /**
       * Input tel, add focus
       */
      $(".form-item").find(".form-tel").once('form-tel').each(function (index) {
        $(this).focusin(function () {
          $(this).closest(".form-item").addClass("is-focused");
        });
        $(this).focusout(function () {
          if ($(this).val().length <= 0) {
            $(this).closest(".form-item").removeClass("is-focused");
          }
        });
        if ($(this).val().length > 0) {
          $(this).closest(".form-item").addClass("is-focused");
        }
      });
    },
    'numberField': function () {
      /**
       * Input number, add focus
       */
      $(".form-item").find(".form-number").once('form-number').each(function (index) {
        $(this).focusin(function () {
          $(this).closest(".form-item").addClass("is-focused");
        });
        $(this).focusout(function () {
          if ($(this).val().length <= 0) {
            $(this).closest(".form-item").removeClass("is-focused");
          }
        });
        if ($(this).val().length > 0) {
          $(this).closest(".form-item").addClass("is-focused");
        }
      });
    }
  }



})(jQuery, Drupal, drupalSettings);
