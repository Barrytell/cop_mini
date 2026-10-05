<!DOCTYPE html>
<html lang="en">
<body style="font-family: sans-serif; color: #102033;">
    <h1 style="font-size: 1.25rem;">{{ $announcement->title }}</h1>
    <div>{!! \App\Support\SafeHtml::from($announcement->body) !!}</div>
</body>
</html>
