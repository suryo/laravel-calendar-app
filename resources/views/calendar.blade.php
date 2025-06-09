<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kalender Highlight 2025</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .highlight {
            background-color: yellow !important;
        }
        td {
            height: 80px;
            vertical-align: top;
        }
    </style>
</head>
<body class="container py-4">
    <h1 class="text-center mb-4">Kalender Highlight 2025</h1>

    @foreach($months as $month => $name)
        <h3 class="mt-5">{{ $name }} 2025</h3>
        <table class="table table-bordered text-center">
            <thead class="table-dark">
                <tr>
                    <th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th>
                    <th>Fri</th><th>Sat</th><th>Sun</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $start = \Carbon\Carbon::create(2025, $month, 1);
                    $startDay = $start->dayOfWeekIso; // Monday = 1
                    $daysInMonth = $start->daysInMonth;
                    $day = 1;
                @endphp

                @for ($week = 0; $week < 6; $week++)
                    <tr>
                        @for ($dow = 1; $dow <= 7; $dow++)
                            @if ($week === 0 && $dow < $startDay)
                                <td></td>
                            @elseif ($day > $daysInMonth)
                                <td></td>
                            @else
                                @php
                                    $dateStr = sprintf('2025-%02d-%02d', $month, $day);
                                @endphp
                                <td class="{{ in_array($dateStr, $highlightDates) ? 'highlight' : '' }}">
                                    {{ $day++ }}
                                </td>
                            @endif
                        @endfor
                    </tr>
                @endfor
            </tbody>
        </table>
    @endforeach
</body>
</html>
