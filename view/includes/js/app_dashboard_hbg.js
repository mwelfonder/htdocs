
var userdata = '';
const currentMonth = new Date().getMonth() + 1; // zero based - returns 3 for March
var nameslist = '';

$(document).ready(function () { // load charts and define them globaly with window.
    window.workdays = $('#workdays').text();


    // ############################## //
    // render the Monthyl Line chart
    var options = {
        chart: {
            type: 'line',
            height: 350,
            zoom: {
                enabled: false // disable zoom here
            }
        },
        series: [{
            name: 'Done',
            data: [0],
            color: '#02a54b',
        }, {
            name: 'Open',
            data: [0],
            color: '#0082f2',
        }, {
            name: 'Abbruch',
            data: [0],
            color: '#ffa90b',
        }],
        xaxis: {
            categories: ['Jan', 'Feb', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
        },
        dataLabels: {
            enabled: true,
            enabledOnSeries: [0, 1, 2]
        }
    }

    window.chart_monthlines = new ApexCharts(document.querySelector("#chart_monthlines"), options);
    chart_monthlines.render();




    var options = {
        series: [{
            name: 'Kunde war nicht da',
            data: [0, 0],
            color: '#f19f07',
        }, {
            name: 'Ich war nicht da',
            data: [0, 0],
            color: '#07d1f1',
        }, {
            name: 'HBG nicht durchführbar',
            data: [0, 0],
            color: '#f10707',
        }],
        chart: {
            type: 'bar',
            height: 400,
            stacked: true,
            scrollbar: {
                enabled: true
            }
        },
        plotOptions: {
            bar: {
                horizontal: true,
                dataLabels: {
                    position: 'top',
                },
            }
        },
        dataLabels: {
            enabled: true,
            offsetX: -6,
            style: {
                fontSize: '12px',
                colors: ['#fff']
            }
        },
        stroke: {
            show: true,
            width: 1,
            colors: ['#fff']
        },
        tooltip: {
            shared: true,
            intersect: false
        },
        xaxis: {
            categories: [0, 0],
        },
    };

    window.chart_reasons = new ApexCharts(document.querySelector("#chart_reasons"), options);
    chart_reasons.render();




    var options = {
        series: [{
            name: 'Total',
            data: [0, 0],
            color: '#6b6b6b',
        }, {
            name: 'Done',
            data: [0, 0],
            color: '#02a54b',
        }, {
            name: 'Abbruch',
            data: [0, 0],
            color: '#ffa90b',
        }, {
            name: 'Open',
            data: [0, 0],
            color: '#0082f2',
        }],
        chart: {
            type: 'bar',
            height: 350
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '55%',
                endingShape: 'rounded'
            },
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            show: true,
            width: 2,
            colors: ['transparent']
        },
        xaxis: {
            categories: [0, 0],
        },
        yaxis: {
            title: {
                text: 'Hausbegehungen'
            }
        },
        fill: {
            opacity: 1
        },

    };

    window.chart_hausbegeher = new ApexCharts(document.querySelector("#chart_hausbegeher"), options);
    chart_hausbegeher.render();

    // fit page height
    var height = $('.content-wrapper').height();
    height = height + 400;
    $('.body_content').css('height', height + 'px');

    // define tooltips
    $("[title]").tooltip();

});


