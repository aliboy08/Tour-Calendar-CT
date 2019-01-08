(function($){$(function(){
    
    console.log(tour_calendar_data);

    var currency = tour_calendar_data.currency;

    var tour_dates = [];
    if( typeof tour_calendar_data.tour_dates !== 'undefined' ) {
        tour_dates = JSON.parse(tour_calendar_data.tour_dates);
        console.log(tour_dates);
    }

    var today = new Date();
    $('#cruise-tour-calendar').fullCalendar({
        header: {
            //left: '',
            //left: 'prev,next today',
            //center: 'title',
            //center: 'title',
            //right: ''
            //right: 'month,listMonth'
        },
        defaultDate: today,
        eventLimit: false,
        events: tour_dates,
        eventRender: function(event, element) {
            // Add extra info
            var price = '<span class="tour-price">'+ currency +' '+  event.price +'</span>';
            var route = '';
            if( event.route ) {
                route = '<span class="tour-route">('+ event.route +')</span>';
            }
            element.find('.fc-title').append('<div class="event-extra-info">'+ price + route + '</div>');
        },
        eventAfterAllRender: function(view){
            // Add enquire now to dates without event
            var enquire_now_btn = '<a href="#enquire-now" class="calendar-enquire-btn">Enquire now</a>';
            $('.fc-row .fc-content-skeleton tbody td').each(function(){
                if( !$(this).hasClass('fc-event-container') ) {
                    $(this).append(enquire_now_btn);
                }
            });
            $('.calendar-enquire-btn').on('click', function(e){
                e.preventDefault();
                $('.quick-question-popup-trigger').click();
            });
        }
    });

    // Fix calendar height on popup
    $('.cruise-tour-calender-popup-btn').on('click', function(){
        $('#cruise-tour-calendar').fullCalendar('rerenderEvents');
    });

    // Reposition custom calendar navs
    $('#cruise-tour-calendar .fc-toolbar').after($('.fc-custom-nav'));

    var current_date = new Date();
    var current_year = current_date.getFullYear();
    var current_month = current_date.getMonth() + 1;
    var current_day = current_date.getDate();

    // Custom calendar nav - year
    $('.fc-custom-year-nav .nav').click(function(e){
        e.preventDefault();

        $('.fc-custom-year-nav .nav').removeClass('active');
        $(this).addClass('active');

        var year = parseInt($(this).data('year'));
        var date;

        if( year == current_year ) {
            // If current year, go to current date
            date = year +'-'+ current_month +'-'+ current_day;
        } else {
            // Future year, Jan. 1
            date = year +'-01-01';
        }
        
        date = moment(date, "YYYY-MM-DD");
        $('#cruise-tour-calendar').fullCalendar('gotoDate', date);
    });
    
    // Custom calendar nav - month
    $('.fc-custom-month-nav .nav').click(function(e){
        e.preventDefault();

        $('.fc-custom-month-nav .nav').removeClass('active');
        $(this).addClass('active');

        var this_month = $(this).data('month');
        var current_calendar_date_year = get_current_calendar_date().format('Y');
        var go_to_date = current_calendar_date_year +'-'+ this_month +'-01';
        go_to_date = moment(go_to_date, "YYYY-MM-DD");
        $('#cruise-tour-calendar').fullCalendar('gotoDate', go_to_date);
    });
    
    function get_current_calendar_date(){
        return $("#cruise-tour-calendar").fullCalendar('getDate');
    }
    
})})(jQuery)