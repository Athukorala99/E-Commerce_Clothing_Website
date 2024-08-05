<?php

namespace FluentForm\App\Models;

class User extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['id', 'name', 'email', 'permalink'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['ID', 'user_email', 'user_pass', 'display_name'];

    /**
     * Get the id of the user.
     *
     * @return bool
     */
    public function getIdAttribute()
    {
        return (int) $this->attributes['ID'];
    }

    /**
     * Get the name of the user.
     *
     * @return string
     */
    public function getNameAttribute()
    {
        return $this->attributes['display_name'];
    }

    /**
     * Get the email of the user.
     *
     * @return string
     */
    public function getEmailAttribute()
    {
        return $this->attributes['user_email'];
    }

    /**
     * Get the permalink of the user.
     *
     * @return string
     */
    public function getPermalinkAttribute()
    {
        return get_edit_user_link($this->attributes['ID']);
    }
}
