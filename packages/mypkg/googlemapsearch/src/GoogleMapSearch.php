<?php

namespace Mypkg\GoogleMapSearch;

use Illuminate\Support\Facades\Http;

class GoogleMapSearch
{
    public function GetAddress($apiKey, $input) {
        $pattern = '/^[a-zA-Z0-9=+-]+$/';
        if ($apiKey == "" || !preg_match($pattern, $apiKey)) {
            return "Non-validable Google API key";
        }

        $pattern = "/^[0-9a-zA-Z\p{Han}]+$/u";
        if ($input == "" || !preg_match($pattern, $input)) {
            return "Non-validable address";
        }

        $apiURL = "https://maps.googleapis.com/maps/api/place/findplacefromtext/json";
        $client = new \GuzzleHttp\Client();
        $response = Http::get($apiURL, [
                'input' => $input,
                'inputtype' => 'textquery',
                'fields' => 'geometry',
                'key' => $apiKey
            ]);
        unset($client);
        return $response;
    }
}