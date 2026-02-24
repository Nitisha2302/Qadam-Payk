<!DOCTYPE html>
<html>
<head>
    <title>Courier Revenue Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background-color: #ddd; }
    </style>
</head>
<body>

<h2>City-wise Courier Revenue Report</h2>

<table>
    <thead>
        <tr>
            <th>City</th>
            <th>Total Orders</th>
            <th>Total Revenue</th>
        </tr>
    </thead>
    <tbody>
        @foreach($reportData as $row)
        <tr>
            <td>{{ $row['city'] }}</td>
            <td>{{ $row['total_orders'] }}</td>
            <td>₹{{ number_format($row['total_revenue'], 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>
