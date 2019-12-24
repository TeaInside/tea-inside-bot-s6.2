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
            border: 2px solid #000;
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
<script type="text/javascript" src="/assets/js/r.js"></script>
</body>
</html>