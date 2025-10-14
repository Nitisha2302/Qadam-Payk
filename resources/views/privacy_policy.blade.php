<!DOCTYPE html>
<html>
<head>
    <title>{{ $policy->title ?? 'Privacy Policy' }}</title>
</head>
<body>
    <div class="container">
        {!! $policy->content ?? '<p>No privacy policy found.</p>' !!}
    </div>
</body>
</html>
