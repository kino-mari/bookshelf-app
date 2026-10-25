<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BookIndexRequest extends FormRequest
{
    /**
     * リクエストの認可判断
     */
    public function authorize(): bool
    {
        return true; // false から true に変更
    }

    /**
     * クエリパラメータのバリデーションルール (AP01対応)
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'keyword' => ['nullable', 'string', 'max:255'],
            'genre_id' => ['nullable', 'integer', 'exists:genres,id'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * カスタムエラーメッセージ（任意）
     */
    public function messages(): array
    {
        return [
            'keyword.max' => '検索キーワードは255文字以内で指定してください',
            'genre_id.integer' => 'ジャンルIDは数値で指定してください',
            'genre_id.exists' => '指定されたジャンルが存在しません',
            'page.integer' => 'ページ番号は整数で指定してください',
            'per_page.max' => '1ページの取得件数は最大100件までです',
        ];
    }

    /**
     * API用にバリデーション失敗時、JSONレスポンス（422）を返却
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status' => 'error',
                'message' => 'リクエストパラメータが不正です',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}