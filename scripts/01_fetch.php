<?php
$basePath = dirname(__DIR__);
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
$json = json_decode(file_get_contents('https://fhy.wra.gov.tw/Api/v2/Disaster/Flooding?$format=JSON'), true);
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

            if ($case['SourceCode'] === 7 || $case['IsReceded'] !== true) {
                $fc['features'] = [
                    'type' => 'Feature',
                    'properties' => [
                        'authority' => '水利署防災資訊服務網',
                        'stationID' => $case['DisasterFloodingID'],
                        'stationName' => $case['CaseNo'],
                        'unitOfMeasurement' => 'cm',
                        'result' => $case['Depth'],
                        'phenomenonTime' => $case['Time'],
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

file_put_contents($basePath . '/docs/json/fhy.json', json_encode($fc, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
