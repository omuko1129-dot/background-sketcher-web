<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('3D地球儀マップ') }}
        </h2>
    </x-slot>

    <!-- Cesium の CSS -->
    <link href="https://cesium.com/downloads/cesiumjs/releases/1.114/Build/Cesium/Widgets/widgets.css" rel="stylesheet">

    <style>
        #cesiumContainer {
            width: 100%;
            height: 700px;
            margin: 0;
            padding: 0;
            overflow: hidden;
            border-radius: 8px;
        }
    </style>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div id="cesiumContainer"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cesium の JS -->
    <script src="https://cesium.com/downloads/cesiumjs/releases/1.114/Build/Cesium/Cesium.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            console.log("Cesium initialization starting...");

            try {
                // Cesium ビューアの初期化
                const viewer = new Cesium.Viewer('cesiumContainer', {
                    animation: false,
                    timeline: false,
                    geocoder: false,
                    homeButton: false,
                    sceneModePicker: false,
                    baseLayerPicker: false,
                    imageryProvider: new Cesium.OpenStreetMapImageryProvider({
                        url: 'https://tile.openstreetmap.org/'
                    }),
                    terrainProvider: new Cesium.EllipsoidTerrainProvider()
                });

                // API から地点データを取得
                fetch('/api/locations')
                    .then(response => response.text()) // まずテキストとして取得
                    .then(rawText => {
                        // 先頭の不穏な '<' 文字を安全に取り除く
                        let cleanText = rawText.trim();
                        if (cleanText.startsWith('<')) {
                            cleanText = cleanText.substring(1);
                        }

                        const data = JSON.parse(cleanText);
                        console.log("Locations loaded successfully:", data);

                        if (!Array.isArray(data)) {
                            console.error("Data is not an array:", data);
                            return;
                        }

                        data.forEach(location => {
                            viewer.entities.add({
                                name: location.title,
                                position: Cesium.Cartesian3.fromDegrees(Number(location.longitude), Number(location.latitude)),
                                point: {
                                    pixelSize: 14,
                                    color: Cesium.Color.RED,
                                    outlineColor: Cesium.Color.WHITE,
                                    outlineWidth: 2
                                },
                                description: `<div><h3>${location.title}</h3><p>${location.description ?? ''}</p></div>`
                            });
                        });

                        // 最初の地点へカメラを移動
                        if (data.length > 0) {
                            viewer.camera.flyTo({
                                destination: Cesium.Cartesian3.fromDegrees(Number(data[0].longitude), Number(data[0].latitude), 150000),
                                duration: 1
                            });
                        }
                    })
                    .catch(error => {
                        console.error("Fetch Error:", error);
                    });

            } catch (e) {
                console.error("Cesium Viewer Critical Error:", e);
            }
        });
    </script>
</x-app-layout>