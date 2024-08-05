<?php

namespace FluentForm\App\Modules\Component;

abstract class BaseComponent
{
    use ComponentInitTrait;

    /**
     * The unique name of the component.
     *
     * @return string
     */
    abstract public function name();

    /**
     * The label of the component.
     *
     * @return string
     */
    abstract public function label();

    /**
     * The element type of the component from already
     * available elements (input_text, textarea e.t.c).
     *
     * @return string
     */
    abstract public function element();

    /**
     * The template type of the component to display preview in editor dropzone from
     * already available templates (inputText, selectCountry, addressFields e.t.c).
     *
     * @return string
     */
    abstract public function template();

    /**
     * Render the element on frontend form.
     *
     * @param array  $component Element Config
     * @param object $form      The Form Object
     *
     * @return void
     */
    abstract public function render($component, $form);

    /**
     * Form submission callback.
     *
     * @param array $formData Submitted form data
     * @param int   $formId   Submitted form id
     * @param array $config   Form elements config
     *
     * @return array $formData
     */
    abstract public function onSubmit($formData, $formId, $config);

    /**
     * Component position in editor. If null is returned then
     * the element will be pushed at last but the derived class
     * will override this method if any customization is needed.
     *
     * @return int|null
     */
    public function index()
    {
        return null;
    }

    /**
     * The group, where the component should be added. By default,
     * the element will be added in "general" group and the derived
     *  class will override this method if any customization is needed.
     *
     * @return string general|advanced|container
     */
    public function group()
    {
        return 'general';
    }

    /**
     * The element icon class for the component to display in the button in
     * the editor element list which is mapped to editor_options.icon_class.
     *
     * @return string
     */
    public function elementIconClass()
    {
        return 'icon-cog';
    }

    /**
     * Element editor/form attributes and the derived class will
     * override this method if any customization is needed.
     *
     * @param array $dafault
     *
     * @return array $default
     */
    public function attributes($default)
    {
        return $default;
    }

    /**
     * Element editor settings and the derived class will
     * override this method if any customization is needed.
     *
     * @param array $dafault
     *
     * @return array $default
     */
    public function settings($default)
    {
        return $default;
    }

    /**
     * Element editor options and the derived class will
     * override this method if any customization is needed.
     *
     * @param array $dafault
     *
     * @return array $default
     */
    public function options($default)
    {
        return $default;
    }

    /**
     * Element's form submission validation rules and the derived
     * class will override this method if needed any customization.
     *
     * @return array
     */
    public function validationRules()
    {
        return [];
    }

    /**
     * Element editor placement settings and the derived class
     * will override this method if any customization is needed.
     *
     * @param array $placemenSettings
     *
     * @return array $placemenSettings
     */
    public function placementSettings($placemenSettings)
    {
        return $placemenSettings;
    }

    /**
     * The customization for the component and derived class can
     * override this method if any customization is needed.
     *
     * @param array $customizationSettings
     *
     * @return array $customizationSettings
     */
    public function customizationSettings($customizationSettings)
    {
        return $customizationSettings;
    }

    /**
     * Add the component type in fluentform field types to
     * be available in FormFieldParser and derived class can
     * override this method if any customization is needed.
     *
     * @param array $types
     *
     * @return array $types
     */
    public function addType($types)
    {
        $types[] = $this->name();

        return $types;
    }

    /**
     * The keywords to search the element in the editor and
     * the derived class will override this method if needed.
     *
     * @return array
     */
    public function searchBy()
    {
        return [$this->name(), $this->label()];
    }

    /**
     * Transform the element's submitted value saved in database
     * to show it properly/formatted in entry page if needed and
     * this method implementation optional so a default method is
     * implemented here and original value is returned. In any case
     * if any customization of the value is needed then the derived
     * class will override it and will format and return the the value.
     *
     * @param mixed  $value  [description]
     * @param string $field
     * @param int    $formId
     *
     * @return mixed $value
     */
    public function formatEntryValue($value, $field, $formId)
    {
        return $value;
    }
}
