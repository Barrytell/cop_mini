<!DOCTYPE html>
<html lang="en">
<body style="font-family: sans-serif; color: #102033;">
    <p>A message arrived from the website.</p>
    <p><strong>{{ $contact->name }}</strong> · {{ $contact->email }}@if ($contact->phone) · {{ $contact->phone }}@endif</p>
    <p><strong>{{ $contact->subject }}</strong></p>
    <p style="white-space: pre-wrap;">{{ $contact->body }}</p>
</body>
</html>
