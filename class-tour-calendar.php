<?php
class CruiseTourCalendar {

    public function __construct(){
        $this->base_dir = get_stylesheet_directory_uri() .'/components/tour-calendar';
        $this->vendor_dir = get_stylesheet_directory_uri() .'/vendors';
        $this->tour_dates = $this->get_tour_dates();
        add_action('wp_enqueue_scripts', array($this, 'register_assets'));
        add_shortcode('tour_calendar_popup', array($this, 'shortcode'));
    }

    public function register_assets(){
        $styles = array(
            array(
                'handle' => 'fullcalendar-css',
                'url' => $this->vendor_dir.'/fullcalendar/fullcalendar.min.css',
                'deps' => array(),
                'media' => 'all',
            ),
            array(
                'handle' => 'fullcalendar-print-css',
                'url' => $this->vendor_dir.'/fullcalendar/fullcalendar.print.css',
                'deps' => array('fullcalendar-css'),
                'media' => 'print',
            ),
            array(
                'handle' => 'tour-calendar-css',
                'url' => $this->base_dir. '/css/tour-calendar.css',
                'deps' => array('fullcalendar-css'),
                'media' => 'all',
            ),
        );
        foreach( $styles as $i ) {
            wp_register_style($i['handle'], $i['url'], $i['deps'], null, $i['media']);
        }
        $scripts = array(
            array(
                'handle' => 'moment-js',
                'url' => $this->vendor_dir.'/moment.min.js',
                'deps' => array(),
            ),
            array(
                'handle' => 'fullcalendar-js',
                'url' => $this->vendor_dir.'/fullcalendar/fullcalendar.min.js',
                'deps' => array('jquery'),
            ),
            array(
                'handle' => 'tour-calendar-js',
                'url' => $this->base_dir. '/js/tour-calendar-scripts.js',
                'deps' => array('fullcalendar-js'),
            ),
        );
        foreach( $scripts as $i ) {
            wp_register_script($i['handle'], $i['url'], $i['deps'], null, true);
        }

        wp_localize_script( 'tour-calendar-js', 'tour_calendar_data', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'currency' => ff_get_currency(),
            'currency_symbol' => ff_get_currency_symbol(),
            'currency_rate' => ff_get_currency_rate(),
            'tour_dates' => json_encode($this->tour_dates),
        ));
    }

    public function load_assets(){
        $load_css = array('fullcalendar-css', 'fullcalendar-print-css', 'tour-calendar-css');
        foreach( $load_css as $css ) {
            wp_enqueue_style($css);
        }
        $load_js = array('moment-js', 'fullcalendar-js', 'tour-calendar-js');
        foreach( $load_js as $js ) {
            wp_enqueue_script($js);
        }
    }
    
    public function shortcode(){
        $this->load_assets();
        echo '<div style="display:none;">';
            echo '<div id="cruise-tour-calendar-popup" class="cruise-tour-calendar-container">';
                echo '<div class="cruise-tour-calendar-inner">';
                    echo '<h3>'. __('Tour Calendar', 'chinatours') .'</h3>';
                    echo '<div class="fc-custom-nav">';
                        // Custom year nav
                        echo '<div class="fc-custom-year-nav">';
                            $current_year = date("Y");
                            echo '<span class="nav active" data-year="'. $current_year .'">'. $current_year .'</span>';
                            $next_year = $current_year + 1;
                            echo '<span class="nav" data-year="'. $next_year .'">'. $next_year .'</span>';
                        echo '</div>';

                        // Custom month nav
                        $current_month = date("M");
                        $months = array(
                            'Jan', 'Feb', 'Mar',
                            'Apr', 'May', 'Jun',
                            'Jul', 'Aug', 'Sep',
                            'Oct', 'Nov', 'Dec'
                        );
                        echo '<div class="fc-custom-month-nav">';
                            $i = 0;
                            foreach( $months as $m ) { $i++;
                                $active = ( strtolower($current_month) == strtolower($m) ) ? ' active' : '';
                                echo '<span class="nav'. $active .'" data-month="'. $i .'">'. $m .'</span>';
                            }
                        echo '</div>';
                    echo '</div>';
                    echo '<div id="cruise-tour-calendar" class="tour-calendar"></div>';
                echo '</div>';
            echo '</div>';
        echo '</div>';
        $this->get_tour_dates();
    }

    public function get_tour_dates(){

        $all_tours_dates = array();

        // Get Cruises
        $cruises = get_posts(array(
            'post_type' => 'cruise_tour',
            'showposts' => -1,
            'no_found_rows' => true,
            'fields' => 'ids',
        ));

        foreach( $cruises as $id ) {

            $cruise_name = get_the_title($id);
            $cruise_slug = get_post_field('post_name', $id);
            $cruise_price_downstream = get_field('downstream_lower_level_price', $id);
            $cruise_price_upstream = get_field('upstream_lower_level_price', $id);

            // Get cruise tour dates
            $cruise_tour_dates = get_field('tour_dates', $id);
            if( $cruise_tour_dates ) {

                foreach( $cruise_tour_dates as $d ) {

                    // Get dates between date range
                    $period = new DatePeriod(
                        new DateTime($d['date_from']),
                        new DateInterval('P1D'),
                        new DateTime($d['date_to'])
                    );

                    foreach ($period as $key => $value) {

                        $day_name = strtolower($value->format('l'));
                        $route = $d[$day_name];

                        if( $route == 'none' ) continue;
                        
                        $date = $value->format('Y-m-d');
                        
                        $price = ( $route == 'upstream' ) ? $cruise_price_upstream : $cruise_price_downstream;
                        $price = ff_converted_currency_amount($price);

                        $all_tours_dates[] = array(
                            'title' => $cruise_name,
                            'start' => $date,
                            'price' => $price,
                            //'url' => get_permalink($id),
                            'url' => '/cruise-tour/'. $cruise_slug . '?route='. $route,
                            'route' => $route,
                        );
                        
                    }

                }

            }

        }

        return $all_tours_dates;

    }

}
if( !is_admin() ) $cruise_tour_calendar = new CruiseTourCalendar();