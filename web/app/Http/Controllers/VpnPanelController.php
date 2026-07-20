<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VpnPanelController extends Controller
{
    private const STATUS_LOG = '/var/log/openvpn/status.log';
    private const ADD_SCRIPT = '/opt/scripts/add-client.sh';
    private const REVOKE_SCRIPT = '/opt/scripts/revoke-client.sh';

    public function view(string $page)
    {
        return view("pages.{$page}");
    }

    public function handleApi(Request $request, string $action)
    {
        try {
            return match ($action) {
                'clients' => $this->getClients(),
                'add' => $this->addClient(trim($request->input('client', ''))),
                'revoke' => $this->revokeClient(trim($request->input('client', ''))),
                'console' => $this->runConsole(trim($request->input('cmd', ''))),
                'status' => $this->getServerStatus(),
                default => response()->json(['error' => 'Unknown'], 400),
            };
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function getClients()
    {
        $downloadDir = '/opt/vpn-panel/www/downloads/';
        $logFile = self::STATUS_LOG;
        $allClients = [];

        // 1. Читаем папку, чтобы найти ВСЕХ клиентов
        if (is_dir($downloadDir)) {
            $files = scandir($downloadDir);
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'ovpn') {
                    $name = pathinfo($file, PATHINFO_FILENAME);
                    $allClients[$name] = [
                        'name' => $name,
                        'status' => 'offline',
                        'real_ip' => '-',
                        'in' => '0 B',
                        'out' => '0 B',
                        'since' => '-',
                        'color' => 'bg-red-500'
                    ];
                }
            }
        }

        // 2. Читаем лог, чтобы обновить тех, кто онлайн
        $log = @file_get_contents($logFile) ?: '';
        $inClientList = false;

        foreach (explode("\n", $log) as $line) {
            $line = trim($line);
            if (str_contains($line, 'HEADER,CLIENT_LIST')) {
                $inClientList = true;
                continue;
            }
            if (str_contains($line, 'HEADER,ROUTING_TABLE') || str_contains($line, 'GLOBAL_STATS')) {
                $inClientList = false;
                continue;
            }

            if ($inClientList && str_starts_with($line, 'CLIENT_LIST,')) {
                $row = array_map('trim', explode(',', $line));
                if (count($row) >= 8) {
                    $clientName = $row[1];
                    if (isset($allClients[$clientName])) {
                        $allClients[$clientName]['status'] = 'connected';
                        $allClients[$clientName]['real_ip'] = explode(':', $row[2])[0];
                        $allClients[$clientName]['in'] = $this->fmt($row[5] ?? 0);
                        $allClients[$clientName]['out'] = $this->fmt($row[6] ?? 0);
                        $allClients[$clientName]['since'] = $row[7] ?? '-';
                        $allClients[$clientName]['color'] = 'bg-green-500';
                    }
                }
            }
        }

        // 3. СОРТИРОВКА: сначала онлайн (connected), потом оффлайн (offline)
        $clientsArray = array_values($allClients);
        usort($clientsArray, function ($a, $b) {
            if ($a['status'] === 'connected' && $b['status'] !== 'connected') return -1;
            if ($a['status'] !== 'connected' && $b['status'] === 'connected') return 1;
            return strcmp($a['name'], $b['name']); // внутри групп — по алфавиту
        });

        return response()->json(['clients' => $clientsArray]);
    }

    private function addClient($n)
    {
        if (!$n) throw new \Exception('Имя пустое');
        $o = shell_exec(escapeshellcmd(self::ADD_SCRIPT) . " " . escapeshellarg($n));
        return response()->json(['output' => trim($o)]);
    }

    private function revokeClient($n)
    {
        if (!$n) {
            throw new \Exception('Имя пустое');
        }

        $o = shell_exec(
            "sudo " .
            escapeshellcmd(self::REVOKE_SCRIPT) .
            " " .
            escapeshellarg($n) .
            " 2>&1"
        );

        $lines = array_filter(explode("\n", trim($o)));
        $last = end($lines);

        return response()->json(['output' => $last]);
    }

    private function runConsole($c)
    {
        if (!$c) throw new \Exception('Команда пустая');
        $o = shell_exec(escapeshellcmd($c) . ' 2>&1');
        return response()->json(['output' => $o ?: 'Готово']);
    }

    private function getServerStatus()
    {
        $o = shell_exec('uptime && free -m && df -h /');
        return response()->json(['output' => $o]);
    }

    private function fmt($b)
    {
        $u = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($b >= 1024 && $i < 3) {
            $b /= 1024;
            $i++;
        }
        return round($b, 1) . ' ' . $u[$i];
    }
}
