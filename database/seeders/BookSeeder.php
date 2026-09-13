<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 登録者（山田太郎）を取得
        $user = User::first();

        // 登録データ（11件）
        $books = [
            [
                'title' => '吾輩は猫である',
                'author' => '夏目漱石',
                'isbn' => '9784101010014',
                'published_at' => '1905-01-01',
                'description' => '夏目漱石の長編小説。一匹の風刺精神あふれる猫の目線を通して、人間社会の滑稽さやエゴを描いた名作。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=1',
                'genres' => ['小説'],
            ],
            [
                'title' => '人を動かす',
                'author' => 'D・カーネギー',
                'isbn' => '9784422100524',
                'published_at' => '1936-10-01',
                'description' => '人間関係の原則を深く追求した自己啓発本の金字塔。あらゆる人間関係の悩みを解決する実践的なアドバイスが満載。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=2',
                'genres' => ['ビジネス', '自己啓発'],
            ],
            [
                'title' => 'リーダブルコード',
                'author' => 'Dustin Boswell',
                'isbn' => '9784873115658',
                'published_at' => '2012-06-23',
                'description' => '「理解しやすいコード」を書くための実践的なノウハウとテクニックを凝縮した、エンジニア必読のバイブル。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=3',
                'genres' => ['技術書'],
            ],
            [
                'title' => '7つの習慣',
                'author' => 'スティーブン・R・コヴィー',
                'isbn' => '9784863940246',
                'published_at' => '2013-08-30',
                'description' => '真の成功と人生の豊かさを手に入れるための原則を体系化した、世界的なベストセラー。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=4',
                'genres' => ['ビジネス', '自己啓発'],
            ],
            [
                'title' => '坊っちゃん',
                'author' => '夏目漱石',
                'isbn' => '9784101010021',
                'published_at' => '1906-04-01',
                'description' => '正義感あふれる江戸っ子の「坊っちゃん」が、四国の旧制中学校で巻き起こす痛快な学園物語。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=5',
                'genres' => ['小説'],
            ],
            [
                'title' => 'サピエンス全史',
                'author' => 'ユヴァル・ノア・ハラリ',
                'isbn' => '9784309226712',
                'published_at' => '2016-09-08',
                'description' => 'ホモ・サピエンスがなぜ地球の支配者になれたのかを、認知革命・農業革命・科学革命の視点から解き明かす。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=6',
                'genres' => ['歴史', '科学'],
            ],
            [
                'title' => 'Clean Code',
                'author' => 'Robert C. Martin',
                'isbn' => '9784048930598',
                'published_at' => '2017-12-18',
                'description' => 'アジャイルソフトウェア達人の技。読みやすく保守しやすいコードを美しく書くための原則とリファクタリング技法。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=7',
                'genres' => ['技術書'],
            ],
            [
                'title' => '嫌われる勇気',
                'author' => '岸見一郎・古賀史健',
                'isbn' => '9784478025819',
                'published_at' => '2013-12-13',
                'description' => 'アドラー心理学の対話篇。「対人関係の悩み」を解消し、自分らしく自由に生きるための思考法を説く。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=8',
                'genres' => ['自己啓発'],
            ],
            [
                'title' => '火花',
                'author' => '又吉直樹',
                'isbn' => '9784163902302',
                'published_at' => '2015-03-11',
                'description' => '売れない芸人とその師匠となる先輩芸人の純粋な葛藤と生き様を描いた芥川賞受賞作。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=9',
                'genres' => ['小説'],
            ],
            [
                'title' => 'FACTFULNESS',
                'author' => 'ハンス・ロスリング',
                'isbn' => '9784822289607',
                'published_at' => '2019-01-11',
                'description' => 'データと事実に基づいて世界を正しく見る習慣。思い込みや本能的偏見を乗り越えるための必読書。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=10',
                'genres' => ['ビジネス', '科学'],
            ],
            [
                'title' => 'コンテナ物語',
                'author' => 'マルク・レビンソン',
                'isbn' => '9784822251468',
                'published_at' => '2007-01-18',
                'description' => '「箱」の標準化が世界貿易のコストを激減させ、世界経済を激変させた画期的な歴史とイノベーションの記録。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=11',
                'genres' => ['ビジネス', '歴史'],
            ],
        ];

        foreach ($books as $bookData) {
            // ジャンル名配列を抜き出す
            $genreNames = $bookData['genres'];
            unset($bookData['genres']);

            // user_id を追加
            $bookData['user_id'] = $user->id;

            // ISBNをキーにして検索し、存在しなければ作成
            $book = Book::firstOrCreate(
                ['isbn' => $bookData['isbn']],
                $bookData
            );

            // ジャンル名からID一覧を取得して多対多の紐付けを実施
            $genreIds = Genre::whereIn('name', $genreNames)->pluck('id');
            $book->genres()->sync($genreIds);
        }
    }
}