$(document).ready(function () {





    $.ajax({
        method: "POST",
        url: "view/load/dashboard_overview_hbg_load.php",
        data: {
            func: "load_username",
            user: 'user',
        },
    }).done(function (response) {
        console.log("log" + response);
        userdata = JSON.parse(response);
        nameslist = Object.keys(userdata).map(name => name);
        console.log(userdata)
        console.log(nameslist)
        var totalcount = getTotalCount('all', 'total', 'all')
        $('#box_total_total').html(totalcount);
        $('#box_total_month').html((parseInt(totalcount) / currentMonth).toFixed(2));
        $('#box_total_day').html((parseInt(totalcount) / workdays).toFixed(2));
        // --------------------------------------------------------------------- //
        var count = getTotalCount('all', 'done', 'all')
        $('#box_done_total').html(count);
        $('#box_done_month').html((parseInt(count) / currentMonth).toFixed(2));
        $('#box_done_day').html((parseInt(count) / workdays).toFixed(2));
        $('#box_perc_done').html((parseInt(count) / totalcount * 100).toFixed(2) + '%');
        // --------------------------------------------------------------------- //
        var count = parseInt(getTotalCount('all', 'imnotthere', 'all'));
        count += parseInt(getTotalCount('all', 'impossible', 'all'));
        count += parseInt(getTotalCount('all', 'kdnotthere', 'all'));
        $('#box_abb_total').html(count);
        $('#box_abb_month').html((parseInt(count) / currentMonth).toFixed(2));
        $('#box_abb_day').html((parseInt(count) / workdays).toFixed(2));
        $('#box_perc_abb').html((parseInt(count) / totalcount * 100).toFixed(2) + '%');
        // --------------------------------------------------------------------- //
        var count = getTotalCount('all', 'open', 'all')
        $('#box_open_total').html(count);
        $('#box_open_month').html((parseInt(count) / currentMonth).toFixed(2));
        $('#box_open_day').html((parseInt(count) / workdays).toFixed(2));
        $('#box_perc_open').html((parseInt(count) / totalcount * 100).toFixed(2) + '%');
        // --------------------------------------------------------------------- //
        $('#select_hausbegeher').trigger('change'); // fake trigger the select to update the user stats and charts with val "all"


        const doneData = [];
        const openData = [];
        const totalData = [];
        const imnotthereData = [];
        const impossibleData = [];
        const kdnotthereData = [];
        const abbruchData = [];

        for (const key in userdata) {
            if (Object.hasOwnProperty.call(userdata, key)) {
                const doneSubArray = userdata[key].done;
                const doneCount = Object.values(doneSubArray).reduce((a, b) => a + b, 0);
                doneData.push(doneCount);

                const openSubArray = userdata[key].open;
                const openCount = Object.values(openSubArray).reduce((a, b) => a + b, 0);
                openData.push(openCount);

                const totalSubArray = userdata[key].total;
                const totalCount = Object.values(totalSubArray).reduce((a, b) => a + b, 0);
                totalData.push(totalCount);

                const imnotthereSubArray = userdata[key].imnotthere;
                const imnotthereCount = Object.values(imnotthereSubArray).reduce((a, b) => a + b, 0);
                imnotthereData.push(imnotthereCount);

                const impossibleSubArray = userdata[key].impossible;
                const impossibleCount = Object.values(impossibleSubArray).reduce((a, b) => a + b, 0);
                impossibleData.push(impossibleCount);

                const kdnotthereSubArray = userdata[key].kdnotthere;
                const kdnotthereCount = Object.values(kdnotthereSubArray).reduce((a, b) => a + b, 0);
                kdnotthereData.push(kdnotthereCount);

                const abbruchCount = imnotthereCount + impossibleCount + kdnotthereCount;
                abbruchData.push(abbruchCount);
            }
        }


        chart_hausbegeher.updateOptions({
            series: [{
                name: 'Total',
                data: totalData,
                color: '#6b6b6b',
            }, {
                name: 'Done',
                data: doneData,
                color: '#02a54b',
            }, {
                name: 'Abbruch',
                data: abbruchData,
                color: '#ffa90b',
            }, {
                name: 'Open',
                data: openData,
                color: '#0082f2',
            }],
            xaxis: {
                categories: nameslist,
            }
        });








    });


    $('#select_hausbegeher').on('change', function () {
        var user = $(this).val();
        if (user === 'all') {
            $('.usertotals').addClass('hidden');
        } else {
            $('.usertotals').removeClass('hidden');
        }
        var totalcount = getTotalCount(user, 'total', 'all');
        $('#user_total_total').html(totalcount);
        $('#user_total_month').html((parseInt(totalcount) / currentMonth).toFixed(2));
        $('#user_total_day').html((parseInt(totalcount) / workdays).toFixed(2));

       // getpositiveDayscount(user, 'total', 'all')

        // --------------------------------------------------------------------- //
        var count = getTotalCount(user, 'done', 'all');
        $('#user_done_total').html(count);
        $('#user_done_month').html((parseInt(count) / currentMonth).toFixed(2));
        $('#user_done_day').html((parseInt(count) / workdays).toFixed(2));
        $('#user_perc_done').html((parseInt(count) / totalcount * 100).toFixed(2) + '%');
        // --------------------------------------------------------------------- //
        var count = parseInt(getTotalCount(user, 'imnotthere', 'all'));
        count += parseInt(getTotalCount(user, 'impossible', 'all'));
        count += parseInt(getTotalCount(user, 'kdnotthere', 'all'));
        $('#user_abb_total').html(count);
        $('#user_abb_month').html((parseInt(count) / currentMonth).toFixed(2));
        $('#user_abb_day').html((parseInt(count) / workdays).toFixed(2));
        $('#user_perc_abb').html((parseInt(count) / totalcount * 100).toFixed(2) + '%');
        // --------------------------------------------------------------------- //
        var count = getTotalCount(user, 'open', 'all');
        $('#user_open_total').html(count);
        $('#user_open_month').html((parseInt(count) / currentMonth).toFixed(2));
        $('#user_open_day').html((parseInt(count) / workdays).toFixed(2));
        $('#user_perc_open').html((parseInt(count) / totalcount * 100).toFixed(2) + '%');

        var data_total = [];
        for (var i = 1; i <= 12; i++) {
            data_total.push(parseInt(getTotalCount(user, 'total', i)));
        }
        var data_done = [];
        for (var i = 1; i <= 12; i++) {
            data_done.push(parseInt(getTotalCount(user, 'done', i)));
        }
        var data_imnotthere = [];
        for (var i = 1; i <= 12; i++) {
            data_imnotthere.push(parseInt(getTotalCount(user, 'imnotthere', i)));
        }
        var data_kdnotthere = [];
        for (var i = 1; i <= 12; i++) {
            data_kdnotthere.push(parseInt(getTotalCount(user, 'kdnotthere', i)));
        }
        var data_impossible = [];
        for (var i = 1; i <= 12; i++) {
            data_impossible.push(parseInt(getTotalCount(user, 'impossible', i)));
        }
        var data_abb = [];
        for (var i = 1; i <= 12; i++) {
            var count = parseInt(getTotalCount(user, 'imnotthere', i));
            count += parseInt(getTotalCount(user, 'impossible', i));
            count += parseInt(getTotalCount(user, 'kdnotthere', i));
            data_abb.push(count);
        }
        var data_open = [];
        for (var i = 1; i <= 12; i++) {
            data_open.push(parseInt(getTotalCount(user, 'open', i)));
        }

        chart_monthlines.updateOptions({
            series: [
                {
                    data: data_total,
                    name: 'Total',
                    color: '#a3a3a3',
                },
                {
                    data: data_done,
                    name: 'Done',
                    color: '#02a54b',
                },
                {
                    data: data_open,
                    name: 'Open',
                    color: '#0082f2',
                },
                {
                    data: data_abb,
                    name: 'Abbruch',
                    color: '#ffa90b',
                }
            ],
            xaxis: {
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            },
            stroke: {
                width: [5, 5, 5, 5],
                curve: 'straight',
                dashArray: [5, 0, 0, 0]
            },
        });

        chart_reasons.updateOptions({
            series: [
                {
                    data: data_imnotthere,
                    name: 'Ich war nicht da',
                    color: '#fdb152',
                },
                {
                    data: data_kdnotthere,
                    name: 'KD war nicht da',
                    color: '#4464ff',
                },
                {
                    data: data_impossible,
                    name: 'HBG nicht durchführbar',
                    color: '#ff6b6b',
                }
            ],
            xaxis: {
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            }
        });













    });
});



