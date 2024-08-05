/* global jQuery, Backbone, _, getwooplugins_settings_params, wp */

(function ($, Backbone, _) {
    'use strict'

    /**
     * GWP Backbone Modal plugin
     *
     * @param {object} options
     */
    $.fn.GWPBackboneModal = function (options) {
        return this.each(function () {
            (new $.GWPBackboneModal($(this), options))
        })
    }

    /**
     * Initialize the Backbone Modal
     *
     * @param {object} element [description]
     * @param {object} options [description]
     */
    $.GWPBackboneModal = function (element, options) {
        // Set settings
        let settings = $.extend({}, $.GWPBackboneModal.defaultOptions, options)

        if (settings.template) {
            new $.GWPBackboneModal.View({
                target : settings.template,
                string : settings.data
            })
        }
    }

    /**
     * Set default options
     *
     * @type {object}
     */
    $.GWPBackboneModal.defaultOptions = {
        template : '',
        data     : {}
    }

    /**
     * Create the Backbone Modal
     *
     * @return {null}
     */
    $.GWPBackboneModal.View = Backbone.View.extend({
        tagName       : 'div',
        id            : 'gwp-backbone-modal-dialog',
        _target       : undefined,
        _string       : undefined,
        events        : {
            'click .modal-close' : 'closeButton',
            'click #btn-ok'      : 'addButton',
            'click #send-ajax'   : 'sendAjax',
            'touchstart #btn-ok' : 'addButton',
            'keydown'            : 'keyboardActions',
            'change :radio'      : 'optionChange'
        },
        resizeContent : function () {
            let $content = $('.gwp-backbone-modal-content').find('article')
            let max_h    = $(window).height() * 0.75

            $content.css({
                'max-height' : max_h + 'px'
            })
        },
        initialize    : function (data) {
            let view     = this
            this._target = data.target
            this._string = data.string
            _.bindAll(this, 'render')
            this.render()

            $(window).resize(function () {
                view.resizeContent()
            })
        },
        render        : function () {
            var template = wp.template(this._target)

            this.$el.append(
                template(this._string)
            )

            $(document.body).css({
                'overflow' : 'hidden'
            }).append(this.$el)

            this.resizeContent()
            this.$('.gwp-backbone-modal-content').attr('tabindex', '0').focus()

            // $( document.body ).trigger( 'init_tooltips' );

            $(document.body).trigger('gwp_backbone_modal_loaded', this._target)
        },
        closeButton   : function (e) {
            e.preventDefault()
            $(document.body).trigger('gwp_backbone_modal_before_remove', this._target)
            this.undelegateEvents()
            $(document).off('focusin')
            $(document.body).css({
                'overflow' : 'auto'
            })
            this.remove()
            $(document.body).trigger('gwp_backbone_modal_removed', this._target)
        },

        addButton : function (e) {
            $(document.body).trigger('gwp_backbone_modal_response', [this._target, this.getFormData()])
            this.closeButton(e)
        },

        sendAjax : function (event) {

            event.preventDefault()

            let form   = this.getFormData()
            let data   = this._string
            let action = `${form.action}_by_${form.plugin}`

            if (typeof form['reason_type'] === 'undefined') {
                this.closeButton(event)
                return
            }

            $(event.target).prop('disabled', true).text($(event.target).data('deactivating')).next().addClass('visible')

            wp.ajax.send(action, {
                data    : form,
                success : (response) => {
                    window.location.replace(data.deactivate_link)
                    this.closeButton(event)
                },
                error   : () => {
                    console.error('Deactivation Not logged.')
                    window.location.replace(data.deactivate_link)
                    this.closeButton(event)
                }
            })
        },

        optionChange : function (event) {

            $(event.target).closest('.feedback-dialog-form-body').find('.feedback-text').prop('disabled', true).hide()

            $(event.target).nextAll('.feedback-text').prop('disabled', false).show().focus()

        },

        getFormData     : function () {
            let data = {}

            $(document.body).trigger('gwp_backbone_modal_before_update', this._target)

            $.each($('form', this.$el).serializeArray(), function (index, item) {
                if (item.name.indexOf('[]') !== -1) {
                    item.name       = item.name.replace('[]', '')
                    data[item.name] = $.makeArray(data[item.name])
                    data[item.name].push(item.value)
                }
                else {
                    data[item.name] = item.value
                }
            })

            return data
        },
        keyboardActions : function (e) {
            let button = e.keyCode || e.which

            // Enter key
            if (13 === button && !(e.target.tagName && (e.target.tagName.toLowerCase() === 'input' || e.target.tagName.toLowerCase() === 'textarea'))) {
                this.addButton(e)
            }

            // ESC key
            if (27 === button) {
                this.closeButton(e)
            }
        }
    })

}(jQuery, Backbone, _))

