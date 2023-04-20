<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'completed'];

    protected $casts = ['completed' => 'boolean'];

    protected $attributes = [
        'description' => '',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->attributes['completed'] = false;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