function parsedata(user) {




}


function getTotalCount(name, status, month) {
    let count = 0;
    let data;

    if (name === 'all') {
        // If the name parameter is 'all', sum up the counts for all names
        for (const n in userdata) {
            data = userdata[n][status];
            if (month === 'all') {
                for (const key in data) {
                    count += data[key];
                }
            } else {
                for (const key in data) {
                    const date = new Date(key);
                    if (date.getMonth() === month - 1) {
                        count += data[key];
                    }
                }
            }
        }
    } else {
        // Otherwise, get the count for the specified name
        data = userdata[name][status];
        if (month === 'all') {
            for (const key in data) {
                count += data[key];
            }
        } else {
            for (const key in data) {
                const date = new Date(key);
                if (date.getMonth() === month - 1) {
                    count += data[key];
                }
            }
        }
    }

    return count;
}


/*
function getpositiveDayscount(name, subarray, month) {
    const data = yourArray[name][subarray];
    let sum = 0;
    for (const date in data) {
      const dateObj = new Date(date);
      if (month === "all" || dateObj.getMonth() === month - 1) {
        if (data[date] > 0) {
          sum += 1;
        }
      }
    }
    return sum;
  }
*/



// apex for daily overview
/* global apex 
var parse = JSON.parse(response);
console.log(parse)
var chartData = [];

// Convert the data to an array of arrays with timestamp and value
for (var timestamp in parse) {
  var point = [
    timestamp * 1000, // Convert the timestamp to milliseconds
    parse[timestamp]
  ];
  chartData.push(point);
}


var options = {
    chart: {
      type: 'line',
      height: 350,
      zoom: {
        enabled: true,
        type: 'x',
        autoScaleYaxis: true
      }
    },
    series: [{
      name: 'Sales',
      data: chartData
    }],
    xaxis: {
      type: 'datetime'
    },
    responsive: [{
      breakpoint: 1000,
      options: {
        chart: {
          height: 300,
        }
      }
    }]
  }
var chart = new ApexCharts(document.querySelector("#monthchard_totals"), options);
chart.render();
*/