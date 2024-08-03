<?php
$basePath = dirname(__DIR__);
//$json = '{"UpdataTime":"2024-07-30T21:46:23","Data":[{"CityCode":"10007","DisasterFlooding":[{"DisasterFloodingID":"5663fe2d-d604-44be-ad8e-d74e0d856204","Time":"2024-07-30T18:20:00","CategoryCode":1,"SourceCode":7,"CaseNo":"和平路","OperatorName":"第四河川分署","TownCode":"1000712","Situation":"目前感測值1公分","Location":"復興里和平路山腳路口","Point":{"Latitude":23.8692,"Longitude":120.6036},"Depth":28.0,"Treatment":"","IsReceded":true,"RecededDate":"2024-07-30T19:30:00","Type":1,"Photo":[]},{"DisasterFloodingID":"c4361a99-f43f-4cc2-88ca-2a595df08bad","Time":"2024-07-30T18:30:00","CategoryCode":1,"SourceCode":7,"CaseNo":"興霖路380巷","OperatorName":"第四河川分署","TownCode":"1000715","Situation":"目前感測值5公分","Location":"舊館村興霖路380巷","Point":{"Latitude":23.9579,"Longitude":120.5238},"Depth":14.0,"Treatment":"","IsReceded":true,"RecededDate":"2024-07-30T18:50:00","Type":1,"Photo":[]},{"DisasterFloodingID":"c9adf1b9-d50d-4097-90d8-8a34141240aa","Time":"2024-07-30T18:30:00","CategoryCode":1,"SourceCode":7,"CaseNo":"下崙排水","OperatorName":"第四河川分署","TownCode":"1000707","Situation":"目前感測值11公分","Location":"下崙村下崙排水民生街148巷","Point":{"Latitude":24.0007,"Longitude":120.5075},"Depth":20.0,"Treatment":"","IsReceded":false,"RecededDate":null,"Type":1,"Photo":[]},{"DisasterFloodingID":"0391cbaa-907b-469e-9830-ba5e548a9960","Time":"2024-07-30T18:30:00","CategoryCode":1,"SourceCode":7,"CaseNo":"瓦瑤排水","OperatorName":"第四河川分署","TownCode":"1000714","Situation":"目前感測值29公分","Location":"好修村瓦瑤排水","Point":{"Latitude":24.004,"Longitude":120.4612},"Depth":30.0,"Treatment":"","IsReceded":false,"RecededDate":null,"Type":1,"Photo":[]}]},{"CityCode":"66","DisasterFlooding":[{"DisasterFloodingID":"6fbfca5e-4a2d-4f82-b039-1a9f7e851da7","Time":"2024-07-30T20:30:04","CategoryCode":24,"SourceCode":7,"CaseNo":"中96線華豐五街72號前岔路口淹水深度","OperatorName":"第三河川分署","TownCode":"6601900","Situation":"目前感測值0公分","Location":"中96線華豐五街72號前岔路口","Point":{"Latitude":24.21939,"Longitude":120.80483},"Depth":11.0,"Treatment":"","IsReceded":true,"RecededDate":"2024-07-30T20:53:04","Type":1,"Photo":[]}]}]}';
$json = json_decode(file_get_contents('https://fhy.wra.gov.tw/Api/v2/Disaster/Flooding?$format=JSON'), true);
if (!empty($json['Data'])) {
    foreach ($json['Data'] as $city) {
        foreach ($city['DisasterFlooding'] as $case) {
            $caseTime = strtotime($case['Time']);
            $casePath = $basePath . '/raw/' . date('Y/m', $caseTime);
            if (!is_dir($casePath)) {
                mkdir($casePath, 0777, true);
            }
            file_put_contents($casePath . '/' . $case['DisasterFloodingID'] . '.json', json_encode($case, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }
    }
}
