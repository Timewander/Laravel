<?php

namespace App\Service;

class Builder {

    private $airports = null;
    private $GPS = null;

    public function airports() {

        $path = base_path("resources/assets/js/base");
        $key = "airportlist";
        $resource = "$path/$key.js";
        $file = "$path/$key.php";
        if (!file_exists($file)) {
            $content = file_get_contents($resource);
            $map = [
                "var citiesData" => '<?php $airports',
                "in:" => '"in"=>',
                "out:" => '"out"=>',
                "{" => "[",
                "}" => "]",
            ];
            foreach ($map as $from => $to) {
                $content = str_replace($from, $to, $content);
            }
            file_put_contents($file, $content);
        }
        if (is_null($this->airports)) {
            include $file;
            if (isset($airports)) {
                $in = $out = [];
                foreach ($airports["in"] as $airport) {
                    list($code, $name, $name_en) = $airport;
                    $in[$code] = ["zh" => $name, "en" => $name_en];
                }
                foreach ($airports["out"] as $airport) {
                    list($code, $name, $name_en) = $airport;
                    $out[$code] = ["zh" => $name, "en" => $name_en];
                }
                $this->airports = ["in" => $in, "out" => $out];
            }
        }
        $this->appendAirportsGPS();
        return $this->airports;
    }

    private function appendAirportsGPS() {

        $path = base_path("resources/assets/js/base");
        $key = "airportGPS";
        $resource = "$path/$key.js";
        $file = "$path/$key.php";
        if (!file_exists($file)) {
            $content = file_get_contents($resource);
            $pieces = explode(":", $content);
            for ($key = 0;$key < count($pieces) - 1;$key ++) {
                $pieces[$key] = substr($pieces[$key], 0, -3) . "\"" . substr($pieces[$key], -3) . "\"";
            }
            $map = [
                "var geoCoordMap" => '<?php $airportsGPS',
                "{" => "[",
                "}" => "]",
            ];
            foreach ($map as $from => $to) {
                $pieces[0] = str_replace($from, $to, $pieces[0]);
                $pieces[$key] = str_replace($from, $to, $pieces[$key]);
            }
            $content = join("=>", $pieces);
            file_put_contents($file, $content);
        }
        if (is_null($this->GPS)) {
            include $file;
            if (isset($airportsGPS)) {
                $this->GPS = $airportsGPS;
            }
        }
        foreach ($this->GPS as $code => $data) {
            $area = isset($this->airports["in"][$code]) ? "in" : "out";
            $this->airports[$area][$code]["lng"] = $data[0];
            $this->airports[$area][$code]["lat"] = $data[1];
            $this->airports[$area][$code]["stat"] = $data[4];
            $this->airports[$area][$code]["city"] = $data[5];
        }
    }
}