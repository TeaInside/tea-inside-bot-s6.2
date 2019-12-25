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
    user_chart_ctx  = document.getElementById("user_char_ctx"),
    user_chart_loading = document.getElementById("user_chart_loading"),
    update_charts = document.getElementById("update_charts"),
    words_cloud_ctx = document.getElementById("words_cloud_ctx"),
    words_cloud_loading = document.getElementById("words_cloud_loading")
    msg_chart_l = 0,
    user_chart_l = 0,
    words_cloud_l = 0;

for (i = 0; i < 500; i++) {
    month = (dateObj.getMonth() + 1).toString();
    day = dateObj.getDate().toString();
    year = dateObj.getFullYear().toString();
    if (day.length == 1) day = '0'+day[0];
    if (month.length == 1) month = '0'+month[0];
    x = year + "-" + month + "-" + day;
    y = year + " " + monthNames[parseInt(month) - 1] + " " + day;
    if (i == 14) {
        start_date.innerHTML += "<option value=\""+x+"\" selected>"+y+"</option>";
    } else {
        start_date.innerHTML += "<option value=\""+x+"\">"+y+"</option>";
    }
    if (i == 0) {
        end_date.innerHTML += "<option value=\""+x+"\" selected>"+y+"</option>";
    } else {
        end_date.innerHTML += "<option value=\""+x+"\">"+y+"</option>";
    }
    dateObj.setDate(dateObj.getDate() - 1);
}

function msgChart() {
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

function userChart(){
    user_chart_l = 0;
    user_chart_ctx.style.display = "none";
    user_chart_loading.style.display = "";
    let ch = new XMLHttpRequest;
    ch.onload = function () {
        let i, r, l, rt = 0, lt = 0, j = JSON.parse(this.responseText), nr = 0, nl = 0;
        l = r = '<tr><th align="center">No.</th><th align="center">Photo</th><th align="center">Name</th><th align="center">Messages</th></tr>';
        for (i in j) {
            if (j[i][0] == 1) {
                rt += parseInt(j[i][5]);
                r += '<tr><td align="center">'+(nr++)+'</td><td align="center"><img class="ppim" src="https://telegram-bot.teainside.org/storage/files/'+j[i][4]+'"/></td><td class="tdx">'+escapeHtml(j[i][2])+'</td><td align="center">'+j[i][5]+'</td></tr>';
            } else {
                lt += parseInt(j[i][5]);
                l += '<tr><td align="center">'+(nl++)+'</td><td align="center"><img class="ppim" src="https://telegram-bot.teainside.org/storage/files/'+j[i][4]+'"/></td><td class="tdx">'+escapeHtml(j[i][2])+'</td><td align="center">'+j[i][5]+'</td></tr>';
            }
        }
        r += '<tr><td colspan="3" align="center">Total</td><td align="center">'+rt+'</td></tr>';
        l += '<tr><td colspan="3" align="center">Total</td><td align="center">'+lt+'</td></tr>';
        document.getElementById("koding_teh_user_ctx").innerHTML = r;
        document.getElementById("tea_inside_user_ctx").innerHTML = l;
        user_chart_l = 1;
        user_chart_ctx.style.display = "";
        user_chart_loading.style.display = "none";
    };
    ch.open("GET", "https://telegram-bot.teainside.org/api.php?key=chart&action=user_stats&start_date="+start_date.value+"&end_date="+end_date.value);
    ch.send();
}

function wordsCloudChart(){
    words_cloud_chart_l = 0;
    words_cloud_ctx.style.display = "none";
    words_cloud_loading.style.display = "";
    let ch = new XMLHttpRequest;
    ch.onload = function () {
        let i, r, l, rt = 0, lt = 0, j = JSON.parse(this.responseText), nr = 0, nl = 0;
        l = r = '<tr><th align="center">No.</th><th align="center">Word</th><th align="center">Occurences</th></tr>';
        for (i in j) {
            if (j[i][0] == 1) {
                r += '<tr><td align="center">'+(nr++)+'</td><td class="tdx">'+j[i][1]+'</td><td align="center">'+j[i][2]+'</td></tr>';
            } else {
                l += '<tr><td align="center">'+(nl++)+'</td><td class="tdx">'+j[i][1]+'</td><td align="center">'+j[i][2]+'</td></tr>';
            }
        }
        document.getElementById("koding_teh_words_ctx").innerHTML = r;
        document.getElementById("tea_inside_words_ctx").innerHTML = l;
        words_cloud_l = 1;
        words_cloud_ctx.style.display = "";
        words_cloud_loading.style.display = "none";
    };
    ch.open("GET", "https://telegram-bot.teainside.org/api.php?key=chart&action=words_cloud&start_date="+start_date.value+"&end_date="+end_date.value);
    ch.send();
}

let update_click = function () {
    msg_chart_l = user_chart_l = words_cloud_l = 0; 
    msgChart();
    userChart();
    wordsCloudChart();
    update_charts.disabled = 1;
    let intv = setInterval(function () {
        if (msg_chart_l && user_chart_l && words_cloud_l) {
            update_charts.disabled = 0;
            clearInterval(intv);
        }
    }, 500);
}
update_charts.addEventListener("click", update_click);
update_click();