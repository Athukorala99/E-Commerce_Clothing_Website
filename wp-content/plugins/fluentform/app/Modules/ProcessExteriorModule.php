<?php

namespace FluentForm\App\Modules;

use FluentForm\App\Models\Form;
use FluentForm\App\Modules\Acl\Acl;

class ProcessExteriorModule
{
    public function handleExteriorPages()
    {
        if (defined('CT_VERSION')) {
            // oxygen page compatibility
            remove_action('wp_head', 'oxy_print_cached_css', 999999);
        }
        wp_register_script(
            'fluentform_editor_helper',
            fluentFormMix('js/fluent_forms_editor_helper.js'),
            array('jquery'),
            FLUENTFORM_VERSION,
            true
        );
        $this->renderFormPreview(intval(wpFluentForm('request')->get('preview_id')));
    }

    public function renderFormPreview($form_id)
    {
        if (Acl::hasAnyFormPermission($form_id)) {
            add_filter('fluentform/is_form_renderable', function ($renderable) {
                $renderable['status'] = true;
                return $renderable;
            });
            
            $form = Form::find($form_id);
            if ($form) {
                wp_enqueue_script('fluentform_editor_helper');
                wpFluentForm('view')->render('frameless.show_preview', [
                    'form_id' => $form_id,
                    'form'    => $form,
                ]);
                exit();
            }
        }
    }

    private function loadDefaultPageTemplate()
    {
        add_filter('template_include', function ($original) {
            return locate_template(['page.php', 'single.php', 'index.php']);
        });
    }

    /**
     * Set the posts to one
     *
     * @param WP_Query $query
     *
     * @return void
     */
    public function pre_get_posts($query)
    {
        if ($query->is_main_query()) {
            $query->set('posts_per_page', 1);
        }
    }
}
