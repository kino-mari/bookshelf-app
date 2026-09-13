<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    */

    'accepted' => ':attributeを承認してください。',
    'accepted_if' => ':otherが:valueの場合、:attributeを承認してください。',
    'active_url' => ':attributeは有効なURLではありません。',
    'after' => ':attributeには、:dateより後の日付を指定してください。',
    'after_or_equal' => ':attributeには、:date以降の日付を指定してください。',
    'alpha' => ':attributeには、アルファベットのみ使用できます。',
    'alpha_dash' => ':attributeには、英数字、ハイフン、アンダースコアのみ使用できます。',
    'alpha_num' => ':attributeには、英数字のみ使用できます。',
    'array' => ':attributeには、配列を指定してください。',
    'before' => ':attributeには、:dateより前の日付を指定してください。',
    'before_or_equal' => ':attributeには、:date以前の日付を指定してください。',
    'between' => [
        'array' => ':attributeの項目は、:min個から:max個までにしてください。',
        'file' => ':attributeは、:min KBから:max KBまでのファイルを指定してください。',
        'numeric' => ':attributeは、:minから:maxまでの数字を指定してください。',
        'string' => ':attributeは、:min文字から:max文字で指定してください。',
    ],
    'boolean' => ':attributeには、trueかfalseを指定してください。',
    'confirmed' => ':attributeと確認フィールドが一致していません。',
    'current_password' => 'パスワードが正しくありません。',
    'date' => ':attributeは正しい日付ではありません。',
    'date_equals' => ':attributeには、:dateと同じ日付を指定してください。',
    'date_format' => ':attributeの形式は「:format」と一致していません。',
    'declined' => ':attributeを拒否してください。',
    'declined_if' => ':otherが:valueの場合、:attributeを拒否してください。',
    'different' => ':attributeと:otherには、異なるものを指定してください。',
    'digits' => ':attributeは、:digits桁の数字で指定してください。',
    'digits_between' => ':attributeは、:min桁から:max桁の数字で指定してください。',
    'dimensions' => ':attributeの画像サイズが無効です。',
    'distinct' => ':attributeに重複した値が存在します。',
    'doesnt_end_with' => ':attributeは、次のいずれかで終わってはいけません: :values',
    'doesnt_start_with' => ':attributeは、次のいずれかで始まってはいけません: :values',
    'email' => ':attributeは、有効なメールアドレス形式で指定してください。',
    'ends_with' => ':attributeは、次のいずれかで終わる必要があります: :values',
    'enum' => '選択された:attributeは無効です。',
    'exists' => '選択された:attributeは、有効ではありません。',
    'file' => ':attributeには、ファイルを指定してください。',
    'filled' => ':attributeは必須項目です。',
    'gt' => [
        'array' => ':attributeの項目数は、:value個より多い必要があります。',
        'file' => ':attributeは、:value KBより大きい必要があります。',
        'numeric' => ':attributeは、:valueより大きい必要があります。',
        'string' => ':attributeは、:value文字より長い必要があります。',
    ],
    'gte' => [
        'array' => ':attributeの項目数は、:value個以上である必要があります。',
        'file' => ':attributeは、:value KB以上である必要があります。',
        'numeric' => ':attributeは、:value以上である必要があります。',
        'string' => ':attributeは、:value文字以上である必要があります。',
    ],
    'image' => ':attributeには、画像ファイルを指定してください。',
    'in' => '選択された:attributeは、有効ではありません。',
    'in_array' => ':attributeが:otherに存在しません。',
    'integer' => ':attributeには、整数を指定してください。',
    'ip' => ':attributeには、有効なIPアドレスを指定してください。',
    'ipv4' => ':attributeには、有効なIPv4アドレスを指定してください。',
    'ipv6' => ':attributeには、有効なIPv6アドレスを指定してください。',
    'json' => ':attributeには、有効なJSON文字列を指定してください。',
    'lowercase' => ':attributeは、小文字で入力してください。',
    'lt' => [
        'array' => ':attributeの項目数は、:value個より少ない必要があります。',
        'file' => ':attributeは、:value KBより小さい必要があります。',
        'numeric' => ':attributeは、:valueより小さい必要があります。',
        'string' => ':attributeは、:value文字より短い必要があります。',
    ],
    'lte' => [
        'array' => ':attributeの項目数は、:value個以下である必要があります。',
        'file' => ':attributeは、:value KB以下である必要があります。',
        'numeric' => ':attributeは、:value以下である必要があります。',
        'string' => ':attributeは、:value文字以下である必要があります。',
    ],
    'max' => [
        'array' => ':attributeの項目数は、:max個以下にしてください。',
        'file' => ':attributeには、:max KB以下のファイルを指定してください。',
        'numeric' => ':attributeには、:max以下の数字を指定してください。',
        'string' => ':attributeには、:max文字以下の文字列を指定してください。',
    ],
    'max_digits' => ':attributeは、:max桁以下で指定してください。',
    'mimes' => ':attributeには、以下のファイルタイプを指定してください: :values',
    'mimetypes' => ':attributeには、以下のファイルタイプを指定してください: :values',
    'min' => [
        'array' => ':attributeの項目数は、:min個以上にしてください。',
        'file' => ':attributeには、:min KB以上のファイルを指定してください。',
        'numeric' => ':attributeには、:min以上の数字を指定してください。',
        'string' => ':attributeには、:min文字以上の文字列を指定してください。',
    ],
    'min_digits' => ':attributeは、:min桁以上で指定してください。',
    'missing' => ':attributeフィールドは存在しない必要があります。',
    'missing_if' => ':otherが:valueの場合、:attributeフィールドは存在しない必要があります。',
    'multiple_of' => ':attributeは、:valueの倍数である必要があります。',
    'not_in' => '選択された:attributeは、有効ではありません。',
    'not_regex' => ':attributeの形式が無効です。',
    'numeric' => ':attributeには、数字を指定してください。',
    'password' => [
        'letters' => ':attributeには、少なくとも1つの文字が含まれている必要があります。',
        'mixed' => ':attributeには、少なくとも1つの大文字と1つの小文字が含まれている必要があります。',
        'numbers' => ':attributeには、少なくとも1つの数字が含まれている必要があります。',
        'symbols' => ':attributeには、少なくとも1つの記号が含まれている必要があります。',
        'uncompromised' => '指定された:attributeはデータ漏洩で見つかりました。別の:attributeを選択してください。',
    ],
    'present' => ':attributeが存在している必要があります。',
    'prohibited' => ':attributeの入力は禁止されています。',
    'prohibited_if' => ':otherが:valueの場合、:attributeの入力は禁止されています。',
    'prohibits' => ':attributeがあるため、:otherを入力することはできません。',
    'regex' => ':attributeの形式が無効です。',
    'required' => ':attributeは必須項目です。',
    'required_array_keys' => ':attributeには、次の値のエントリが含まれている必要があります: :values',
    'required_if' => ':otherが:valueの場合、:attributeは必須項目です。',
    'required_if_accepted' => ':otherが承認されている場合、:attributeは必須項目です。',
    'required_unless' => ':otherが:valuesでない限り、:attributeは必須項目です。',
    'required_with' => ':valuesが指定されている場合、:attributeは必須項目です。',
    'required_with_all' => ':valuesが全て指定されている場合、:attributeは必須項目です。',
    'required_without' => ':valuesが指定されていない場合、:attributeは必須項目です。',
    'required_without_all' => ':valuesが全て指定されていない場合、:attributeは必須項目です。',
    'same' => ':attributeと:otherが一致していません。',
    'size' => [
        'array' => ':attributeの項目数は、:size個にしてください。',
        'file' => ':attributeには、:size KBのファイルを指定してください。',
        'numeric' => ':attributeには、:sizeを指定してください。',
        'string' => ':attributeには、:size文字の文字列を指定してください。',
    ],
    'starts_with' => ':attributeは、次のいずれかで始まる必要があります: :values',
    'string' => ':attributeには、文字を指定してください。',
    'timezone' => ':attributeには、有効なタイムゾーンを指定してください。',
    'unique' => '指定の:attributeは既に使用されています。',
    'uploaded' => ':attributeのアップロードに失敗しました。',
    'uppercase' => ':attributeは、大文字で入力してください。',
    'url' => ':attributeは、有効なURL形式で指定してください。',
    'uuid' => ':attributeは、有効なUUIDで指定してください。',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    | 特定のフィールド・ルールの組み合わせで個別の文言を指定したい場合に記述します。
    */

    'custom' => [
        'email' => [
            'required' => 'メールアドレスを入力してください',
            'email' => 'メールアドレスはメール形式で入力してください',
        ],
        'password' => [
            'required' => 'パスワードを入力してください',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    | フォーム項目名（:attribute）の日本語変換テーブル
    */

    'attributes' => [
        // 書籍関連
        'title' => 'タイトル',
        'author' => '著者名',
        'publisher' => '出版社',
        'published_at' => '出版日',
        'price' => '価格',
        'isbn' => 'ISBN',
        'description' => '概要',
        'status' => 'ステータス',
        'rating' => '評価',
        'category_id' => 'カテゴリー',
        'image' => 'カバー画像',

        // ユーザー認証・会員管理関連
        'name' => 'お名前',
        'email' => 'メールアドレス',
        'password' => 'パスワード',
        'password_confirmation' => 'パスワード（確認用）',
    ],

];