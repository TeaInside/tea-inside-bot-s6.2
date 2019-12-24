<!DOCTYPE html>
<html>
<head>
	<script type="text/javascript" src="https://www.chartjs.org/dist/2.9.3/Chart.min.js"></script>
	<title></title>
</head>
<body>
<center>
    <div style="margin-top:30px;width:65%;">
        <canvas id="myChart" width="500" height="250"></canvas>
    </div>
</center>
<script type="text/javascript">
    var ctx = document.getElementById('myChart').getContext('2d');
    var data = {
        labels: [
            "11 January 2019",
            "12 January 2019",
            "13 January 2019",
            "14 January 2019",
            "15 January 2019",
        ],
        datasets: [
            {
                label: "Koding Teh",
                data: [3, 2, 4, 6, 1],
                backgroundColor: 'red',
                borderColor: 'red',
                borderWidth: 3,
                fill: false
            },
            {
                label: "Tea Inside",
                data: [10, 20, 1, 4, 2],
                backgroundColor: 'green',
                borderColor: 'green',
                borderWidth: 3,
                fill: false
            }
        ]
    };
	var myLineChart = new Chart(ctx, {
		type: 'line',
		data: data,
		options: {
        scales: {
                xAxes: [{
                    scaleLabel: {
                        display: true,
                        labelString: 'Date'
                    },
                    ticks: {
                        beginAtZero: true
                    }
                }],
                yAxes: [{
                    scaleLabel: {
                        display: true,
                        labelString: 'Messages'
                    },
                    ticks: {
                        beginAtZero: true
                    }
                }]
            }
        }
	});
</script>
</body>
</html>