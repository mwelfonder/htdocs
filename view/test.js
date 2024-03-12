




var hbgDataCache = {};


var user_dest;


var weekplan = {};


getdist();

function getdist() {
    $('#loaderwrapper2').removeClass('hidden')

    $.ajax({
        method: "POST",
        url: "view/load/test_load.php",
        data: {
            func: "load_distance",

        },
    }).done(function (response) {
        // console.log(response);
        user_dest = JSON.parse(response);
        console.log(user_dest)
        $('#loaderwrapper2').addClass('hidden')


    });

}





$(document).ready(function () {




    // ========================================================
    // create the projectlist to match citys
    function processTable(tableId, projectName, projectList) {
        const table = document.getElementById(tableId);
        for (let i = 0; i < table.rows.length; i++) {
            const cell = table.rows[i].cells[0];
            projectList[projectName].push(cell.textContent);
        }
    }

    const projectlist = {
        insyte: [],
        moncobra: []
    };

    processTable("Tproject_Insyte", "insyte", projectlist);
    processTable("Tproject_Moncobra", "moncobra", projectlist);
    console.log('projectlist');
    console.log(projectlist);



    // ------------------------------------------------
    // Init Datatables for the Projectlist
    $('.projecttable').DataTable({
        scrollX: true,

        "bLengthChange": false,
        autoWidth: true,
        paging: false,
    });



    const userlist = get_userlist();


    // -------------------------------------------------------
    // load the weekplan array from db and paste the spans to the DOM
    get_weekplan();












    // --------------------------------------------------
    // context menu
    // Create an object to hold the menu items
    var menuItems = {
        copy: {
            name: "Copy",
            callback: function (key, opt) {
                alert("Copy");
            }
        },
        // Create a submenu called "submenu"
        submenu: {
            name: "Move to",
            items: {}
        }
    };

    // Add the items from the userlist array to the submenu
    for (var i = 0; i < userlist.length; i++) {
        var item = userlist[i];
        menuItems.submenu.items[item] = {
            name: item,
            callback: function (key, opt) {
                alert(key + "!");
            }
        };
    }

    // Use the menuItems object to create the context menu
    $.contextMenu({
        selector: ".eventelements:not(.past) .selected", // exclude tables with "past" class
        items: menuItems
    });




    // --------------------------------------------------
    // context menu for the single cells with left click

    $('.addzone').on('click', function (e) {
        if (e.button === 0) {
            e.preventDefault();
            $(this).contextMenu({
                x: e.pageX,
                y: e.pageY
            });
        }
    });
    $.contextMenu({
        selector: '.addzone',
        trigger: 'none',
        callback: function (key, options) {
            var m = "clicked: " + key;
            console.log(m);
            var target = this;
            add_day_event(key, target)
        },
        items: {
            "urlaub": {
                name: "Urlaub",
                icon: "fa-solid fa-plane-departure"
            },
            "krank": {
                name: "Krank",
                icon: "fa-solid fa-user-doctor"
            },
            "survey": {
                name: "Survey",
                icon: "fa-solid fa-person-walking"
            },
            "streetnav": {
                name: "StreetNav",
                icon: "fa-solid fa-road"
            },
            "callCenter": {
                name: "CallCenter",
                icon: "fa-solid fa-headphones"
            },
            /*
            "sep1": "---------",
            "quit": {
              name: "Quit",
              icon: function($element, key, item) {
                return 'context-menu-icon context-menu-icon-quit';
              }
            }*/
        }
    });







    // ---------------------------------------
    // Listen for click events on table rows
    $('.eventelements').on('click', 'tr', function (event) {
        // If the Ctrl key is held down, toggle the selection of this row
        if (event.ctrlKey) {
            $(this).toggleClass('selected');
        }
        // If the Shift key is held down, select a range of rows
        else if (event.shiftKey && lastSelectedRow != null) {
            var startIndex = $('.eventelements tr').index(lastSelectedRow);
            var endIndex = $('.eventelements tr').index(this);
            if (endIndex < startIndex) {
                var tmp = startIndex;
                startIndex = endIndex;
                endIndex = tmp;
            }
            $('.eventelements tr').removeClass('selected');
            $('.eventelements tr').slice(startIndex, endIndex + 1).addClass('selected');
        }
        // If the row is already selected, deselect it
        else if ($(this).hasClass('selected')) {
            $(this).removeClass('selected');
        }
        // Otherwise, deselect all other rows and select this row
        else {
            $('.eventelements tr').removeClass('selected');
            $(this).addClass('selected');
        }
        lastSelectedRow = this;
    });







    // ---------------------------------------
    // tab slide function 
    $('#citytab_Moncobra').slideUp();
    $('.headtab').click(function () {
        var $panel = $(this).next();
        $('.headtab').next().not($panel).slideUp();
        $panel.slideToggle();
    });


    // ---------------------------------------
    // Name click to show appointment table
    $('.dropname').click(function () {
        var username = $(this).attr('id').replace('droptable_', '');
        $('.appointments_wrapper').each(function () {
            if ($(this).attr('id') !== username) {
                $(this).addClass('hidden');
            }
        });
        $('#' + username).removeClass('hidden');
        fix_height()

        console.log('userdest')
        console.log(user_dest)

        var rows = $('.projectlist tbody tr');







        // Find the object in the array with a "user" property equal to the clicked username
        const desiredUser = user_dest.find(obj => obj.user === username);

        // If a matching object was found, loop over the rows and update the distance value for the matching city
        if (desiredUser) {
            // Loop over all rows
            $('.projectlist tbody tr').each(function (i) {
                // Get the city value in the current row
                var city = $(this).find('td:eq(0)').text().trim();

                // Loop over the properties of the "citys" object for the clicked user
                for (const cityName in desiredUser.citys) {
                    // If the property name in the "citys" object contains the city name from the current row, update the distance value in the corresponding table cell
                    if (cityName.includes(city)) {
                        var distance = Math.round(desiredUser.citys[cityName].distance);
                        var duration = desiredUser.citys[cityName].duration;
                        var $td = $(this).find('td:eq(4)');
                        $td.text(duration);
                        $td.attr('title', 'Entfernung: ' + distance + 'km');
                        break;
                    }
                }
            });
        }




    });

    // ----------------------------------------
    // dropzone handle
    $('.droppable').droppable({
        accept: '.draggable',
        drop: function (event, ui) {
            var city = ui.draggable.text();
            var table = ui.draggable.data('table');
            var number = ui.draggable.next().text(); // Get the text of the next td element
            // $(this).append('<span class="dropped ' + table + '">' + city + '&nbsp;' + number + '</span>');
            var exists = false;
            $(this).find('.dropped').each(function () {
                if ($(this).text().includes(city)) { // Check if the city name already exists in the droppable zone
                    exists = true;
                    return false; // Exit the loop if the city name is found
                }
            });
            if (!exists) {
                $(this).append('<span class="dropped ' + table + '">' + city + '&nbsp;' + number + '</span>');
                $(this).removeClass('addzone'); // Remove the class to remove the ability to add day events
            }
        }
    });








    $('.draggable').draggable({
        helper: function () {
            // create a new helper element
            return $('<div>', {
                text: $(this).text()
            }).appendTo('body');
        },
        appendTo: 'body', // append the helper element to the body element
        revert: 'invalid'
    });


    // ----------------------------------------
    // doubleclick remove of the dropped items. Add addzone for the day event handle
    $(document).on('dblclick', '.dropped', function () {
        var droppableZone = $(this).parent('.droppable');
        $(this).remove();
        if (droppableZone.children().length === 0) {
            droppableZone.addClass('addzone');
        }
        droppableZone.removeClass('dayevent_cell');
    });






    var weekInput = $('#week');
    var prevWeekButton = $('#prevWeekButton');
    var nextWeekButton = $('#nextWeekButton');
    var dateRange = $('#dateRange');
    weekInput.trigger('change');

    // Set the initial date range display
    var currentWeek = weekInput.val();
    var startDate = getStartDate(currentWeek);
    var endDate = getEndDate(currentWeek);
    dateRange.text(startDate + ' - ' + endDate);
    load_hbg_data(currentWeek);

    // Update the date range display whenever the week input field value changes
    weekInput.on('change', function () {
        var currentWeek = weekInput.val();
        var startDate = getStartDate(currentWeek);
        var endDate = getEndDate(currentWeek);
        dateRange.text(startDate + ' - ' + endDate);
        updateColumns(startDate, endDate); //  updateColumns to match the date
        load_hbg_data(currentWeek);
        checkArrayExists()
        get_weekplan(); // load data from db to the week table
    });

    prevWeekButton.on('click', function () {
        var currentWeek = weekInput.val();
        var prevWeek = decrementWeek(currentWeek);
        weekInput.val(prevWeek).trigger('change');

    });

    nextWeekButton.on('click', function () {
        var currentWeek = weekInput.val();
        var nextWeek = incrementWeek(currentWeek);
        weekInput.val(nextWeek).trigger('change');

    });


    $('#saveall').on('click', function () {
        save_plan();


    })


    // fit page height
    var height = $('#modulwrapper').height();
    height = height + 400;
    $('.body_content').css('height', height + 'px');





    fix_height(); // fix toptable height to prevent jumps





    // Add day event like urlaub, krank to the cell
    function add_day_event(event, target) {
        console.log(target)
        target.addClass('dayevent_cell')
        target.append('<span class="dayevent dropped">' + event.charAt(0).toUpperCase() + event.slice(1) + '</span>');
        target.removeClass('addzone'); // Remove the class to remove the ability to add day events
    }

    // calc the toptable height to prevent jumps
    function fix_height() {
        var $elements = $('.appointments_wrapper');
        var maxHeight = 0;
        $elements.each(function () {
            var height = $(this).height();
            //console.log('height: ' + height)
            if (height > maxHeight) {
                maxHeight = height;
            }
        });
        $('#toptable').height(maxHeight);
    }


    function incrementWeek(weekString) {
        var year = parseInt(weekString.substring(0, 4));
        var week = parseInt(weekString.substring(6));
        if (week == 52) {
            year++;
            week = 1;
        } else {
            week++;
        }
        return year + '-W' + week.toString().padStart(2, '0');
    }

    function decrementWeek(weekString) {
        var year = parseInt(weekString.substring(0, 4));
        var week = parseInt(weekString.substring(6));
        if (week == 1) {
            year--;
            week = 52;
        } else {
            week--;
        }
        return year + '-W' + week.toString().padStart(2, '0');
    }

    function getStartDate(weekString) {
        var year = parseInt(weekString.substring(0, 4));
        var week = parseInt(weekString.substring(6));
        var jan1 = new Date(year, 0, 1);
        var dayOffset = (jan1.getDay() + 6) % 7;
        var startDate = new Date(year, 0, 1 + (7 - dayOffset));
        startDate.setDate(startDate.getDate() + (week - 1) * 7);
        return ('0' + startDate.getDate()).slice(-2) + '.' + ('0' + (startDate.getMonth() + 1)).slice(-2) + '.' + startDate.getFullYear();
    }

    function getEndDate(weekString) {
        var year = parseInt(weekString.substring(0, 4));
        var week = parseInt(weekString.substring(6));
        var jan1 = new Date(year, 0, 1);
        var dayOffset = (jan1.getDay() + 6) % 7;
        var endDate = new Date(year, 0, 1 + (7 - dayOffset));
        endDate.setDate(endDate.getDate() + (week - 1) * 7 + 6);
        return ('0' + endDate.getDate()).slice(-2) + '.' + ('0' + (endDate.getMonth() + 1)).slice(-2) + '.' + endDate.getFullYear();
    }



    function get_weekplan() {
        var currentWeek = $('#week').val();
        console.log('currentWeek' + currentWeek)

        if (weekplan[currentWeek]) {
            console.log('------ this week ' + currentWeek + ' already exist ---- parse')
            console.log(weekplan)
            // Data for the current week is already present in weekplan, so call parse_weekplan with the existing data
            parse_weekplan(weekplan[currentWeek]);
        } else {


            // Data for the current week is not present in weekplan, so make an AJAX request to get it
            $.ajax({
                method: "POST",
                url: "view/load/test_load.php",
                data: {
                    func: "load_weekplan",
                    week: currentWeek
                },
            }).done(function (response) {
                //console.log('@@@@@@@@'+response);
                var data = JSON.parse(response)

                weekplan[currentWeek] = data;
                console.log('------ this week ' + currentWeek + ' does not exist ---- load')
                console.log(weekplan)
                parse_weekplan(data);
            });
        }
    }

    function parse_weekplan(data) {

        $('.tableday_row').each(function () {
            const row = $(this);
            row.find('.tableday').html(null);
        });

        for (let i = 0; i < data.length; i++) {
            const {
                username,
                week,
                montag,
                dienstag,
                mittwoch,
                donnerstag,
                freitag,
                samstag
            } = data[i];
            if (username) {
                console.log(`${username} - ${week} - ${montag} - ${dienstag} - ${mittwoch} - ${donnerstag} - ${freitag}`);
                const row = $('#droprow_' + username);
                row.find('.col-2:nth-child(2)').html(getSpans(montag));
                row.find('.col-2:nth-child(3)').html(getSpans(dienstag));
                row.find('.col-2:nth-child(4)').html(getSpans(mittwoch));
                row.find('.col-2:nth-child(5)').html(getSpans(donnerstag));
                row.find('.col-2:nth-child(6)').html(getSpans(freitag));
                row.find('.col-2:nth-child(7)').html(getSpans(samstag));

            }
        }
        check_dayeventClass();
    }




    //====================================================================================
    // loop over all dropcells and add / remove the correct classes
    function check_dayeventClass() {
        $('.tableday_row').each(function () {
            $(this).find('.col-2').each(function () {
                // check if col-2 contains a span
                $(this).removeClass('addzone dayevent_cell');

                if ($(this).find('span').length > 0) {

                    // check if the span has class "dayevent"
                    if ($(this).find('span.dayevent').length > 0) {
                        $(this).addClass('dayevent_cell');
                        $(this).removeClass('addzone');
                    }

                } else {
                    $(this).addClass('addzone');
                }
            });
        });
    }



    // -------------------------------------------------------------------------
    // function to split the events passed from the db to the target spans
    function getSpans(dayValue) {
        if (!dayValue) return ''; // return empty string if dayValue is undefined or empty

        let spans = [];

        if (dayValue === "Urlaub" || dayValue === "Krank" || dayValue === "Survey" || dayValue === "StreetNav" || dayValue === "CallCenter") {
            const span = `<span class="dayevent dropped">${dayValue.trim()}</span>`;
            spans.push(span);
        } else if (dayValue.includes(';')) {
            const values = dayValue.split(';'); // split values by semicolon

            values.forEach(value => {
                const span = getSpans(value.trim());
                spans.push(span);
            });
        } else {
            let className = 'dropped';

            Object.keys(projectlist).forEach(projectName => {
                const projectCities = projectlist[projectName];

                if (projectCities.includes(dayValue)) {
                    className += ` ${projectName.charAt(0).toUpperCase() + projectName.slice(1)}`;
                }
            });

            const span = `<span class="${className}">${dayValue.trim()}</span>`;
            spans.push(span);
        }

        return spans.join(''); // join the spans together into a string and return it
    }












    function save_plan() {
        console.log('saved');
        const weekDate = $('#week').val();
        weekplan[weekDate] = undefined;

        let data = {};

        $('.tableday_row').each(function () {
            const username = $(this).attr('id').replace('droprow_', '');
            const children = $(this).children();

            if (!data[username]) {
                data[username] = {};
            }

            children.not(':first-child').each(function () {
                const dayIndex = $(this).index() - 1;
                const items = [];
                const spans = $(this).find('span');

                spans.each(function () {
                    const spanText = $(this).text();
                    const regex = /^([^0-9]*)/; // Match any characters that are not digits at the start of the string
                    const match = regex.exec(spanText);
                    const cityName = match[1]; // Extract only the city name
                    items.push(cityName);
                });

                if (!data[username][dayIndex]) {
                    data[username][dayIndex] = {
                        spans: []
                    };
                }

                data[username][dayIndex].spans = items;
            });
        });




        console.log('plandata')
        console.log(data);

        $.ajax({
            method: "POST",
            url: "view/load/test_load.php",
            data: {
                func: "safe_weekplan",
                week: weekDate,
                data: JSON.stringify(data),
            },
        }).done(function (response) {
            console.log('response')
            console.log(response)

        });





    }





    function get_userlist() {
        var usernames = [];
        $(".dropname").each(function () {
            var text = $(this).text();
            var parts = text.split(" ");
            var name = parts[0];
            usernames.push(name);
        });

        return usernames;
    }








    function load_hbg_data(date) {
        if (hbgDataCache[date]) {
            console.log('Data already loaded from cache.');
            console.log(hbgDataCache[date]);
            updateHtml(hbgDataCache[date]);
        } else {
            $.ajax({
                method: "POST",
                url: "view/load/test_load.php",
                data: {
                    func: "load_hbg_data",
                    date: date,
                },
            }).done(function (response) {
                //console.log(response);
                var decodedResponse = JSON.parse(response);
                hbgDataCache[date] = decodedResponse;
                updateHtml(decodedResponse);
            });
        }
    }

    function updateHtml(data) {
        var currentWeek = $('#week').val();
        var startDate = getStartDate(currentWeek);
        $('.dropname').each(function () {
            var name = $(this).attr('id').replace('droptable_', '');
            var count = 0;
            if (data[name]) {
                count = data[name]['total'] || data[name];
            }
            $(this).find('.plaininfo').text(count);
            $(this).attr('data-count', count);

        });


        // update the element tables to match the present day
        // set the id of each table to the weekday in the format 2023-03-14
        var dateParts = startDate.split('.');
        var year = dateParts[2];
        var month = dateParts[1];
        var day = dateParts[0];
        var dateFormatted = year + '-' + month + '-' + day;
        var date = new Date(year, month - 1, day); // Note: month is 0-indexed in Date object
        $('.appointments_wrapper').each(function () {
            var tables = $(this).find('table.eventelements');
            tables.each(function (i) {
                var tableDate = new Date(date.getTime());
                tableDate.setDate(date.getDate() + i);
                var tableDateFormatted = formatDate(tableDate);
                var spanDateFormatted = ('0' + tableDate.getDate()).slice(-2) + '.' + ('0' + (tableDate.getMonth() + 1)).slice(-2) + '.' + ('' + tableDate.getFullYear()).slice(-2);
                $(this).attr('id', (i === 0) ? dateFormatted : tableDateFormatted); // Set the ID based on the index
                $(this).parent().find('.weekday_info').text(spanDateFormatted);
            });
        });


        const dateRegExp = /^\d{4}-\d{2}-\d{2}$/;
        $('.appointments_wrapper').each(function () {
            var parent = this;
            var name = $(this).attr('id');
            var tables = $(this).find('table.eventelements');
            tables.empty();
            if (data[name]) {
                // console.log('--------------------   name: ' + name)
                for (let key in data[name]) {
                    if (dateRegExp.test(key)) { // key represents the date e.g. 2023-04-22
                        data[name][key].forEach(function (appointment) {
                            const [time, ...locationParts] = appointment.split(' ');
                            const location = locationParts.join(' ');
                            var timeCell = $('<td>').text(time);
                            var locationCell = $('<td>').text(location);
                            var row = $('<tr class="draggable_appt">');
                            row.append(timeCell);
                            row.append(locationCell);

                            // find the table with matching id and append the row
                            var table = tables.filter('[id="' + key + '"]');
                            table.append(row);
                        });
                    }
                }


            }

        });


        //---------------------------
        // loop over the tables to count the rows and update the daily count
        $('.eventelements_wrapper').each(function () {
            var rowCount = $(this).find('.eventelements tr').length;
            $(this).find('.weekday_infocount').text(rowCount)
        });
        //---------------------------
        // loop over all table counts to update the total of the table
        $('.appointments_wrapper').each(function () {
            var counter = 0;
            $(this).find('.weekday_infocount').each(function () {
                counter += parseInt($(this).text())
            });
            $(this).find('.user_week_totals').text(counter)
            // console.log('count:' + counter)
        });
        fix_height(); // fix toptable height to prevent jumps






        // ----------------------------------------
        // Loop over all tables to set them into the past if neccessary
        var currentDate = new Date();
        var yesterdayDate = new Date();
        yesterdayDate.setDate(currentDate.getDate() - 1);

        $(".eventelements").each(function () {
            var tableId = $(this).attr("id");
            var tableDate = new Date(tableId);
            if (tableDate < yesterdayDate) {
                $(this).addClass("past");
            } else {
                $(this).removeClass("past");
            }
        });

    }




    function formatDate(date) {
        var year = date.getFullYear();
        var month = ('0' + (date.getMonth() + 1)).slice(-2); // Add leading zero if necessary
        var day = ('0' + date.getDate()).slice(-2); // Add leading zero if necessary
        return year + '-' + month + '-' + day;
    }

    function updateColumns(startDate, endDate) {
        // Update the columns based on the selected date range
        var currentDate = new Date(startDate); // startDate === '20.03.2023'
        var daysOfWeek = ['Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'];
        $('#columnHeaders .col').each(function (index, element) {
            if (index > 0) { // Skip the first column (Name)
                // increment the startDate by index 
                let dateString = startDate;
                let parts = dateString.split(".");
                let date = new Date(parts[2], parts[1] - 1, parts[0]);
                date.setDate(date.getDate() + index - 1); // Increment the date by one day
                let day = date.getDate().toString().padStart(2, '0'); // Convert the day to a string and add a leading zero if necessary
                let month = (date.getMonth() + 1).toString().padStart(2, '0'); // Convert the month to a string and add a leading zero if necessary
                let newDateString = `${day}.${month}.`; // Convert the date back to a string with only day and month
                //console.log(newDateString); // Output: "04.04"
                // update the col
                let colString = daysOfWeek[index - 1] + ' ' + newDateString
                $(this).text(colString)
            }
        });


    }





    var plandata = []; // declare global

    function checkArrayExists() {
        const weekDate = $('#week').val().trim();
        const username = 'user1'; // replace with the username you want to retrieve data for

        // retrieve saved data for the given week and user
        const savedData = plandata[weekDate] && plandata[weekDate][username] || {};

        if (Object.keys(savedData).length > 0) {
            console.log('Data already present for ' + username + ' in week ' + weekDate);

            // loop through each row and cell to replace the cell contents with the saved data
            $('.tableday_row#droprow_' + username).children().not(':first-child').each(function () {
                const childIndex = $(this).index() - 1;
                const spans = $(this).find('span');
                const savedSpanTexts = savedData[childIndex] || [];
                spans.each(function (i) {
                    const savedSpanText = savedSpanTexts[i] || '';
                    $(this).text(savedSpanText);
                });
            });
        } else {
            console.log('No data present for ' + username + ' in week ' + weekDate);

            // loop through each row and cell to remove the cell contents
            $('.tableday_row#droprow_' + username).children().not(':first-child').each(function () {
                const spans = $(this).find('span');
                spans.each(function () {
                    $(this).text('');
                });
            });
        }
    }



}); // end of document ready