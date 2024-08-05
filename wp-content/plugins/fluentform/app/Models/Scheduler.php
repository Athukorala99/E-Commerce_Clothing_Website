<?php

namespace FluentForm\App\Models;

class Scheduler extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ff_scheduled_actions';

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
     * A schedule action is owned by a submission.
     *
     * @return \FluentForm\Framework\Database\Orm\Relations\BelongsTo
     */
    public function submission()
    {
        return $this->belongsTo(Submission::class, 'origin_id', 'id');
    }
}
