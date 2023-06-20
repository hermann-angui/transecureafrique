function getChartColorsArray(e) {
    if (null !== document.getElementById(e)) {
        var t = document.getElementById(e).getAttribute("data-colors");
        if (t) return (t = JSON.parse(t)).map(function (e) {
            var t = e.replace(" ", "");
            if (-1 === t.indexOf(",")) {
                var o = getComputedStyle(document.documentElement).getPropertyValue(t);
                return o || t
            }
            var a = e.split(",");
            return 2 != a.length ? t : "rgba(" + getComputedStyle(document.documentElement).getPropertyValue(a[0]) + "," + a[1] + ")"
        });
        console.warn("data-colors Attribute not found on:", e)
    }
}

$(document).ready(function(){

    var marqueChart = echarts.init(document.getElementById("pie-chart-marque"));
    option1 = {
        tooltip: {
            trigger: "item",
            formatter: "{a} <br/>{b} : {c} ({d}%)"
        },
        legend: {
            orient: "vertical",
            left: "left",
            data: [],
            textStyle: {color: "#8791af"}
        },
        color: [
            '#509472',
            '#ff751a',
            '#00a80c',
            '#ffbc8c',
            '#b16ebd',
            '#d0d0d0',
            '#57f2f8',
            '#efe5a2',
            '#c2f5d9',
            '#fd5500'],
        series: [{
            name: "Marques",
            type: "pie",
            radius: "55%",
            center: ["50%", "60%"],
            data: [
            ],
            itemStyle: {emphasis: {shadowBlur: 10, shadowOffsetX: 0, shadowColor: "rgba(0, 0, 0, 0.5)"}}
        }]
    };
    marqueChart.setOption(option1, !0);

    var energieChart = echarts.init(document.getElementById("pie-chart-energie"));
    option2 = {
        tooltip: {
            trigger: "item",
            formatter: "{a} <br/>{b} : {c} ({d}%)"
        },
        legend: {
            orient: "Energie",
            left: "left",
            data: [],
            textStyle: {color: "#8791af"}
        },
        color: ['#339966','#ff751a'],
        series: [
            {
                name: "Type d'énergie",
                type: "pie",
                radius: "55%",
                center: ["50%", "60%"],
                data: [
                ],
                itemStyle: {emphasis: {shadowBlur: 10, shadowOffsetX: 0, shadowColor: "rgba(0, 0, 0, 0.5)"}}
            }
        ]
    };
    energieChart.setOption(option2, !0);

    var columnChart = echarts.init(document.querySelector("#stacked-column-chart"));
    options = {
        chart: {
            height: 360,
            type: "bar",
            toolbar: {show: !1},
            zoom: {enabled: !0}
        },
        yAxis: {
            type: 'value',
            boundaryGap: [0, 0.01]
        },
        xAxis: {
            type: 'category',
            data: []
        },
        plotOptions: {bar: {horizontal: !1, columnWidth: "45%"}},
        dataLabels: {enabled: !1},
        series: [
        ],
        legend: {position: "bottom"},
        fill: {opacity: 1}
    };
    columnChart.setOption(options, !0);
   // columnChart.render();
    // chart = new ApexCharts(document.querySelector("#stacked-column-chart"), options)
    // chart.render();

   $.get('/api/stats/district').done(function(data){
        marqueChart.setOption({
            tooltip: {
                trigger: "item",
                formatter: "{a} <br/>{b} : {c} ({d}%)"
            },
            legend: {
                orient: "vertical",
                left: "left",
                data: data.marque.legend,
                textStyle: {color: "#8791af"}
            },
            color: [
                '#509472',
                '#ff751a',
                '#00a80c',
                '#ffbc8c',
                '#b16ebd',
                '#d0d0d0',
                '#57f2f8',
                '#efe5a2',
                '#c2f5d9',
                '#fd5500'],
            series: [
                {
                    name: "Marques",
                    type: "pie",
                    radius: "55%",
                    center: ["50%", "60%"],
                    data: data.marque.series,
                    itemStyle: {emphasis: {shadowBlur: 10, shadowOffsetX: 0, shadowColor: "rgba(0, 0, 0, 0.5)"}}
                }
            ]
        });
        energieChart.setOption({
            tooltip: {
                trigger: "item",
                formatter: "{a} <br/>{b} : {c} ({d}%)"
            },
            legend: {
                orient: "Energie",
                left: "left",
                data: data.energie.legend,
                textStyle: {color: "#8791af"}
            },
            color: ['#339966','#ff751a'],
            series: [{
                    name: "Type d'énergie",
                    type: "pie",
                    radius: "55%",
                    center: ["50%", "60%"],
                    data: data.energie.series,
                    itemStyle: {emphasis: {shadowBlur: 10, shadowOffsetX: 0, shadowColor: "rgba(0, 0, 0, 0.5)"}}
                }
            ]
        });
        console.log(data.monthly.axis);

       columnChart.setOption({
            chart: {
                height: 360,
                type: "bar",
                toolbar: {show: !1},
                zoom: {enabled: !0}
            },
            yAxis: {
                type: 'value',
                boundaryGap: [0, 0.01]
            },
            xAxis: {
                type: 'category',
                data: data.monthly.axis
            },
            plotOptions: {bar: {horizontal: !1, columnWidth: "45%"}},
            dataLabels: {enabled: !1},
            series: [
                data.monthly.delivres,
                data.monthly.demande,
            ],
            legend: {position: "bottom"},
            fill: {opacity: 1}
        });

   });
});

