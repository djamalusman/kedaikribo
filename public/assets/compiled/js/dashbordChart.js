document.addEventListener('DOMContentLoaded', function () {

    /* =====================================================
       MENU TERLARIS PER KATEGORI (BAR CHART)
    ===================================================== */
    const monthLabels   = window.menuCategoryMonthLabels || [];
    const seriesData    = window.menuCategoryMonthlySeries || [];
    const menuDetailMap = window.menuDetailMap || {};

    console.log('Month Labels:', monthLabels);
    console.log('Series Data:', seriesData);
    console.log('Detail Map:', menuDetailMap);

    const menuCtx = document.getElementById('menuCategoryChart');

    if (menuCtx && monthLabels.length && seriesData.length) {

        if (window.menuCategoryChartInstance) {
            window.menuCategoryChartInstance.destroy();
        }

        const colors = [
            '#0d6efd',
            '#198754',
            '#ffc107',
            '#dc3545',
            '#6f42c1',
            '#20c997',
            '#fd7e14',
            '#0dcaf0'
        ];

        const datasets = seriesData.map((item, index) => ({
            label: item.label,
            data: item.data,
            backgroundColor: colors[index % colors.length],
            borderRadius: 6,
            barPercentage: 0.75,
            categoryPercentage: 0.7
        }));

        window.menuCategoryChartInstance = new Chart(menuCtx, {
            type: 'bar',
            data: {
                labels: monthLabels,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            title: items => items[0].label,
                            label: function (ctx) {
                                const category = ctx.dataset.label;
                                const month = ctx.label;
                                const total = ctx.raw;

                                let lines = [`${category}: ${total} terjual`];

                                if (
                                    menuDetailMap[month] &&
                                    menuDetailMap[month][category]
                                ) {
                                    menuDetailMap[month][category].forEach(item => {
                                        lines.push(`• ${item.name}: ${item.qty}`);
                                    });
                                }

                                return lines;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Qty Terjual' }
                    }
                }
            }
        });
    } else {
        console.warn('Menu Terlaris: data / canvas tidak tersedia');
    }

    /* =====================================================
       OMZET BULANAN (LINE CHART)
    ===================================================== */
    const revenueLabels = window.monthlyLabels || [];
    const revenueData   = window.monthlyRevenue || [];

    const revenueCtx = document.getElementById('monthlyRevenueChart');

    if (revenueCtx && revenueLabels.length && revenueData.length) {

        if (window.monthlyRevenueChartInstance) {
            window.monthlyRevenueChartInstance.destroy();
        }

        window.monthlyRevenueChartInstance = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: revenueLabels,
                datasets: [{
                    label: 'Omzet Bulanan',
                    data: revenueData,
                    pointStyle: 'circle',
                    pointRadius: 6,
                    pointHoverRadius: 9,
                    pointBackgroundColor: '#28a745',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40,167,69,0.25)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: true },
                    tooltip: {
                        callbacks: {
                            label: ctx =>
                                'Rp ' + ctx.raw.toLocaleString('id-ID')
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: v => 'Rp ' + v.toLocaleString('id-ID')
                        }
                    }
                }
            }
        });
    } else {
        console.warn('Omzet bulanan: data / canvas tidak tersedia');
    }

});
