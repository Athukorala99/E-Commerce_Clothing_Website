<?php

namespace FluentForm\App\Services\Migrator;

use FluentForm\App\Services\Migrator\Classes\NinjaFormsMigrator;
use FluentForm\App\Services\Migrator\Classes\CalderaMigrator;
use FluentForm\App\Services\Migrator\Classes\GravityFormsMigrator;
use FluentForm\App\Services\Migrator\Classes\WpFormsMigrator;
class Bootstrap
{
    protected $importer;

    public function boot()
    {
        add_action('wp_ajax_fluentform-migrator-get-migrator-data', [$this, 'getMigratorData']);
        add_action('wp_ajax_fluentform-migrator-get-forms-by-key', [$this, 'getFormsByKey']);
        add_action('wp_ajax_fluentform-migrator-import-forms', [$this, 'importForms']);
        add_action('wp_ajax_fluentform-migrator-import-entries', [$this, 'importEntries']);

    }

    public function availableMigrations()
    {
        $migratorLinks = [];

        if ((new CalderaMigrator())->exist()) {
            $migratorLinks[] = [
                'name' => 'Caldera Forms',
                'key'  => 'caldera',
            ];
        }
        if ((new NinjaFormsMigrator())->exist()) {
            $migratorLinks[] = [
                'name' => 'Ninja Forms',
                'key'  => 'ninja_forms',
            ];
        }
        if ((new GravityFormsMigrator())->exist()) {
            $migratorLinks[] = [
                'name' => 'Gravity Forms',
                'key'  => 'gravityform',
            ];
        }
        if ((new WpFormsMigrator())->exist()) {
            $migratorLinks[] = [
                'name' => 'WPForms',
                'key'  => 'wpforms',
            ];
        }
        return $migratorLinks;

    }

    public function setImporterType()
    {
        $formType = sanitize_text_field(wpFluentForm('request')->get('form_type'));

        switch ($formType) {
            case 'caldera':
                $this->importer = new CalderaMigrator();
                break;
            case 'ninja_forms':
                $this->importer = new NinjaFormsMigrator();
                break;
            case 'gravityform':
                $this->importer = new GravityFormsMigrator();
                break;
            case 'wpforms':
                $this->importer = new WpFormsMigrator();
                break;
            default:
                wp_send_json([
                    'message' => __('Unsupported Form Type!'),
                    'success' => false,
                ]);
        }


    }

    public function getMigratorData()
    {
        \FluentForm\App\Modules\Acl\Acl::verify(['fluentform_settings_manager', 'fluentform_forms_manager']);

        wp_send_json([
            'status'        => true,
            'migrator_data' => $this->availableMigrations()
        ], 200);
    }

    public function importForms()
    {
        \FluentForm\App\Modules\Acl\Acl::verify(['fluentform_settings_manager', 'fluentform_forms_manager']);
        
        $formIds = wpFluentForm('request')->get('form_ids');
        $formIds = array_map('sanitize_text_field', $formIds);

        $this->setImporterType();
        $this->importer->import_forms($formIds);

    }

    public function importEntries()
    {
        \FluentForm\App\Modules\Acl\Acl::verify(['fluentform_settings_manager', 'fluentform_forms_manager']);


        $fluentFormId = intval(wpFluentForm('request')->get('imported_fluent_form_id'));
        $importFormId = sanitize_text_field(wpFluentForm('request')->get('source_form_id'));
        $this->setImporterType();
        $this->importer->insertEntries($fluentFormId, $importFormId);
    }

    public function hasOtherForms()
    {
        \FluentForm\App\Modules\Acl\Acl::verify(['fluentform_settings_manager', 'fluentform_forms_manager']);

        $migrationData = $this->availableMigrations();
        if (is_array($migrationData) && !empty($migrationData)) {
            return true;
        }
        return false;
    }

    public function getFormsByKey()
    {
        \FluentForm\App\Modules\Acl\Acl::verify(['fluentform_settings_manager', 'fluentform_forms_manager']);

        $this->setImporterType();
        $forms = $this->importer->getFormsFormatted();

        wp_send_json([
            'forms'   => $forms,
            'success' => true,
        ]);
    }


}
