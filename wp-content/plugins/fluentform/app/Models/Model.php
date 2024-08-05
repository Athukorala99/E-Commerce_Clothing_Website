<?php

namespace FluentForm\App\Models;

use FluentForm\Framework\Database\Orm\Model as BaseModel;

class Model extends BaseModel
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id', 'ID'];

    /**
     * Get the number of models to return per page.
     *
     * @return int
     */
    public function getPerPage()
    {
        $request = wpFluentForm('request');

        return intval(
            $request->get('per_page', $request->get('perPage', $this->perPage))
        );
    }
}
