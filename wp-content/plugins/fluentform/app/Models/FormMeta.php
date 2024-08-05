<?php

namespace FluentForm\App\Models;

use FluentForm\Framework\Support\Arr;

class FormMeta extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'fluentform_form_meta';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * A formMeta is owned by a form.
     *
     * @return \FluentForm\Framework\Database\Orm\Relations\BelongsTo
     */
    public function form()
    {
        return $this->belongsTo(Form::class, 'form_id', 'id');
    }

    public static function prepare($attributes, $predefinedForm)
    {
        $formMeta = [];
    
        $formMeta[] = [
            'meta_key' => 'formSettings',
            'value'    => json_encode(Form::getFormsDefaultSettings()),
        ];
    
        $formMeta[] = [
            'meta_key' => 'template_name',
            'value'    => Arr::get($attributes, 'predefined'),
        ];
    
        if (isset($predefinedForm['notifications'])) {
            $formMeta[] = [
                'meta_key' => 'notifications',
                'value'    => json_encode($predefinedForm['notifications']),
            ];
        }
    
        if (Arr::get($attributes, 'predefined') == 'conversational') {
            $formMeta[] = [
                'meta_key' => 'is_conversion_form',
                'value'    => 'yes',
            ];
        }

        return $formMeta;
    }

    public static function retrieve($key, $formId = null, $default  = null)
    {
        $meta = static::when($formId, function ($q) use ($formId) {
            return $q->where('form_id', $formId);
        })
            ->where('meta_key', $key)
            ->first();

        if ($meta && isset($meta->value)) {
            $value = json_decode($meta->value, true);
            if (JSON_ERROR_NONE == json_last_error()) {
                return $value;
            }
            return $meta->value;
        }

        return $default;
    }

    public static function store(Form $form, $formMeta)
    {
        foreach ($formMeta as $meta) {
            $meta['value'] = trim(preg_replace('/\s+/', ' ', $meta['value']));

            $form->formMeta()->create([
                'meta_key' => $meta['meta_key'],
                'value'    => $meta['value'],
            ]);
        }
    }

    public static function persist($formId, $metaKey, $metaValue)
    {
        if (is_array($metaValue) || is_object($metaValue)) {
            $metaValue = json_encode($metaValue);
        }

        return static::updateOrCreate(
            ['form_id' => $formId, 'meta_key' => $metaKey],
            ['value' => $metaValue]
        );
    }

    public static function remove($formId, $metaKey)
    {
        static::where('form_id', $formId)
            ->where('meta_key', $metaKey)
            ->delete();
    }
}
