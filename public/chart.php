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
    let ch = new XMLHttpRequest;
    ch.onload = function () {
        let ctx = document.getElementById('myChart').getContext('2d'),
        myLineChart = new Chart(ctx, {
            type: 'line',
            data: JSON.parse(this.responseText),
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
    };
    ch.open("GET", "https://telegram-bot.teainside.org/api.php?key=chart&action=msg&start_date=2019-12-15&end_date=2019-12-25");
    ch.send();
</script>
</body>
</html>