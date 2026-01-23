<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $fillable = [
        'category',
        'questions_answers',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'questions_answers' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get formatted questions and answers
     */
    public function getFormattedQuestionsAttribute()
    {
        return collect($this->questions_answers ?? [])->map(function ($qa, $index) {
            return [
                'index' => $index + 1,
                'question' => $qa['question'] ?? '',
                'answer' => $qa['answer'] ?? '',
            ];
        });
    }
}
