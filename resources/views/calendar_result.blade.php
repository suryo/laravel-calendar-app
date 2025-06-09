<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Kalender {{ $month }}/{{ $year }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .highlight {
            background-color: yellow;
        }


        td {
            height: 80px;
            vertical-align: top;
        }

        td ul {
            list-style-type: '📌 ';
            padding-left: 1em;

        }

        .task-date {
            background-color: #d4eaff !important;
            /* biru muda */
        }

        .active-day {
            background-color: #e6f9e6 !important;
            /* hijau muda */
        }

        .holiday {
            background-color: #ffe6e6 !important;
            /* merah muda */
        }
    </style>
</head>

<body class="container py-4">
    <h1 class="text-center mb-4">Kalender {{ DateTime::createFromFormat('!m', $month)->format('F') }} {{ $year }}
    </h1>

    <a href="{{ route('calendar.index') }}" class="btn btn-secondary mb-3">🔙 Kembali</a>

    <table class="table table-bordered text-center">
        <thead class="table-dark">
            <tr>
                <th>Mon</th>
                <th>Tue</th>
                <th>Wed</th>
                <th>Thu</th>
                <th>Fri</th>
                <th>Sat</th>
                <th>Sun</th>
            </tr>
        </thead>
        <tbody>
            @php
                $start = \Carbon\Carbon::create($year, $month, 1);
                $startDay = $start->dayOfWeekIso;
                $daysInMonth = $start->daysInMonth;
                $day = 1;
            @endphp

            @for ($week = 0; $week < 6; $week++)
                <tr>
                    @for ($dow = 1; $dow <= 7; $dow++)
                        @if ($week === 0 && $dow < $startDay)
                            <td></td>
                        @elseif($day > $daysInMonth)
                            <td></td>
                        @else
                            @php
                                $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
                                $isHighlight = in_array($dateStr, $highlightDates);
                                $hasTask = isset($taskMap[$dateStr]);
                                $isActiveDay = false;
                                $isHoliday = in_array($dateStr, $holidays ?? []);

                                // Deteksi hari aktif dari stats['detail']
                                foreach ($stats['detail'] as $days) {
                                    if (in_array($dateStr, $days)) {
                                        $isActiveDay = true;
                                        break;
                                    }
                                }

                                $class = trim(
                                    ($isHighlight ? 'highlight ' : '') .
                                        ($hasTask ? 'task-date ' : '') .
                                        ($isActiveDay ? 'active-day' : '') .
                                        ($isHoliday ? 'holiday' : ''),
                                );
                            @endphp

                            <td class="{{ $class }}" data-date="{{ $dateStr }}"
                                onclick="loadTaskList('{{ $dateStr }}')" data-bs-toggle="modal"
                                data-bs-target="#taskModal">
                                <div><strong>{{ $day++ }}</strong></div>
                                @if ($isHoliday)
                                    <div class="text-danger small">🎌 Libur</div>
                                @endif
                                @if (isset($taskMap[$dateStr]))
                                    <ul class="text-start small px-2 mt-1 mb-0">
                                        @foreach ($taskMap[$dateStr] as $task)
                                            <li>{{ $task['task'] }}</li>
                                        @endforeach
                                    </ul>
                                @endif

                            </td>
                        @endif
                    @endfor
                </tr>
            @endfor
        </tbody>
    </table>

    <div class="mt-4">
        <h5>📊 Statistik Kalender:</h5>
        <ul>
            <li><strong>Total Minggu Aktif:</strong> {{ $stats['total_active_weeks'] }}</li>
            <li><strong>Hari Aktif per Minggu:</strong>
                <ul>
                    @foreach ($stats['active_days_per_week'] as $week => $count)
                        <li>Minggu ke-{{ $week }}: {{ $count }} hari</li>
                    @endforeach
                </ul>
            </li>
        </ul>
    </div>

    <h5 class="mt-4">📊 Statistik Produktivitas:</h5>
<ul>
    <li><strong>Produktivitas per hari:</strong>
        <ul>
            @foreach($dailyProductivity as $date => $val)
                <li>{{ $date }}: {{ $val }}%</li>
            @endforeach
        </ul>
    </li>
    <li><strong>Produktivitas per minggu:</strong>
        <ul>
            @foreach($weeklyProductivity as $week => $val)
                <li>Minggu ke-{{ $week }}: {{ $val }}%</li>
            @endforeach
        </ul>
    </li>
    <li><strong>Produktivitas rata-rata bulan ini:</strong> {{ $monthlyProductivity }}%</li>
</ul>



    <!-- Modal -->
    <div class="modal fade" id="taskModal" tabindex="-1" aria-labelledby="taskModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="{{ route('tasks.store') }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="taskModalLabel">Daftar Task & Tambah</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="start_date" id="startDateInput">

                    <!-- Task List -->
                    <div id="taskListContainer" class="mb-4">
                        <strong>Task yang sudah ada:</strong>
                        <ul class="list-group" id="taskList"></ul>
                    </div>

                    <!-- Form Tambah -->
                    <div class="mb-3">
                        <label class="form-label">Task</label>
                        <input type="text" class="form-control" name="task" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Level</label>
                        <input type="text" class="form-control" name="level" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Priority</label>
                        <input type="text" class="form-control" name="priority" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dateline</label>
                        <input type="date" class="form-control" name="dateline" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <input type="text" class="form-control" name="status" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Simpan Task</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="taskModal" tabindex="-1" aria-labelledby="taskModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('tasks.store') }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="taskModalLabel">Tambah Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="start_date" id="startDateInput">
                    <div class="mb-3">
                        <label class="form-label">Task</label>
                        <input type="text" class="form-control" name="task" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Level</label>
                        <input type="text" class="form-control" name="level" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Priority</label>
                        <input type="text" class="form-control" name="priority" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dateline</label>
                        <input type="date" class="form-control" name="dateline" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <input type="text" class="form-control" name="status" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Simpan</button>
                </div>
            </form>
        </div>
    </div>

</body>

</html>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const allTasks = @json($taskMap);

    function loadTaskList(date) {
        document.getElementById('startDateInput').value = date;
        const listEl = document.getElementById('taskList');
        console.log('Loading tasks for date:', date);
        console.log(listEl)
        listEl.innerHTML = '';

        const tasks = allTasks[date] || [];
        if (tasks.length === 0) {
            listEl.innerHTML = '<li class="list-group-item">Tidak ada task.</li>';
        } else {
            tasks.forEach(task => {
                const item = document.createElement('li');
                item.className = 'list-group-item';
                item.innerHTML =
                    `<strong>${task.task}</strong> | Level: ${task.level}, Priority: ${task.priority}, Status: ${task.status}`;
                listEl.appendChild(item);
            });
        }
    }
</script>
<script>
    document.querySelectorAll('td[data-date]').forEach(td => {
        td.addEventListener('click', function() {
            const date = this.getAttribute('data-date');
            document.getElementById('startDateInput').value = date;
            new bootstrap.Modal(document.getElementById('taskModal')).show();
        });
    });
</script>
