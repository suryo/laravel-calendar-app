<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pilih Bulan & Tahun</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
    <h1 class="text-center mb-4">Pilih Bulan dan Tahun</h1>

    <form action="{{ route('calendar.show') }}" method="GET" class="row g-3 justify-content-center">
        <div class="col-md-3">
            <label for="month" class="form-label">Bulan</label>
            <select name="month" id="month" class="form-select">
                @foreach(range(1, 12) as $m)
                    <option value="{{ $m }}">{{ DateTime::createFromFormat('!m', $m)->format('F') }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label for="year" class="form-label">Tahun</label>
            <input type="number" name="year" id="year" class="form-control" value="{{ date('Y') }}" min="1900" max="2100">
        </div>
        <div class="col-md-2 align-self-end">
            <button type="submit" class="btn btn-primary w-100">Tampilkan</button>
        </div>
    </form>
</body>
</html>
