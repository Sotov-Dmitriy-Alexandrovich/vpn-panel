<?php

namespace App\Services;

class OpenVPNService
{
    private const STATUS_LOG = "/var/log/openvpn/status.log";
    private const INDEX_FILE = "/etc/openvpn/server/easy-rsa/pki/index.txt";


    public function getClients()
    {
        $online = $this->getOnlineClients();

        $clients = [];


        if (!file_exists(self::INDEX_FILE)) {
            return [];
        }


        foreach (file(self::INDEX_FILE) as $line) {

            $line = trim($line);


            // только действующие сертификаты
            if (!str_starts_with($line, "V")) {
                continue;
            }


            if (!preg_match('/\/CN=([^\/\s]+)/', $line, $m)) {
                continue;
            }


            $name = $m[1];


            // убираем сертификат сервера
            if ($name === "server") {
                continue;
            }



            $clients[] = [

                "name" => $name,


                "online" => isset($online[$name]),


                "status" => isset($online[$name])
                    ? "connected"
                    : "offline",


                "color" => isset($online[$name])
                    ? "green"
                    : "red",


                "real_ip" =>
                    $online[$name]["real_ip"] ?? "-",


                "virtual_ip" =>
                    $online[$name]["virtual_ip"] ?? "-",


                "in" =>
                    $online[$name]["in"] ?? "0 B",


                "out" =>
                    $online[$name]["out"] ?? "0 B",

                "since" =>
                    isset($online[$name]["since"])
                    ? date("d.m.Y H:i:s", $online[$name]["since"])
                    : "-"            ];

        }


usort($clients, function($a, $b){

    // онлайн выше
    if ($a['online'] == $b['online']) {
        return 0;
    }

    return $a['online'] ? -1 : 1;

});


return $clients;    }




    private function getOnlineClients()
    {

        $result = [];


        if (!file_exists(self::STATUS_LOG)) {
            return [];
        }


        foreach(file(self::STATUS_LOG) as $line)
        {

            $line = trim($line);


            if (!str_starts_with($line,"CLIENT_LIST")) {
                continue;
            }



            $p = explode(",", $line);



            if(count($p) < 9) {
                continue;
            }



            $name = $p[1];



            $result[$name] = [

                "real_ip" =>
                    $p[2],


                "virtual_ip" =>
                    $p[3],


                "in" =>
                    $this->bytes($p[6]),


                "out" =>
                    $this->bytes($p[7]),


                "since" =>
                    $p[8]
            ];

        }


        return $result;
    }





    private function bytes($bytes)
    {

        $bytes=(int)$bytes;


        $units=[
            "B",
            "KB",
            "MB",
            "GB"
        ];


        $i=0;


        while(
            $bytes>=1024 &&
            $i<count($units)-1
        ){
            $bytes/=1024;
            $i++;
        }


        return round($bytes,1)." ".$units[$i];

    }

}
