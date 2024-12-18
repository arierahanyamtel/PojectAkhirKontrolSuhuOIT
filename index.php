<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontrol Lampu dan Grafik Sensor</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-light">
    <div class="container text-center py-4">
        <h2 class="mb-4">Kontrol Lampu dan Grafik Sensor</h2>

        <div class="row mb-4">
    <div class="col">
        <button id="lampu1" class="btn btn-outline-secondary" onclick="toggleLamp('D1')">Lampu 1</button>
    </div>
    <div class="col">
        <button id="lampu2" class="btn btn-outline-secondary" onclick="toggleLamp('D2')">Lampu 2</button>
    </div>
    <div class="col">
        <button id="lampu3" class="btn btn-outline-secondary" onclick="toggleLamp('D3')">Lampu 3</button>
    </div>
    <div class="col">
        <button id="auto" class="btn btn-outline-primary" onclick="toggleAuto()">AUTO</button>
    </div>
</div>


        <!-- Informasi Sensor -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body text-center">
                        <h4 id="temperature">Suhu: Memuat...</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body text-center">
                        <h4 id="humidity">Kelembaban: Memuat...</h4>
                        <h5 id="humidityStatus">Status: Memuat...</h5>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grafik Sensor -->
        <canvas id="sensorChart" height="100"></canvas>

        <!-- Audio Beep -->
        <audio id="beep" src="beep.mp3"></audio>
    </div>

    <script>
        let chart; 
        let previousSuhuRange = null;  
        let previousKelembabanRange = null;  
        let isFetching = false;  
        let autoMode = false;  

        
        function getSuhuRange(suhu) {
            if (suhu < 29) return 'below29';
            if (suhu >= 29 && suhu < 30) return '29to30';
            if (suhu >= 30 && suhu <= 31) return '30to31';
            return 'above31';
        }

        
        function getKelembabanRange(kelembaban) {
            if (kelembaban >= 30 && kelembaban < 60) return 'dry';
            if (kelembaban >= 60 && kelembaban < 70) return 'normal';
            return 'highHumidity';
        }

        
        async function fetchData() {
            if (isFetching) return; 

            isFetching = true; 

            try {
                const response = await fetch('http://localhost/kuliah/iot/getdata.php');
                const data = await response.json();

                const suhu = data.mqtt_data.temperature;
                const kelembaban = data.mqtt_data.humidity;

                document.getElementById('temperature').textContent = `Suhu: ${suhu} °C`;
                document.getElementById('humidity').textContent = `Kelembaban: ${kelembaban} %`;

                
                const kelembabanRange = getKelembabanRange(kelembaban);
                const humidityStatus = document.getElementById('humidityStatus');

                if (kelembabanRange === 'dry') {
                    humidityStatus.textContent = 'Tingkat Kelembaban: Kering/Aman';
                } else if (kelembabanRange === 'normal') {
                    humidityStatus.textContent = 'Tingkat Kelembaban: Mulai banyak uap air/Normal';
                } else if (kelembabanRange === 'highHumidity') {
                    humidityStatus.textContent = 'Tingkat Kelembaban: Terdapat banyak uap air';
                }

                
                if (previousKelembabanRange !== kelembabanRange) {
                    if (kelembabanRange === 'normal') {
                        playBeep(1);
                    } else if (kelembabanRange === 'highHumidity') {
                        playBeep(3);
                    }
                }

                
                const suhuRange = getSuhuRange(suhu);

                
                if (suhuRange === 'above31') {
                    if (previousSuhuRange !== suhuRange) {
                        playBeep(3);
                    }
                } else if (suhuRange === '30to31') {
                    if (previousSuhuRange !== suhuRange) {
                        playBeep(2);
                    }
                } else if (suhuRange === '29to30') {
                    if (previousSuhuRange !== suhuRange) {
                        playBeep(1);
                    }
                }

                
                previousSuhuRange = suhuRange;
                previousKelembabanRange = kelembabanRange;

                
                const labels = data.sensors_data.map(item => item.timestamp);
                const suhuData = data.sensors_data.map(item => item.temperature);
                const kelembabanData = data.sensors_data.map(item => item.humidity);

                updateChart(labels, suhuData, kelembabanData);
            } catch (error) {
                console.error('Error fetching data:', error);
            } finally {
                isFetching = false; 
            }
        }

        
        function updateChart(labels, suhuData, kelembabanData) {
            const ctx = document.getElementById('sensorChart').getContext('2d');

            if (chart) {
                chart.data.labels = labels;
                chart.data.datasets[0].data = suhuData;
                chart.data.datasets[1].data = kelembabanData;
                chart.update();
            } else {
                chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [
                            {
                                label: 'Suhu (°C)',
                                data: suhuData,
                                borderColor: 'red',
                                borderWidth: 2,
                            },
                            {
                                label: 'Kelembaban (%)',
                                data: kelembabanData,
                                borderColor: 'blue',
                                borderWidth: 2,
                            }
                        ]
                    }
                });
            }
        }

        
        function playBeep(times) {
            const beepSound = document.getElementById('beep');
            let count = 0;

            function play() {
                if (count < times) {
                    beepSound.play().catch(error => {
                        console.error('Error memutar audio:', error);
                    });
                    count++;
                    setTimeout(play, 1000); 
                }
            }
            play();
        }

       
function toggleLamp(lampId) {
    const button = document.getElementById(`lampu${lampId.charAt(1)}`);
    const currentStatus = button.textContent.includes('ON') ? 'OFF' : 'ON';
    const lampStatus = `${lampId}_${currentStatus}`;

    
    $.post('http://localhost/kuliah/iot/kontrol.php', { lamp: lampStatus }, (response) => {
        try {
            const data = typeof response === "string" ? JSON.parse(response) : response;

            if (data.status === 'SUCCESS') { 
                
                button.textContent = `Lampu ${lampId.charAt(1)}: ${currentStatus}`;
                if (currentStatus === 'ON') {
                    button.classList.remove('btn-outline-secondary');
                    button.classList.add('btn-success');
                } else {
                    button.classList.remove('btn-success');
                    button.classList.add('btn-outline-secondary');
                }
            } else {
                console.error('Gagal memperbarui status lampu:', data.message);
                alert(`Error: ${data.message}`);
            }
        } catch (error) {
            console.error('Error memproses respons dari server:', error);
        }
    }).fail(() => {
        console.error('Gagal menghubungi server.');
        alert('Tidak dapat menghubungi server. Periksa koneksi Anda.');
    });
}


        
function toggleAuto() {
    $.post('http://localhost/kuliah/iot/kontrol.php', { lamp: "AUTO" }, (response) => {
        try {
            const data = typeof response === "string" ? JSON.parse(response) : response;

            if (data.status === 'SUCCESS') {
                
                for (let i = 1; i <= 3; i++) {
                    const button = document.getElementById(`lampu${i}`);
                    button.textContent = `Lampu ${i}: OFF`;
                    button.classList.remove('btn-success');
                    button.classList.add('btn-outline-secondary');
                }
                console.log('Mode AUTO diaktifkan, semua lampu dimatikan');
            } else {
                console.error('Gagal mengaktifkan mode AUTO:', data.message);
                alert(`Error: ${data.message}`);
            }
        } catch (error) {
            console.error('Error memproses respons dari server:', error);
        }
    }).fail(() => {
        console.error('Gagal menghubungi server.');
        alert('Tidak dapat menghubungi server. Periksa koneksi Anda.');
    });
}


        
        setInterval(fetchData, 20000); 

    </script>
</body>
</html>