const GWPAdminHelper = (($) => {
    return class GWPAdminHelper {

        static ResetPopupData(pluginslug) {
            let id      = `#gwp-plugin-deactivate-feedback-dialog-wrapper-${pluginslug}`
            let $button = $('.feedback-dialog-form-button-send', id)
            $button.prop('disabled', false).text($button.data('defaultvalue')).next().removeClass('visible')
        }

        static DeactivatePopup(pluginslug) {

            let id = `#gwp-plugin-deactivate-feedback-dialog-wrapper-${pluginslug}`

            $('.wp-list-table.plugins').find('[data-slug="' + pluginslug + '"].active').each(function () {

                let deactivate_link = $(this).find('.deactivate a').prop('href')

                $(this).data('deactivate_link', deactivate_link)

                $(this).find('.deactivate a').on('click', (event) => {
                    event.preventDefault()

                    $(this).GWPBackboneModal({
                        template : `gwp-deactive-feedback-dialog-${pluginslug}`,
                        data     : {
                            deactivate_link : deactivate_link,
                            plugin          : pluginslug
                        }
                    })
                })
            })
        }

        static ProPopup() {

            // let id = `#gwp-pro-dialog-wrapper-${pluginslug}`;
            $('.getwooplugins-admin-form-table .is-pro a.pro-modal, .getwooplugins-admin-form-table a.help-modal').each(function () {

                $(this).on('click', (event) => {
                    event.preventDefault()
                    // event.stopPropagation();

                    let template_id = $(this).data('template')
                    //let template = `#${template_id}`

                    $(this).GWPBackboneModal({
                        template : template_id,
                        data     : {}
                    })

                })
            })
        }

    }
})(jQuery);

(function ($, params, wp) {
    $(function () {

        // Color picker with alpha support
        if ($().wpColorPicker) {
            $('.color-picker-alpha').wpColorPicker({
                change : function (event, ui) {
                    window.onbeforeunload = function () {
                        return params.i18n_nav_warning
                    }
                },
            })
        }

        // Edit prompt
        $(function () {
            var changed = false

            $(':input:not(.no-track)').on('change', function () {
                if (!changed) {
                    window.onbeforeunload = function () {
                        return params.i18n_nav_warning
                    }
                    changed               = true
                }
            })

            $('.submit :input').on('click', function () {
                window.onbeforeunload = ''
            })
        })

        $(document.body).on('init_tooltips', function () {

            if (!jQuery().tipTip) {
                return false;
            }

            $('.tips, .help_tip, .woocommerce-help-tip, .getwooplugins-help-tip').tipTip({
                'attribute' : 'data-tip',
                'fadeIn'    : 50,
                'fadeOut'   : 50,
                'delay'     : 200,
                'keepAlive' : true
            })

            $('.column-wc_actions .wc-action-button').tipTip({
                'fadeIn'  : 50,
                'fadeOut' : 50,
                'delay'   : 200
            })

            // Add tiptip to parent element for widefat tables
            $('.parent-tips').each(function () {
                $(this).closest('a, th').attr('data-tip', $(this).data('tip')).tipTip({
                    'attribute' : 'data-tip',
                    'fadeIn'    : 50,
                    'fadeOut'   : 50,
                    'delay'     : 200,
                    'keepAlive' : true
                }).css('cursor', 'help')
            })
        })

        // Tooltips
        $(document.body).trigger('init_tooltips')

        try {
            $(document.body).on('init_form_field_dependency', function () {
                $('[data-gwp_dependency]').GWPFormFieldDependency()
            }).trigger('init_form_field_dependency')

        }
        catch (err) {
            // If failed (conflict?) log the error but don't stop other scripts breaking.
            window.console.log(err);
        }

        GWPAdminHelper.ProPopup()

    })
})(jQuery, getwooplugins_settings_params, wp)
