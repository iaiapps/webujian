<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'label',
        'content',
        'order',
    ];

    protected $casts = [
        'question_id' => 'integer',
        'order' => 'integer',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function getLabelDisplay()
    {
        return $this->label.'.';
    }
}
