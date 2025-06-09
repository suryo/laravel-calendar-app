<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Task;
use App\Models\Holiday;



use Carbon\CarbonPeriod;

class CalendarController extends Controller
{
    public function index()
    {
        return view('calendar_form');
    }

    public function show(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:1900|max:2100',
        ]);

        $month = $request->month;
        $year = $request->year;

        $holidays = Holiday::whereYear('holiday_date', $year)
            ->whereMonth('holiday_date', $month)
            ->pluck('holiday_date')
            ->map(fn($date) => \Carbon\Carbon::parse($date)->format('Y-m-d'))
            ->toArray();

        // Ambil semua task yang punya start_date atau dateline di bulan/tahun yang diminta
        $taskDates = Task::where(function ($query) use ($month, $year) {
            $query->whereMonth('start_date', $month)->whereYear('start_date', $year);
        })->orWhere(function ($query) use ($month, $year) {
            $query->whereMonth('dateline', $month)->whereYear('dateline', $year);
        })->get();

        // Ambil tanggal2 unik dari start_date & dateline
        $highlightDates = $taskDates->flatMap(function ($task) {
            return [$task->start_date->format('Y-m-d'), $task->dateline->format('Y-m-d')];
        })->unique()->values()->toArray();

        // Kelompokkan task per tanggal (untuk ditampilkan di modal)
        $taskMap = $taskDates->groupBy(function ($task) {
            return $task->start_date->format('Y-m-d');
        })->map(function ($group) {
            return $group->map(function ($task) {
                return [
                    'task' => $task->task,
                    'level' => $task->level,
                    'priority' => $task->priority,
                    'status' => $task->status,
                ];
            });
        });

        $stats = $this->getWorkCalendarStats($year, $month, $holidays);

        $tasks = Task::whereYear('start_date', $year)
            ->whereMonth('start_date', $month)
            ->get()
            ->groupBy(fn($task) => Carbon::parse($task->start_date)->format('Y-m-d'));

        $dailyProductivity = $this->calculateDailyProductivity($tasks->toArray());
        $weeklyProductivity = $this->calculateWeeklyProductivity($dailyProductivity);
        $monthlyProductivity = $this->calculateMonthlyProductivity($dailyProductivity, $year, $month);


        return view('calendar_result', compact(
            'month',
            'year',
            'highlightDates',
            'taskMap',
            'stats',
            'holidays',
            'dailyProductivity',
            'weeklyProductivity',
            'monthlyProductivity'
        ));
    }



    function getWorkCalendarStats($year, $month, $holidayList = [])
    {
        $start = Carbon::createFromDate($year, $month, 1)->startOfWeek(Carbon::MONDAY);
        $end = Carbon::createFromDate($year, $month)->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $weeks = [];

        // Loop tanggal dari Senin pertama sampai akhir minggu di bulan tsb
        $period = CarbonPeriod::create($start, $end);
        foreach ($period as $date) {
            $weekIndex = $date->copy()->startOfWeek()->format('W');

            // Masukkan hanya hari kerja (Mon-Fri) DAN tanggal itu ada di bulan yang diminta
            // if ($date->dayOfWeekIso <= 5 && $date->month == $month) {
            if (
                $date->dayOfWeekIso <= 5 &&
                $date->month == $month &&
                !in_array($date->toDateString(), $holidayList)
            )
                $weeks[$weekIndex][] = $date->toDateString();
        }


        // Hanya minggu yang punya minimal 1 hari kerja di bulan tersebut
        $activeWeeks = array_filter($weeks, fn($days) => count($days) > 0);


        return [
            'total_active_weeks' => count($activeWeeks),
            'active_days_per_week' => array_map('count', $activeWeeks),
            'detail' => $activeWeeks,
        ];
    }

    function calculateDailyProductivity(array $tasksByDate)
    {
        $result = [];

        foreach ($tasksByDate as $date => $tasks) {
            $doneCount = collect($tasks)->where('status', 'done')->count();

            $productivity = min(100, ($doneCount / 3) * 100);
            $result[$date] = round($productivity, 2);
        }

        return $result; // [ '2025-06-02' => 66.67, '2025-06-03' => 100, ... ]
    }

    function calculateWeeklyProductivity(array $dailyProductivity)
    {
        $weekly = [];

        foreach ($dailyProductivity as $date => $percent) {
            $week = Carbon::parse($date)->startOfWeek()->format('W');

            if (!isset($weekly[$week])) $weekly[$week] = [];

            $weekly[$week][] = $percent;
        }

        // hitung rata-rata per minggu
        $weeklyResult = [];
        foreach ($weekly as $week => $values) {
            $weeklyResult[$week] = round(array_sum($values) / count($values), 2);
        }

        return $weeklyResult; // [ '22' => 88.3, '23' => 100, ... ]
    }

    function calculateMonthlyProductivity(array $dailyProductivity, $year, $month)
    {
        $filtered = array_filter($dailyProductivity, function ($date) use ($year, $month) {
            return Carbon::parse($date)->year == $year && Carbon::parse($date)->month == $month;
        }, ARRAY_FILTER_USE_KEY);

        if (count($filtered) === 0) return 0;

        return round(array_sum($filtered) / count($filtered), 2); // rata-rata semua hari aktif
    }
}
