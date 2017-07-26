# はじめに

Laravelで多対多(n:n, Many To Many)リレーションはとても簡単に扱うことが出来ます。
今回は、ひとつの投稿に対して複数のタグを付与できる掲示板をサンプルに解説してみます。

![screen.gif](https://qiita-image-store.s3.amazonaws.com/0/32030/2ee7e1b2-f2a5-6901-b465-54eece13eb73.gif)

https://github.com/naga3/laravel_tagbbs

# プロジェクト作成

`tagbbs`プロジェクトを作成します。
今回はLaravel Installerを使いましたが、各自の環境に合わせてください。

## Laravel Installerの導入

```
composer global require laravel/installer
```

## プロジェクト作成

```
laravel new tagbbs
```

# データベース設定

## データベース作成

データベースを作成し、`.env`ファイルを編集します。
作成したDB名が`tagbbs`、ユーザー名`root`パスワードなしの場合以下のようになります。

```
DB_DATABASE=tagbbs
DB_USERNAME=root
DB_PASSWORD=
```

## モデル・マイグレーション作成

まず投稿用のモデル`Post`とタグ用のモデル`Tag`を作成します。

```
php artisan make:model Post -m
php artisan make:model Tag -m
```

`-m`オプションでマイグレーションファイルが同時に作成されます。

また、中間テーブルはモデルが不要なのでマイグレーションファイルのみを作成します。

```
php artisan make:migration create_post_tag_table --create=post_tag
```

## マイグレーション編集

`database/migrations`ディレクトリにある、各マイグレーションファイルのカラムを編集します。

### xxxx_create_posts_table.php

投稿テーブルです。

```php
        Schema::create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('body');
            $table->timestamps();
        });
```

`body`カラムが本文です。

### xxxx_create_tags_table.php

タグテーブルです。

```php
        Schema::create('tags', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });
```

`name`カラムにタグ名が入ります。

### xxxx_create_post_tag_table.php

投稿テーブルとタグテーブルを関連付ける中間テーブルです。

```php
        Schema::create('post_tag', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('post_id');
            $table->integer('tag_id');
        });
```

## マイグレーション

マイグレーションを実行し、テーブルを作成します。

```
php artisan migrate
```

`posts`, `tags`, `post_tag`テーブルが定義通り作成されていることを確認してください。

## タグデータの生成

今回はタグ登録機能を省略し、Laravelのシーディングで登録します。

`database/seeds/DatabaseSeeder.php`の`run`メソッドを書きます。

```php
    public function run()
    {
        $tags = ['うどん', 'そば', 'ラーメン', 'フォー'];
        foreach ($tags as $tag) App\Tag::create(['name' => $tag]);
    }
```

シーディングを実行します。

```
php artisan db:seed
```

`tags`テーブルに4レコード登録されていることを確認してください。

# 多対多(Many To Many)リレーションの設定

モデルクラスに多対多のリレーションを設定します。

`app/Post.php`に以下のメソッドを追加します。

```php
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
```

これで、`tags`メソッドにより投稿に紐付けられたタグ一覧を取得できるようになります。

# ルートの設定

次にルートを設定します。

`routes/web.php`の`Route::get`呼び出しに、投稿一覧とタグ一覧を付与します。

```php
Route::get('/', function () {
    return view('welcome', ['posts' => App\Post::all(), 'tags' => App\Tag::all()]);
});
```

`routes/web.php`に`Route::post`を追加します。

```php
Route::post('/', function () {
    $post = new App\Post();
    $post->body = request()->body;
    $post->save();
    $post->tags()->attach(request()->tags);
    return redirect('/');
});
```

投稿フォームから本文取得して`posts`テーブルに保存し、さらに投稿に付与されたタグも保存しています。

`$post->tags()`で投稿に紐付いたタグ一覧を取得出来ます。`attach`メソッドにタグIDの配列を渡すことによって一気にタグを登録しています。

# ビューの設定

`resources/views/welcome.blade.php`を以下のようにします。

```php
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tag BBS</title>
</head>
<body>
    <form method="post">
        {{ csrf_field() }}
        @foreach ($tags as $tag)
            <input type="checkbox" name="tags[]" value="{{ $tag->id }}">{{ $tag->name }}
        @endforeach
        <input name="body">
        <button>投稿</button>
    </form>
    @foreach ($posts as $post)
        <hr>
        <p>Tags: @foreach ($post->tags as $tag) {{ $tag->name }} @endforeach </p>
        <p>{{ $post->body }}</p>
    @endforeach
</body>
</html>
```

# 実行

```
php artisan serve
```

などで実行し、ブラウザでタグ付きの登録ができることを確認します。
