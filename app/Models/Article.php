<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Article extends Model
{
    use HasFactory;

    /**
     * Guarded from mass assigment
     * 
     * @var array
     */
    protected $guarded = [];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['excerpt'];

    /**
     * Allowed attributes of model for sorting
     * 
     * @var array 
     */
    public $allowedSorts = [
        'id',
        'title',
        'body',
        'created_at',
        'updated_at'
    ];

    /**
     * Allowed attributes for filtering
     * 
     * @var array
     */
    public $allowedFilters = [
        'id',
        'title',
        'body',
        'created_at',
        'updated_at'
    ];


    /**
     * Generate accessable path of resource
     * 
     * @return String
     */
    public function path()
    {
        return "/articles/{$this->id}";
    }

    public function getExcerptAttribute()
    {
        return Str::limit($this->body, 150, $end='...');
    }



    /**
     * Associated user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
