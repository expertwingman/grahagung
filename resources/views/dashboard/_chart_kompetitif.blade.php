{{-- Chart kompetitif sumber lead: garis berlomba per minggu --}}
<div class="bg-white rounded-xl shadow-sm p-5">
    <h2 class="font-bold text-gray-700 mb-1">Sumber lead — siapa yang naik?</h2>
    <p class="text-xs text-gray-400 mb-4">8 minggu terakhir, kumulatif per minggu</p>
    <canvas id="chartKompetitif" height="200"></canvas>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const data = @json($sumberPerMinggu);
    const warna = ['#3B82F6','#F59E0B','#10B981','#8B5CF6','#EF4444','#EC4899','#0891B2','#6366F1'];
    const datasets = data.series.map((s, i) => ({
        label: s.name,
        data: s.data,
        borderColor: warna[i % warna.length],
        backgroundColor: warna[i % warna.length] + '18',
        borderWidth: 3,
        pointRadius: 4,
        pointBackgroundColor: warna[i % warna.length],
        tension: 0.3,
        fill: false,
    }));
    new Chart(document.getElementById('chartKompetitif'), {
        type: 'line',
        data: { labels: data.labels, datasets },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 11 }, usePointStyle: true, padding: 12 } },
                tooltip: { callbacks: { label: (ctx) => `${ctx.dataset.label}: ${ctx.parsed.y} lead` } },
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } }, title: { display: true, text: 'Lead', font: { size: 11 } } },
                x: { ticks: { font: { size: 11 } } },
            },
        },
    });
});
</script>
