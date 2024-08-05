// eslint-disable-next-line no-unused-vars
// import config from '@config';
// import '@styles/admin';
// Uncomment the following line if needed:
// import 'airbnb-browser-shims';

// Your code goes here ...

// enable submit and deactivate button while clicking on survey list items
(function ($) {
  
  var publishSliderButtonFunc = function (view = false) {
    $('.depicter-publish-slider:not([disabled])').off('click');
    $('.depicter-publish-slider:not([disabled])').on('click', function () {
      var $this = $(this);
      $this.find('.btn-label').hide();
      $this.find('.depicter-state-icon').show();
      var sliderID = $this.data('document-id') || $this.parents('tbody,#elementor-controls').find('select').val().replace( '#', '');
      var data = {
        action: 'depicter/document/store',
        ID: sliderID,
        status: 'published'
      };

      $.ajax({
        url: depicterParams.ajaxUrl,
        type: 'POST',
        data: data,
        headers: {
          'X-DEPICTER-CSRF': depicterParams.token
        }
      }).done(function (response) {
        if (response.hits) {
          $this.attr('disabled', true);
          $this.parents('.depicter-notice-btns').siblings('.depicter-notice-txts').hide();
          $this.find('.btn-label').html(depicterParams.publishedText);
          $this.find('.btn-label').show();
          $this.find('.depicter-state-icon').hide();
          if (view) {
            view.$el.find('.depicter-notice-wrapper').remove(); 
          } else {
            $('.fl-module-module').find('.depicter-notice-wrapper').remove();
          }
        } else {
          $this.find('.btn-label').show();
          $this.find('.depicter-state-icon').hide();
        }
      });

    });
  }

  var editSliderButtonFunc = function () {
    $('.depicter-edit-slider').off('click');
    $('.depicter-edit-slider').on('click', function () {
      var sliderID = $(this).data('document-id') || $(this).parents('tbody, #elementor-controls').find('select').val().replace('#', '');
      var editorUrl = depicterParams.editorUrl.replace( 'document=1', 'document=' + sliderID );
      window.open( editorUrl );
    });
  }

  $(document).ready(function () {

    var $feedbackContainer = $('.depicter-survey-container');
    // show the popup survey
    $('#deactivate-depicter').on('click', function (e) {
      e.preventDefault();
      $feedbackContainer.addClass('show');
    });

    // close the popup survey
    $feedbackContainer.on('click', function(e) {
      if ((!$(e.target).parents('.depicter-survey-list').length && !$(e.target).is('.depicter-survey-list')) || $(e.target).is('.depicter-close')) {
        $feedbackContainer.removeClass('show');
      }
    });

    // enable submit button if one reason clicked
    $feedbackContainer.find('input[name="dep_deactivation_reason"]').each(function () {
      $(this).on('click', function () {
        $feedbackContainer.find('.depicter-submit').attr('disabled', false);
      });
    });

    var ajaxDeactivationRequest = function (reason, userDescriptionText) {
      $.ajax({
        url: depDeactivationParams.ajaxUrl,
        method: 'POST',
        data: {
          _wpnonce: $feedbackContainer.find('#_wpnonce').val(),
          action: 'depicter/deactivate/feedback',
          issueRelatesTo: reason,
          userDescription: userDescriptionText,
        },
      }).done(function (res) {
        location.href = $('#deactivate-depicter').attr('href');
      });
    };

    // send deactivation feedback
    $feedbackContainer.find('.depicter-submit').on('click', function () {
      var $selectedRadioInput = $feedbackContainer.find('input[name="dep_deactivation_reason"]:checked'),
        reason = $selectedRadioInput.val(),
        userDescriptionText = $selectedRadioInput.parent('div').find('input[type="text"').length ? $selectedRadioInput.parent('div').find('input[type="text"').val() : '';
      ajaxDeactivationRequest(reason, userDescriptionText);
      $(this).parent('.depicter-button-wrapper').addClass('loading');
    });

    // deactivate plugin if click on skip
    $feedbackContainer.find('.depicter-skip').on('click', function() {
      ajaxDeactivationRequest('skip', '');
      $(this).parent('.depicter-button-wrapper').addClass('loading skipped');
      location.href = $('#deactivate-depicter').attr('href');
    });

    // ─── Beaver Builder Js ───────────────────────────────────────────

    var beaverBuilderControls = function () {
      var $tbody = $('.fl-field-control-wrapper select[name="document_id"]').parents('tbody');

      $tbody.find(' > tr:not(#fl-field-document_id)').hide();
      var sliderID = $('.fl-field-control-wrapper select[name="document_id"]').val();
      if (sliderID) {
        $('#fl-field-slider_control_buttons_' + sliderID.replace( '#', '')).show();
      }

      editSliderButtonFunc();

      publishSliderButtonFunc();
    }

    if (typeof FLBuilder != 'undefined') {
      $('body').on('fl-builder.settings-form-init', function () {
        $('.fl-field-control-wrapper select[name="document_id"]').on('change', function () {
          beaverBuilderControls();
        });
        beaverBuilderControls();
      });
    }

    if (typeof CtBuilderAjax != 'undefined') {
      $('#oxygen-sidebar-control-panel-basic-styles').bind('DOMSubtreeModified', function(){
        if ( $(this).find('.depicter-edit-slider').length ) {
          editSliderButtonFunc();
          publishSliderButtonFunc();
        }
      });
    }

  });

  if ( window.elementor ) {
    elementor.hooks.addAction( 'panel/open_editor/widget', function( panel, model, view ) {
      editSliderButtonFunc();
      publishSliderButtonFunc(view);
    });
  }

})(jQuery);
