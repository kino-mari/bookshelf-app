<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReadingPlanRequest extends FormRequest
{
    /**
     * リクエストを実行する権限があるか
     */
    public function authorize(): bool
    {
        $plan = $this->route('plan');

        // ログインしていない場合は拒否
        if ($this->user() === null) {
            return false;
        }

        // 新規作成の場合は許可
        if ($plan === null) {
            return true;
        }

        // 編集の場合は自分の読書計画だけ許可
        return $plan->user_id === $this->user()->id;
    }

    /**
     * バリデーションルール
     */
    public function rules(): array
    {
        $plan = $this->route('plan');

        // 新規登録の場合
        if ($plan === null) {
            return [
                'book_id' => [
                    'required',
                    'integer',
                    'exists:books,id',
                    Rule::unique('reading_plans', 'book_id')
                        ->where(fn ($query) => $query->where(
                            'user_id',
                            $this->user()->id
                        )),
                ],
                'due_date' => [
                    'required',
                    'date',
                    'after_or_equal:today',
                ],
            ];
        }

        // 編集の場合
        return [
            'due_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],
        ];
    }

    /**
     * 日本語のエラーメッセージ
     */
    public function messages(): array
    {
        return [
            'book_id.required' => '書籍を選択してください。',
            'book_id.integer' => '正しい書籍を選択してください。',
            'book_id.exists' => '選択された書籍は存在しません。',
            'book_id.unique' => 'この書籍はすでに読書計画に登録されています。',

            'due_date.required' => '読了予定日を入力してください。',
            'due_date.date' => '正しい日付を入力してください。',
            'due_date.after_or_equal' => '読了予定日は今日以降の日付を指定してください。',
        ];
    }
}
