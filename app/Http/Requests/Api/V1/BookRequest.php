<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class BookRequest extends FormRequest
{
    /**
     * リクエストの認可判断
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * バリデーションルール
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // ルートパラメータから書籍オブジェクトまたはIDを取得
        $book = $this->route('book');
        $bookId = is_object($book) ? $book->id : $book;

        return [
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'isbn' => [
                'required',
                'digits:13',
                Rule::unique('books', 'isbn')->ignore($bookId),
            ],
            'published_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'image_url' => ['nullable', 'string', 'max:255', 'url'],
            'genres' => ['required', 'array', 'min:1'],
            'genres.*' => ['integer', 'exists:genres,id'],
        ];
    }

    /**
     * エラーメッセージのカスタマイズ
     */
    public function messages(): array
    {
        return [
            'title.required' => 'タイトルを入力してください',
            'title.max' => 'タイトルは255文字以内で入力してください',
            'author.required' => '著者を入力してください',
            'author.max' => '著者は255文字以内で入力してください',
            'isbn.required' => 'ISBNを入力してください',
            'isbn.digits' => 'ISBNはハイフンなしの数字13桁で入力してください',
            'isbn.unique' => 'このISBNはすでに登録されています',
            'published_date.required' => '出版日を入力してください',
            'published_date.date' => '出版日は正しい日付を入力してください',
            'image_url.url' => '画像URLは有効なURL形式（http://... や https://...）で入力してください',
            'image_url.max' => '画像URLは255文字以内で入力してください',
            'genres.required' => 'ジャンルを１つ以上選択してください',
            'genres.array' => 'ジャンルは配列で選択してください',
            'genres.min' => 'ジャンルを１つ以上選択してください',
            'genres.*.exists' => '選択されたジャンルが不正です',
        ];
    }

    /**
     * API用にバリデーション失敗時のレスポンスをJSON形式で返却
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status' => 'error',
                'message' => 'バリデーションエラーが発生しました',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}