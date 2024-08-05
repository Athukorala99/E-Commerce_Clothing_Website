<?php

namespace FluentForm\App\Models;

class SubmissionMeta extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'fluentform_submission_meta';

    /**
     * A formMeta is owned by a form.
     *
     * @return \FluentForm\Framework\Database\Orm\Relations\BelongsTo
     */
    public function form()
    {
        return $this->belongsTo(Form::class, 'form_id', 'id');
    }

    /**
     * A formMeta is owned by a submission.
     *
     * @return \FluentForm\Framework\Database\Orm\Relations\BelongsTo
     */
    public function submission()
    {
        return $this->belongsTo(Submission::class, 'response_id', 'id');
    }


    public static function retrieve($key, $submissionId = null, $default  = null)
    {
        $meta = static::when($submissionId, function ($q) use ($submissionId) {
            return $q->where('response_id', $submissionId);
        })
            ->where('meta_key', $key)
            ->first();

        if ($meta && isset($meta->value)) {
            return maybe_unserialize($meta->value);
        }

        return $default;
    }

    public static function persist($submissionId, $metaKey, $metaValue, $formId = null)
    {
        $metaValue = maybe_serialize($metaValue);
        if (!$formId) {
            $formId = Submission::select('form_id')->where('id', $submissionId)->value('form_id');
        }
        return static::updateOrCreate(
            ['response_id' => $submissionId, 'meta_key' => $metaKey],
            [
                'value' => $metaValue,
                'form_id'     => $formId,
            ]
        );
    }
}
