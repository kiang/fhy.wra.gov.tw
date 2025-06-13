<?php
$basePath = dirname(__DIR__);

// Create SSL context to handle HTTPS requests
$context = stream_context_create([
    'http' => [
        'timeout' => 30,
        'user_agent' => 'Mozilla/5.0 (compatible; flood-monitoring-bot/1.0)',
    ],
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true,
    ]
]);
/**
 * {
    "DisasterFloodingID": "6fbfca5e-4a2d-4f82-b039-1a9f7e851da7",
    "Time": "2024-07-30T20:30:04",
    "CategoryCode": 24,
    "SourceCode": 7,
        7: 自動淹水感測器
    "CaseNo": "中96線華豐五街72號前岔路口淹水深度",
    "OperatorName": "第三河川分署",
    "TownCode": "6601900",
        6601900 = 彰化縣二水鄉
    "Situation": "目前感測值0公分", //災情描述
    "Location": "中96線華豐五街72號前岔路口",
    "Point": {
        "Latitude": 24.21939,
        "Longitude": 120.80483
    },
    "Depth": 11,
    "Treatment": "",
    "IsReceded": true,
        true: 已退水
    "RecededDate": "2024-07-30T20:53:04",
    "Type": 1,
    "Photo": []
}
 */

// Load station information
$stationsFile = $basePath . '/raw/iot_stations.json';
$stationInfo = [];
if (file_exists($stationsFile)) {
    $stations = json_decode(file_get_contents($stationsFile), true);
    if ($stations) {
        foreach ($stations as $station) {
            $stationInfo[$station['SensorUUID']] = $station;
        }
    }
}

$json = json_decode(file_get_contents('https://fhy.wra.gov.tw/Api/v2/Disaster/Flooding?$format=JSON', false, $context), true);
$fc = [
    'type' => 'FeatureCollection',
    'features' => [],
];
if (!empty($json['Data'])) {
    foreach ($json['Data'] as $city) {
        foreach ($city['DisasterFlooding'] as $case) {
            $caseTime = strtotime($case['Time']);
            $casePath = $basePath . '/raw/' . date('Y/m', $caseTime);
            if (!is_dir($casePath)) {
                mkdir($casePath, 0777, true);
            }
            file_put_contents($casePath . '/' . $case['DisasterFloodingID'] . '.json', json_encode($case, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

            if ($case['IsReceded'] == false) {
                $fc['features'][] = [
                    'type' => 'Feature',
                    'properties' => [
                        'authority' => $case['OperatorName'],
                        'stationID' => $case['DisasterFloodingID'],
                        'stationName' => $case['CaseNo'],
                        'location' => $case['Location'],
                        'situation' => $case['Situation'],
                        'unitOfMeasurement' => 'cm',
                        'result' => $case['Depth'],
                        'phenomenonTime' => $case['Time'],
                        'townCode' => $case['TownCode'],
                        'categoryCode' => $case['CategoryCode'],
                        'sourceCode' => $case['SourceCode'],
                        'dataSource' => 'Disaster-Report'
                    ],
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [
                            $case['Point']['Longitude'],
                            $case['Point']['Latitude'],
                        ],
                    ],
                ];
            }
        }
    }
}

// Fetch IoT flood sensor real-time data
$iotJson = json_decode(file_get_contents('https://fhyv.wra.gov.tw/FhyWeb/v1/Api/FloodSensor/RealTimeInfo?$format=JSON', false, $context), true);
if (!empty($iotJson)) {
    foreach ($iotJson as $sensor) {
        // Only include sensors with recent data (not too old) and non-zero depth
        $sourceTime = strtotime($sensor['SourceTime']);
        $isRecent = (time() - $sourceTime) < (24 * 60 * 60); // within 24 hours
        
        // Include if depth > 0 or if it's a recent reading (within 24 hours)
        if ($sensor['Depth'] > 0 || $isRecent) {
            $stationData = isset($stationInfo[$sensor['SensorUUID']]) ? $stationInfo[$sensor['SensorUUID']] : null;
            
            // If station not found in local file, try to fetch from API
            if (!$stationData) {
                $stationApiUrl = 'https://fhyv.wra.gov.tw/FhyWeb/v1/Api/FloodSensor/Station?$format=JSON';
                $tempStations = json_decode(file_get_contents($stationApiUrl, false, $context), true);
                if ($tempStations) {
                    foreach ($tempStations as $station) {
                        if ($station['SensorUUID'] === $sensor['SensorUUID']) {
                            $stationData = $station;
                            // Update local station info
                            $stationInfo[$sensor['SensorUUID']] = $station;
                            break;
                        }
                    }
                    // Update the stations file with new data
                    file_put_contents($stationsFile, json_encode(array_values($stationInfo), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                }
            }
            
            if ($stationData) {
                // API returns Taiwan time but incorrectly marked as +00:00, so we parse the time value as Taiwan time
                $sourceTimeStr = preg_replace('/\+00:00$|Z$/', '', $sensor['SourceTime']);
                $transferTimeStr = preg_replace('/\+00:00$|Z$/', '', $sensor['TransferTime']);
                $sourceTime = new DateTime($sourceTimeStr, new DateTimeZone('Asia/Taipei'));
                $transferTime = new DateTime($transferTimeStr, new DateTimeZone('Asia/Taipei'));
                
                $fc['features'][] = [
                    'type' => 'Feature',
                    'properties' => [
                        'authority' => isset($stationData['Operator']) ? 'Operator-' . $stationData['Operator'] : 'Unknown',
                        'stationID' => $sensor['SensorUUID'],
                        'stationName' => $stationData['SensorName'],
                        'address' => $stationData['Address'],
                        'sensorType' => $stationData['SensorType'],
                        'unitOfMeasurement' => 'cm',
                        'result' => $sensor['Depth'],
                        'phenomenonTime' => $sourceTime->format('c'),
                        'transferTime' => $transferTime->format('c'),
                        'toBeConfirm' => $sensor['ToBeConfirm'],
                        'isCulvert' => $stationData['IsCulvert'],
                        'cityCode' => $stationData['CityCode'],
                        'townCode' => $stationData['TownCode'],
                        'dataSource' => 'IoT-Sensor'
                    ],
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [
                            $stationData['Point']['Longitude'],
                            $stationData['Point']['Latitude'],
                        ],
                    ],
                ];
            }
        }
    }
}

file_put_contents($basePath . '/docs/json/fhy.json', json_encode($fc, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
