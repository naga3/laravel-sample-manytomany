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
