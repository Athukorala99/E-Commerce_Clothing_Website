<?php

namespace FluentForm\App\Models;

class EntryMeta extends Model
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
     * A formMeta is owned by an entry.
     *
     * @return \FluentForm\Framework\Database\Orm\Relations\BelongsTo
     */
    public function entry()
    {
        return $this->belongsTo(Entry::class, 'response_id', 'id');
    }
}
