<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BookIndexRequest extends FormRequest
{
    /**
     * リクエストを実行する権限があるか
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 検索パラメータのバリデーションルール
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
     * 日本語のエラーメッセージ
     */
    public function messages(): array
    {
        return [
            'keyword.max' => '検索キーワードは255文字以内で指定してください。',
            'genre_id.integer' => 'ジャンルIDは数値で指定してください。',
            'genre_id.exists' => '指定されたジャンルが存在しません。',
            'page.integer' => 'ページ番号は整数で指定してください。',
            'page.min' => 'ページ番号は1以上で指定してください。',
            'per_page.integer' => '1ページの取得件数は整数で指定してください。',
            'per_page.min' => '1ページの取得件数は1以上で指定してください。',
            'per_page.max' => '1ページの取得件数は最大100件までです。',
        ];
    }

    /**
     * バリデーション失敗時にJSON形式の422レスポンスを返す
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'status' => 'error',
                'message' => 'リクエストパラメータが不正です。',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
