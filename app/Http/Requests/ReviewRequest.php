<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewRequest extends FormRequest
{
    /**
     * リクエストを実行する権限があるか確認する
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * リクエストに適用するバリデーションルールを取得する
     *
     * @return array バリデーションルール
     */
    public function rules(): array
    {
        return [
            // 評価値（1〜5の整数値の範囲を検証）
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string', 'max:1000'],
        ];
    }

    /**
     * バリデーションエラー時に表示するメッセージを取得する
     *
     * @return array エラーメッセージ
     */
    public function messages(): array
    {
        return [
            'rating.required' => '評価を選択してください。',
            'rating.integer' => '評価は数値で指定してください。',
            'rating.between' => '評価は1〜5の間で選択してください。',
            'comment.required' => 'レビュー内容を入力してください。',
            'comment.max' => 'レビュー内容は1000文字以内で入力してください。',
        ];
    }
}
