# 臺灣淹水地圖 (Taiwan Flooding Map)

This project collects real-time data from flooding sensors across Taiwan and visualizes it on an interactive map.

## Live Demo

The live map is available at: [https://kiang.github.io/flooding/](https://kiang.github.io/flooding/)

## Data Source

The flooding data is sourced from the Water Resources Agency (WRA) of Taiwan's Ministry of Economic Affairs:
- Source: [https://fhy.wra.gov.tw/fhyv2/](https://fhy.wra.gov.tw/fhyv2/)
- The data is released under a CC-BY license (Creative Commons Attribution)

## Features

- Real-time visualization of flooding data from sensors across Taiwan
- Display of flooding depth measurements in centimeters
- Information about flooding locations, including coordinates and timestamps
- Option to show only active flooding points or all historical data
- Calculation and display of flooding points

## Technical Implementation

The project consists of:
- PHP scripts to fetch and process data from the WRA API
- GeoJSON data formatting for map visualization
- Web interface with interactive map display

## Data Structure

Each flooding data point includes:
- Location (latitude/longitude)
- Flooding depth (in cm)
- Timestamp
- Station information
- Authority responsible
- Status (receded or active)

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Data License

The flooding data is provided by the Water Resources Agency (WRA) of Taiwan under a CC-BY license. When using this data, please provide appropriate attribution to the WRA.

## Author

Created by 江明宗 (Finjon Kiang)

## Contributing

Contributions to improve the project are welcome. Please feel free to submit issues or pull requests. 