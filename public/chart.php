<!DOCTYPE html>
<html>
<head>
	<script type="text/javascript" src="/assets/js/Chart.min.js"></script>
	<title>Comparison Telegram Group Messages between Koding Teh and Tea Inside Indonesia</title>
    <style type="text/css">
        body {
            background-color: #000;
        }
        * {
            font-family: Arial;
        }
        #header {
            margin-top: 10px;
            margin-bottom: 40px;
        }
        #msg_chart_cage {
            margin-top: 50px;
            width: 90%;
        }
        #user_chart_cage {
            margin-top: 20px;
            width: 80%;
            padding: 0px 10px 35px 10px;
        }
        .rebdr {
            margin-top: 10px;
            background-color: #fff;
            border: 1px solid #000;
        }
        #selector {
            width: 38%;
            padding: 20px;
            padding-top: 0px;
        }
        .ust {
            height: 400px;
            overflow-y: scroll;
            overflow-x: scroll;
            padding: 0px 10px 35px 10px;
            border: 1px solid #000;
            width: 43%;
            display: inline-block;
        }
        .ustq {
            border-collapse: collapse;
        }
        .ustq tr th {
            padding: 5px 8px 5px 8px;
        }
        .tdx {
            padding-right: 5px;
            padding-left: 5px;
        }
        .ppim {
            border-radius: 100%;
            width: 50px;
            height: 50px;
            background-color: #000;
        }
    </style>
</head>
<body>
<center>
    <div id="header">
        <h1 style="color:#fff;">Comparison Telegram Group Messages between Koding Teh and Tea Inside Indonesia</h1>
    </div>

    <div class="rebdr" id="selector">
        <h3>Select Date Range:</h3>
        <div>
            Start Date: <select id="start_date"></select>&nbsp;&nbsp;&nbsp;End Date: <select id="end_date"></select>
        </div>
        <div style="margin-top:10px;">
            <button style="cursor:pointer;" id="update_charts">Update Charts</button>
        </div>
    </div>

    <div class="rebdr" id="msg_chart_cage">
        <h2>Messages Amount</h2>
        <div style="margin-top:30px;width:95%;">
            <h1 id="msg_chart_loading">Loading...</h1>
            <canvas style="display:none;" id="msg_chart_ctx" width="500" height="250"></canvas>
        </div>
    </div>

    <div class="rebdr" id="user_chart_cage">
        <h2>Users Stats</h2>
        <h1 id="user_chart_loading">Loading...</h1>
        <div style="display:none" id="user_char_ctx">
            <div class="ust">
                <h3>Koding Teh</h3>
                <table id="koding_teh_user_ctx" class="ustq" border="1">
                    
                </table>
            </div>
            <div class="ust">
                <h3>Tea Inside Indonesia</h3>
                <table id="tea_inside_user_ctx" class="ustq" border="1">
                    <tr><th>No.</th><th>Name</th><th>Messages</th></tr>
                </table>
            </div>
        </div>
    </div>
</center>
<script type="text/javascript">
    function escapeHtml(text) {
      return text
        .toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
    }
    const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    let dateObj = new Date(), month, day, year, x, y, i,
        start_date = document.getElementById("start_date"),
        end_date = document.getElementById("end_date"),
        msg_chart_loading = document.getElementById("msg_chart_loading"),
        msg_chart_ctx = document.getElementById("msg_chart_ctx"),
        user_char_ctx  = document.getElementById("user_char_ctx"),
        user_chart_loading = document.getElementById("user_chart_loading"),
        update_charts = document.getElementById("update_charts"),
        msg_chart_l = 0,
        user_chart_l = 0;

    for (i = 0; i < 500; i++) {
        month = (dateObj.getUTCMonth() + 1).toString();
        day = dateObj.getUTCDate().toString();
        year = dateObj.getUTCFullYear().toString();
        if (day.length == 1) day = '0'+day[0];
        if (month.length == 1) month = '0'+month[0];
        x = year + "-" + month + "-" + day;
        y = year + " " + monthNames[parseInt(month) - 1] + " " + day;
        if (i == 12) {
            start_date.innerHTML += "<option value=\""+x+"\" selected>"+y+"</option>";
        } else {
            start_date.innerHTML += "<option value=\""+x+"\">"+y+"</option>";
        }
        if (i == 1) {
            end_date.innerHTML += "<option value=\""+x+"\" selected>"+y+"</option>";
        } else {
            end_date.innerHTML += "<option value=\""+x+"\">"+y+"</option>";
        }
        dateObj.setDate(dateObj.getDate() - 1);
    }

    async function msgChart() {
        msg_chart_l = 0;
        msg_chart_ctx.style.display = "none";
        msg_chart_loading.style.display = "";
        let ch = new XMLHttpRequest;
        ch.onload = function () {
            let data = JSON.parse(this.responseText),
                myLineChart = new Chart(msg_chart_ctx.getContext('2d'), {
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
            msg_chart_l = 1;
            msg_chart_ctx.style.display = "";
            msg_chart_loading.style.display = "none";
        };
        ch.open("GET", "https://telegram-bot.teainside.org/api.php?key=chart&action=msg&start_date="+start_date.value+"&end_date="+end_date.value);
        ch.send();
    }

    async function userChart(){
        user_chart_l = 0;
        user_char_ctx.style.display = "none";
        user_chart_loading.style.display = "";
        let ch = new XMLHttpRequest;
        ch.onload = function () {
            let i, r, l, rt = 0, lt = 0, j = JSON.parse(this.responseText), nr = 0, nl = 0;
            l = r = '<tr><th align="center">No.</th><th align="center">Photo</th><th align="center">Name</th><th align="center">Messages</th></tr>';
            for (i in j) {
                if (j[i][0] == 1) {
                    rt += parseInt(j[i][5]);
                    r += '<tr><td align="center">'+(nr++)+'</td><td><img class="ppim" src="https://telegram-bot.teainside.org/storage/files/'+j[i][4]+'"/></td><td class="tdx">'+escapeHtml(j[i][2])+'</td><td align="center">'+j[i][5]+'</td></tr>';
                } else {
                    lt += parseInt(j[i][5]);
                    l += '<tr><td align="center">'+(nl++)+'</td><td><img class="ppim" src="https://telegram-bot.teainside.org/storage/files/'+j[i][4]+'"/></td><td class="tdx">'+escapeHtml(j[i][2])+'</td><td align="center">'+j[i][5]+'</td></tr>';

                }
            }
            r += '<tr><td colspan="3" align="center">Total</td><td align="center">'+rt+'</td></tr>';
            l += '<tr><td colspan="3" align="center">Total</td><td align="center">'+lt+'</td></tr>';
            document.getElementById("koding_teh_user_ctx").innerHTML = r;
            document.getElementById("tea_inside_user_ctx").innerHTML = l;
            user_chart_l = 1;
            user_char_ctx.style.display = "";
            user_chart_loading.style.display = "none";
        };
        ch.open("GET", "https://telegram-bot.teainside.org/api.php?key=chart&action=user_stats&start_date="+start_date.value+"&end_date="+end_date.value);
        ch.send();
    }

    let update_click = function () {
        msg_chart_l = user_chart_l = 0; 
        msgChart();
        userChart();
        update_charts.disabled = 1;
        let intv = setInterval(function () {
            if (msg_chart_l && user_chart_l) {
                update_charts.disabled = 0;
                clearInterval(intv);
            }
        }, 500);
    }
    update_charts.addEventListener("click", update_click);
    update_click();
</script>
</body>
</html>