<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ strip_tags($policy->title) ?? 'Privacy Policy' }}</title>
    <link rel="icon" href="{{ asset('favicon-qadampayk.png') }}" type="image/x-icon">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }

        /* WRAPPER */
        .qadam-privacy-wrapper {
            padding: 0;
        }

        /* HEADER */
        .qadam-policy-head {
            background-color: #e6f4ee;
            padding: 60px 0 40px;
        }

        .qadam-logo img {
            width: 140px;
            margin-bottom: 20px;
        }

        .qadam-policy-head h1 {
            font-size: 42px;
            color: #008955;
            font-weight: 700;
        }

        /* CONTAINER */
        .qadam-policy-container {
            max-width: 900px;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        /* CONTENT STYLING */
        .qadam-policy-content h1,
        .qadam-policy-content h2,
        .qadam-policy-content h3 {
            margin-top: 1.5rem;
            margin-bottom: 1rem;
            font-weight: 600;
            color: #008955;
        }

        .qadam-policy-content p {
            font-size: 16px;
            line-height: 1.7;
            margin-bottom: 1rem;
        }

        .qadam-policy-content ul,
        .qadam-policy-content ol {
            margin-bottom: 1rem;
            padding-left: 2rem;
            color: #008955;
        }

        .qadam-policy-content li {
            margin-bottom: 0.5rem;
        }

        .qadam-policy-content a {
            color: #008955;
            text-decoration: none;
        }

        .qadam-policy-content a:hover {
            text-decoration: underline;
        }

        .qadam-policy-content blockquote {
            border-left: 4px solid #008955;
            padding-left: 15px;
            color: #555;
            margin: 1.5rem 0;
            font-style: italic;
            background-color: #f1f5f9;
        }
    </style>
</head>
<body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>