<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { onMounted, reactive, ref } from 'vue';
import mapboxgl from 'mapbox-gl';
import 'mapbox-gl/dist/mapbox-gl.css';

const props = defineProps({
    savedAngles: {
        type: Array,
        default: () => []
    }
});

const mapContainer = ref(null);
let map = null;

// reactiveを使用し、プロパティ変更を確実にVueへ通知
const cameraState = reactive({
    zoom: 16.5,
    pitch: 60,
    bearing: -17.6,
    center: [139.7006, 35.6895]
});

// 入力フォーム用ステート
const isSaving = ref(false);
const angleName = ref('');

onMounted(() => {
    mapboxgl.accessToken = import.meta.env.VITE_MAPBOX_ACCESS_TOKEN || '';
    if (!mapContainer.value) return;

    map = new mapboxgl.Map({
        container: mapContainer.value,
        style: 'mapbox://styles/mapbox/light-v11',
        center: cameraState.center,
        zoom: cameraState.zoom,
        pitch: cameraState.pitch,
        bearing: cameraState.bearing,
        antialias: true,
        preserveDrawingBuffer: true
    });

    // カメラの状態をプロパティごとに直接更新
    const updateCameraState = () => {
        if (!map) return;
        const center = map.getCenter();
        
        cameraState.zoom = Number(map.getZoom().toFixed(2));
        cameraState.pitch = Number(map.getPitch().toFixed(1));
        cameraState.bearing = Number(map.getBearing().toFixed(1));
        cameraState.center = [Number(center.lng.toFixed(5)), Number(center.lat.toFixed(5))];
    };

    // 移動・ズーム・角度変更・回転の全イベントにリスナーを登録
    map.on('move', updateCameraState);
    map.on('zoom', updateCameraState);
    map.on('pitch', updateCameraState);
    map.on('rotate', updateCameraState);

    map.on('load', () => {
        const layers = map.getStyle().layers;
        const labelLayerId = layers.find(
            (layer) => layer.type === 'symbol' && layer.layout && layer.layout['text-field']
        )?.id;

        map.addLayer(
            {
                'id': 'add-3d-buildings',
                'source': 'composite',
                'source-layer': 'building',
                'filter': ['==', 'extrude', 'true'],
                'type': 'fill-extrusion',
                'minzoom': 15,
                'paint': {
                    'fill-extrusion-color': '#aaa',
                    'fill-extrusion-height': ['get', 'height'],
                    'fill-extrusion-base': ['get', 'min_height'],
                    'fill-extrusion-opacity': 0.8
                }
            },
            labelLayerId
        );
    });
});

// 保存モードの開始
const startSave = () => {
    angleName.value = `構図 ${props.savedAngles.length + 1}`;
    isSaving.value = true;
};

// DBへの保存実行
const executeSave = () => {
    if (!angleName.value.trim()) return;

    router.post('/camera-angles', {
        name: angleName.value,
        zoom: cameraState.zoom,
        pitch: cameraState.pitch,
        bearing: cameraState.bearing,
        center: cameraState.center
    }, {
        onSuccess: () => {
            isSaving.value = false;
            angleName.value = '';
        }
    });
};

// DBからアングル削除
const deleteAngle = (id) => {
    router.delete(`/camera-angles/${id}`);
};

// カメラ移動
const applyAngle = (angle) => {
    if (!map) return;
    map.flyTo({
        center: angle.center,
        zoom: angle.zoom,
        pitch: angle.pitch,
        bearing: angle.bearing,
        duration: 1500
    });
};
</script>

<template>
    <Head title="3D Canvas Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">3D背景キャンバス</h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
                
                <!-- コントロールパネル -->
                <div class="bg-white p-4 rounded-lg shadow-sm flex flex-wrap gap-4 items-center justify-between">
                    <div class="flex gap-4 text-xs font-mono bg-gray-100 p-2 rounded">
                        <span><strong>ズーム:</strong> {{ cameraState.zoom }}</span>
                        <span><strong>ピッチ:</strong> {{ cameraState.pitch }}°</span>
                        <span><strong>方位角:</strong> {{ cameraState.bearing }}°</span>
                    </div>

                    <!-- 保存入力エリア -->
                    <div>
                        <div v-if="!isSaving">
                            <button 
                                @click="startSave"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold py-2 px-4 rounded shadow transition"
                            >
                                💾 構図を保存する
                            </button>
                        </div>
                        <div v-else class="flex gap-2 items-center">
                            <input 
                                v-model="angleName"
                                type="text" 
                                class="text-sm border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-1 px-2"
                                placeholder="構図の名前"
                                @keyup.enter="executeSave"
                            />
                            <button 
                                @click="executeSave"
                                class="bg-green-600 hover:bg-green-700 text-white text-xs font-bold py-1.5 px-3 rounded shadow transition"
                            >
                                保存
                            </button>
                            <button 
                                @click="isSaving = false"
                                class="bg-gray-300 hover:bg-gray-400 text-gray-700 text-xs py-1.5 px-2 rounded transition"
                            >
                                取消
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 3Dマップエリア -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 relative">
                    <div ref="mapContainer" class="w-full h-[550px] rounded-lg overflow-hidden border border-gray-200"></div>

                    <!-- 保存済み構図一覧 -->
                    <div v-if="savedAngles && savedAngles.length > 0" class="absolute top-10 right-10 bg-white/90 backdrop-blur p-3 rounded-lg shadow-lg max-w-xs w-full border border-gray-100">
                        <h4 class="text-xs font-bold text-gray-500 mb-2 uppercase tracking-wider">保存済み構図（DB）</h4>
                        <div class="flex flex-col gap-2 max-h-48 overflow-y-auto">
                            <div 
                                v-for="angle in savedAngles" 
                                :key="angle.id"
                                class="flex justify-between items-center bg-gray-50 border border-gray-200 p-2 rounded hover:bg-indigo-50 transition"
                            >
                                <button 
                                    @click="applyAngle(angle)"
                                    class="text-left text-xs flex-1"
                                >
                                    <span class="font-bold text-gray-700 block">{{ angle.name }}</span>
                                    <span class="text-gray-400 text-[10px]">P:{{ angle.pitch }}° / B:{{ angle.bearing }}°</span>
                                </button>
                                <button 
                                    @click="deleteAngle(angle.id)"
                                    class="text-red-500 hover:text-red-700 text-xs px-2 py-1 font-bold"
                                >
                                    ✕
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>