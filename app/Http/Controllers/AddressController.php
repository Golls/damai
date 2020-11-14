<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Address as mAddress; // 地址資料表的模組
use Mypkg\GoogleMapSearch\GoogleMapSearch; // 具有 GetAddress() 函式的 library

class AddressController extends Controller
{
    /**
     * 建構子預設不使用 Google MAP API。
     *
     * @return void
     */
    function __construct() {
        $this->withGoogle = false;
    }

    /**
     * 依郵遞區號寫入地址資料。
     *
     * @return string
     */
    public function importByZip($zip, $mode = "") {
        if (strlen($zip) != 3 && is_numeric($zip)) {
            return "郵遞區號格式有誤";
        }
        $this->withGoogle = (isset($mode) && $mode == strtolower("GM")) ? true : false;

        $city = '';
        $areas = [];
        $citiesArray = $this->createCitiesArray();
        if (!is_array($citiesArray)) {
            return "生成城市資料錯誤";
        }

        $this->getCityandAreas($citiesArray, $zip, $city, $areas);
        if ($city == "" || count($areas) == 0) {
            return "找不到郵遞對應的區域資料";
        }
        foreach ($areas as $key => $value) {
            $area = $areas[$key];
            $roads = $this->createRoads($area['filename']);
            $this->insertAddressData($city, $zip, $area['area'], $area['filename'], $roads);
        }
        return "寫入郵遞區域 ".$zip." 資料結束";
    }

    /**
     * 寫入全部郵遞區號資料。
     *
     * @return string
     */
    public function importAll($mode = "") {
        $this->withGoogle = (isset($mode) && $mode == strtolower("GM")) ? true : false;
        $citiesArray = $this->createCitiesArray();
        if (!is_array($citiesArray)) {
            return "生成城市資料錯誤";
        }

        foreach ($citiesArray as $key => $value) {
            $city = $citiesArray[$key]['city'];
            $regions = $citiesArray[$key]['data'];
            $this->loopImport($city, $regions);
        }
        return "寫入全部郵遞區域資料結束";
    }

    /**
     * 將 0.json 檔案轉換成對應各城市資料的陣列。
     *
     * @return array
     */
    private function createCitiesArray() {
        $citiesJSONFile = 'app/public/address/0/0.json';
        $citiesArray = $this->jsonToArray($citiesJSONFile);
        return $citiesArray;
    }

    /**
     * 使用參考傳遞取得對應某郵遞區域的城市與區域資料。
     *
     * @return void
     */
    private function getCityandAreas($citiesArray, $zip, &$city, &$areas) {
        foreach ($citiesArray as $key1 => $val1) {
            $data = $citiesArray[$key1]['data'];
            $endLoop = false;
            foreach ($data as $key2 => $val2) {
                if ($data[$key2]['zip'] == $zip) {
                    $city = $citiesArray[$key1]['city'];
                    $areas[] = $data[$key2];
                }

                if (count($areas) > 0 && $data[$key2]['zip'] != $zip) {
                    $endLoop = true;
                    break;
                }
            }

            if ($endLoop == true) {
                break;
            }
        }
    }

    /**
     * 將對應某地區的 JSON 檔案內容轉換成反應出各街道的陣列。
     *
     * @return array
     */
    private function createRoads($fileName) {
        $subdir = substr($fileName, 0, 1);
        $roadsJSONFile = 'app/public/address/'.$subdir.'/'.$fileName.'.json';
        $roads = $this->jsonToArray($roadsJSONFile);
        return $roads;
    }

    /**
     * 找出某地址是否有可切分出的路、街、巷、弄、號、樓等資料
     *
     * @return void
     */
    private function subDiv($address, &$road, &$lane, &$alley, &$no, &$floor) {
        $i = 0;
        $word = '';
        while ($i < mb_strlen($address)) {
            $char = mb_substr($address, $i, 1);
            $word .= $char;
            if ($char == "路" || $char == "街") {
                $road .= $word;
                $word = '';
            }

            if ($char == "巷") {
                $lane .= $word;
                $word = '';
            }

            if ($char == "弄") {
                $lane .= $word;
                $word = '';
            }

            if ($char == "號") {
                $no .= $word;
                $word = '';
            }

            if ($char == "樓") {
                $floor .= $word;
                $word = '';
            }
            $i++;
        }
    }

    /**
     * 持續寫入全部郵遞區號資料流程。
     *
     * @return void
     */
    private function loopImport($city, $regions) {
        foreach ($regions as $key => $value) {
            $zip = $regions[$key]['zip'];
            $area = $regions[$key]['area'];
            $fileName = $regions[$key]['filename'];
            $roads = $this->createRoads($fileName);
            $this->insertAddressData($city, $zip, $area, $fileName, $roads);
        }
    }

    /**
     * 將地址內容寫入資料庫裡，並確認是否要透過 Google MAP API 來取得經緯度。
     *
     * @return void
     */
    private function insertAddressData($city, $zip, $area, $fileName, $roads) {
        $model = new mAddress;
        $dataSet = [];
        $road = '';
        $lane = '';
        $alley = '';
        $no = '';
        $floor = '';

        if ($this->withGoogle == true) {
            $gm = new GoogleMapSearch();
            $googleApiKey = env('GOOGLE_API_KEY');
        }

        foreach ($roads as $key => $value) {
            $address = $roads[$key]['name'];
            $fullAddress = $city.$area.$roads[$key]['name'];
            $road = '';
            $lane = '';
            $alley = '';
            $no = '';
            $floor = '';
            $this->subDiv($address, $road, $lane, $alley, $no, $floor);
            
            $model->zip = $zip;
            $model->city = $city;
            $model->area = $area;
            $model->road = $road;
            $model->lane = $lane;
            $model->alley = $alley;
            $model->no = $no;
            $model->floor = $floor;
            $model->address = $address;
            $model->full_address = $fullAddress;
            $model->filename = $fileName;
            if ($this->withGoogle == true) {
                $json = $gm->GetAddress($googleApiKey, $fullAddress);
                $result = json_decode($json, true);
                $model->latitude = isset($result['candidates'][0]['geometry']['location']['lat']) ? $result['candidates'][0]['geometry']['location']['lat'] : 0;
                $model->lontitue = isset($result['candidates'][0]['geometry']['location']['lng']) ? $result['candidates'][0]['geometry']['location']['lng'] : 0;
                $model->full_address = isset($result['candidates'][0]['formatted_address']) ? $result['candidates'][0]['formatted_address'] : $model->full_address;
            }
            $dataSet[] = $model->attributesToArray();
        }
        mAddress::insert($dataSet);
    }

    /**
     * 將 JSON 格式檔案轉換成陣列。
     *
     * @return array
     */
    private function jsonToArray($file) {
        try {
            $jsonString = file_get_contents(storage_path($file), "r");
            $array = json_decode($jsonString, true);
            return $array;
        } catch (Exception $e) {
            return $e->message;
        }
    }
}
