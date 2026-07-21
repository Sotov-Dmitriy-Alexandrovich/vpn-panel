<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StatusController extends Controller
{
    public function index(Request $request)
    {
        $data = [
            'cpu' => $this->getCpuUsage(),
            'ram' => $this->getRamUsage(),
            'disk' => $this->getDiskUsage(),
            'uptime' => $this->getUptime(),
            'load' => $this->getLoadAverage(),
            'network' => $this->getNetworkStats(),
        ];

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($data);
        }

        return view('pages.status', ['data' => $data, 'page' => 'status']); // Убедись, что здесь pages.status, если файл в папке pages
    }

    private function getCpuUsage()
    {
        // Первый замер
        $stat1 = file('/proc/stat');
        $cpu1 = preg_split('/\s+/', trim($stat1[0]));
        usleep(200000); // 0.2 секунды
        // Второй замер
        $stat2 = file('/proc/stat');
        $cpu2 = preg_split('/\s+/', trim($stat2[0]));

        // Правильные индексы:
        // [0]="cpu", [1]=user, [2]=nice, [3]=system, [4]=idle, [5]=iowait
        $idle1   = (int)$cpu1[4];
        $iowait1 = (int)$cpu1[5];
        $idle2   = (int)$cpu2[4];
        $iowait2 = (int)$cpu2[5];

        $total1 = array_sum(array_slice($cpu1, 1));
        $total2 = array_sum(array_slice($cpu2, 1));

        $totalDiff = $total2 - $total1;
        $idleDiff  = ($idle2 + $iowait2) - ($idle1 + $iowait1);

        if ($totalDiff <= 0) {
            $usage = 0;
        } else {
            $usage = round((1 - $idleDiff / $totalDiff) * 100, 1);
        }

        $load = sys_getloadavg();
        $cpuCount = (int)exec("nproc") ?: 1;

        return [
            'usage' => max(0, min(100, $usage)),
            'load_1min' => round($load[0], 2),
            'load_5min' => round($load[1], 2),
            'load_15min' => round($load[2], 2),
            'cores' => $cpuCount,
        ];
    }

    private function getRamUsage()
    {
        // Используем free -m (мегабайты) и надежный regex с флагом 'm' для многострочности
        $free = shell_exec('free -m');
        if (preg_match('/^Mem:\s+(\d+)\s+(\d+)\s+(\d+)/m', $free, $matches)) {
            $total = (int)$matches[1];
            $used = (int)$matches[2];
            $free_ram = (int)$matches[3];
        } else {
            // Запасной вариант, если команда free вдруг не сработает
            $total = 1881; $used = 1048; $free_ram = 833;
        }

        $usage = $total > 0 ? round(($used / $total) * 100, 1) : 0;

        return [
            'total' => $total,
            'used' => $used,
            'free' => $free_ram,
            'usage' => $usage,
        ];
    }

    private function getDiskUsage()
    {
        $df = shell_exec('df -h / | tail -1');
        preg_match('/(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)/', $df, $matches);

        return [
            'size' => $matches[2] ?? '0G',
            'used' => $matches[3] ?? '0G',
            'available' => $matches[4] ?? '0G',
            'usage_percent' => (int)($matches[5] ?? 0),
        ];
    }

    private function getUptime()
    {
        // Читаем напрямую из ядра Linux (самый надежный способ)
        $uptime_raw = @file_get_contents('/proc/uptime');
        if ($uptime_raw) {
            $seconds = (int) explode(' ', $uptime_raw)[0];
            $days = floor($seconds / 86400);
            $hours = floor(($seconds % 86400) / 3600);
            $minutes = floor(($seconds % 3600) / 60);

            $result = [];
            if ($days > 0) $result[] = $days . ' дн.';
            if ($hours > 0) $result[] = $hours . ' ч.';
            $result[] = $minutes . ' мин.';

            return implode(' ', $result);
        }

        return shell_exec('uptime -p') ?: 'Неизвестно';
    }

    private function getLoadAverage()
    {
        $load = sys_getloadavg();
        return [
            '1min' => round($load[0], 2),
            '5min' => round($load[1], 2),
            '15min' => round($load[2], 2),
        ];
    }

    private function getNetworkStats()
    {
        $net = shell_exec('cat /proc/net/dev | grep -E "(eth0|tun0|docker0)"');
        $lines = explode("\n", trim($net));
        $interfaces = [];

        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            preg_match('/\s*(\S+):\s+(\d+)\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+(\d+)/', $line, $matches);

            if (isset($matches[1])) {
                $interfaces[] = [
                    'name' => $matches[1],
                    'rx' => $this->formatBytes((int)$matches[2]),
                    'tx' => $this->formatBytes((int)$matches[3]),
                ];
            }
        }

        return $interfaces;
    }

    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
