<!DOCTYPE html>
<html>
<head>
    <title>{{ $productInfo->product_name }}</title>
</head>
<body>
    <img src="{{ public_path('storage/'.$productInfo->barcode_image) }}" style="height:100px;">
</body>
</html>