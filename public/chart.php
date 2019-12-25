<!DOCTYPE html>
<html>
<head>
	<script type="text/javascript" src="/assets/js/Chart.min.js"></script>
	<title>Comparison Telegram Group Messages between Koding Teh and Tea Inside Indonesia</title>
    <link rel="stylesheet" type="text/css" href="/assets/css/chart.css?x=1"/>
</head>
<body>
<center>
    <div id="header">
        <h1 style="color:#fff;">Comparison Telegram Group Messages between Koding Teh and Tea Inside Indonesia</h1>
    </div>

    <div class="rebdr" id="selector">
        <h3>Select Date Range:</h3>
        <div>
            <div style="margin-right:20px;" class="scl">Start Date: <select id="start_date"></select></div>
            <div style="margin-left:20px;" class="scl">End Date: <select id="end_date"></select></div>
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
        <h1>Users Stats</h1>
        <h1 id="user_chart_loading">Loading...</h1>
        <div style="display:none" id="user_char_ctx">
            <div class="ust">
                <h3>Koding Teh</h3>
                <table id="koding_teh_user_ctx" class="ustq" border="1"></table>
            </div>
            <div class="ust">
                <h3>Tea Inside Indonesia</h3>
                <table id="tea_inside_user_ctx" class="ustq" border="1"></table>
            </div>
        </div>
    </div>

    <div class="rebdr" id="words_cloud_cage">
        <h1>Top 20 Words Cloud</h1>
        <h1 id="words_cloud_loading">Loading...</h1>
        <div style="display:none" id="words_cloud_ctx">
            <div class="ust">
                <h3>Koding Teh</h3>
                <table id="koding_teh_words_ctx" class="ustq" border="1"></table>
            </div>
            <div class="ust">
                <h3>Tea Inside Indonesia</h3>
                <table id="tea_inside_words_ctx" class="ustq" border="1"></table>
            </div>
        </div>
    </div>
</center>
<script type="text/javascript" src="/assets/js/r.js?x=2"></script>
</body>
</html>