<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;

class VpnPanelController extends Controller
{
    private const STATUS_LOG = '/var/log/openvpn/status.log';
    private const ADD_SCRIPT = '/opt/scripts/add-client.sh';
    private const REVOKE_SCRIPT = '/opt/scripts/revoke-client.sh';

    public function view(string $page) { return view("pages.{$page}"); }

    public function handleApi(Request $request, string $action)
    {
        try {
            return match($action) {
                'clients' => $this->getClients(),
                'add' => $this->addClient(trim($request->input('client',''))),
                'revoke' => $this->revokeClient(trim($request->input('client',''))),
                'console' => $this->runConsole(trim($request->input('cmd',''))),
                'status' => $this->getServerStatus(),
                default => response()->json(['error'=>'Unknown action'], 400),
            };
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

private function getClients()
{
    $log = @file_get_contents(self::STATUS_LOG) ?: '';
    $connected = [];
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
                $connected[] = [
                    'name'      => $row[1] ?: 'Unknown',
                    'real_ip'   => explode(':', $row[2])[0],
                    'in'        => $this->fmt($row[5] ?? 0),
                    'out'       => $this->fmt($row[6] ?? 0),
                    'since'     => $row[7] ?? '',
                    'status'    => 'connected',
                    'color'     => 'bg-green-500'
                ];
            }
        }
    }

    // Сортируем: подключённые сверху
    usort($connected, function($a, $b) {
        return strcmp($a['name'], $b['name']);
    });

    return response()->json([
        'clients' => $connected   // пока только подключённые
    ]);
}
    private function addClient($n) {
        if (!$n) throw new \Exception('Имя клиента пустое');
        $out = shell_exec("sudo " . escapeshellcmd(self::ADD_SCRIPT) . " " . escapeshellarg($n) . " 2>&1");
        return response()->json(['output' => trim($out)]);
    }

    private function revokeClient($n) {
        if (!$n) throw new \Exception('Имя клиента пустое');
        $out = shell_exec("sudo " . escapeshellcmd(self::REVOKE_SCRIPT) . " " . escapeshellarg($n) . " 2>&1");
        return response()->json(['output' => trim($out)]);
    }

    private function runConsole($c) {
        if (!$c) throw new \Exception('Команда пустая');
        $out = shell_exec(escapeshellcmd($c) . " 2>&1");
        return response()->json(['output' => $out ?: 'Выполнено']);
    }

    private function getServerStatus() {
        $out = shell_exec("uptime && free -m && df -h / 2>&1");
        return response()->json(['output' => $out]);
    }

    private function fmt($bytes) {
        $units = ['B','KB','MB','GB']; $i = 0;
        while ($bytes >= 1024 && $i < 3) { $bytes /= 1024; $i++; }
        return round($bytes, 1) . ' ' . $units[$i];
    }
}
