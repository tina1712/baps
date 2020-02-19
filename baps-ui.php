<?php

# https://stackoverflow.com/questions/293601/php-and-concurrent-file-access
# http://www.php.net/flock
# https://www.w3schools.com/js/js_json_php.asp

// INSERT INTO `timeslots_applicants` (`id`, `applicant_id`, `timeslot_id`, `timestamp`) VALUES (NULL, '3', '1', CURRENT_TIMESTAMP)
// SELECT * FROM applicants INNER JOIN timeslots_applicants ON timeslots_applicants.applicant_id=applicants.id ORDER BY timeslots_applicants.timestamp ASC
// SELECT * FROM wp_baps_applicants INNER JOIN wp_baps_timeslots_applicants ON wp_baps_timeslots_applicants.applicant_id=wp_baps_applicants.id ORDER BY wp_baps_timeslots_applicants.timestamp ASC

/*
Firmen in Datenbank spielen
Timeslots zu Firmen zuordnen

1. Schauen ob UUID von Link existiert (vorhandene Daten ändern):
    Werte abfragen und anzeigen
2. Wenn Werte in $_POST (neue Werte eingespielt / update):
    Werte in Datenbank einspielen
3. Wenn keine Werte vorhanden sind (neue Anmeldung):
    Leeres Formular anzeigen

*/

add_shortcode("baps", "baps_application_page");

define("BAPS_UPLOAD_DIR", dirname(__FILE__) . "/uploads/");

function baps_application_page() {
    forms();
}

function forms() {
    global $wpdb;
    $wp = $wpdb->prefix;

    $full_name = array_key_exists("full_name", $_POST) ? $_POST["full_name"] : "";
    $email = array_key_exists("email", $_POST) ? $_POST : "";
    $student_id = array_key_exists("student_id", $_POST) ? $_POST["student_id"] : "";
    $study_field = array_key_exists("study_field", $_POST) ? $_POST["study_field"] : "";
    
    $uid = uniqid();

//    var_dump($_SERVER['REQUEST_URI']);
//    echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']);

    if (isset($_GET["id"]))
        $uuid = $_GET["id"];
    else
        $uuid = substr(md5(rand(1000, 100000)."+".rand(0, 100000)."+".rand(0, 1000000)), 0, 32);

    $html = sprintf('<form action="%s" method="post" id="baps-form" enctype="multipart/form-data">', str_replace( '%7E', '~', $_SERVER['REQUEST_URI']));
    $html = $html.'<div class="baps-row">';
    $html = $html.'<span>Name:</span>';
    $html = $html.sprintf('<input type="text" name="full_name" value="%s"/>', $full_name);
    $html = $html.'</div>';
    $html = $html.'<div class="baps-row">';
    $html = $html.'<span>E-mail</span>';
    $html = $html.sprintf('<input type="text" name="email" value="%s" />', $email);
    $html = $html.'</div>';
    $html = $html.'<div class="baps-row">';
    $html = $html.'<span>Matrikelnummer:</span>';
    $html = $html.sprintf('<input type="text" name="student_id" value="%s" />', $student_id);
    $html = $html.'</div>';
    $html = $html.'<div class="baps-row">';
    $html = $html.'<span>Studienrichtung:</span>';
    $html = $html.sprintf('<select name="study_field" value="%s">', $study_field);

    $query = "SELECT name FROM {$wp}baps_study_fields ORDER BY {$wp}baps_study_fields.id ASC";
    $response = $wpdb->get_results($query);
    foreach ($response as $r)
        $html = $html.sprintf('<option>%s</option>', $r->name);

    $html = $html.'</select>';
    $html = $html.'</div>';
    $html = $html.'<div class="baps-row">';
    $html = $html.'<span>Aktuelles Semester:</span>';
    $html = $html.'<select name="semester">';
    $html = $html.'<option>1-4</option>';
    $html = $html.'<option>5-8</option>';
    $html = $html.'<option>9-12</option>';
    $html = $html.'<option>13+</option>';
    $html = $html.'</select>';
    $html = $html.'</div>';

    $html = $html.'<div class="baps-row">';
    $html = $html.'<span>Lebenslauf hochladen</span>';
    $html = $html.'<input type="file" name="cv" /><br />';
    $html = $html.'</div>';
    $html = $html.'<div class="baps-row">';
    //echo $html;

    $query = "SELECT COUNT(*) FROM {$wp}baps_companies";
    $num_companies = $wpdb->get_var($query);
    $query = "SELECT COUNT(*) FROM {$wp}baps_timeslots";
    $num_timeslots = $wpdb->get_var($query);
    
    $timetable = array();
    for ($i=0; $i<$num_timeslots; $i++) {
        for ($j=0; $j<$num_companies; $j++) {
            $arr = array ("id" => ($j * $num_timeslots) + $i, "free" => 2);
            $timetable[$i][$j] = $arr;
        }
    }

    $query = "SELECT {$wp}baps_timeslots_applicants.applicant_id, {$wp}baps_timeslots_applicants.company_id, {$wp}baps_timeslots_applicants.timeslot_id FROM wp_baps_applicants INNER JOIN wp_baps_timeslots_applicants ON wp_baps_timeslots_applicants.applicant_id=wp_baps_applicants.id ORDER BY {$wp}baps_timeslots_applicants.timestamp ASC";
    $response = $wpdb->get_results($query);
   
    $ts_query = "SELECT id, slot from {$wp}baps_timeslots";
    $ts_response = $wpdb->get_results($ts_query);

    $c_query = "SELECT id, name from {$wp}baps_companies";
    $c_response = $wpdb->get_results($c_query);

    $selectors = '<div style="display:block;">';

    foreach ($c_response as $c_r) {
        $selectors = $selectors."$c_r->name<select>";
        $selectors = $selectors."<option></option>";
        foreach ($ts_response as $ts_r) {
            $slot_id = ($ts_r->id) + ($c_r->id * $num_timeslots);
            $selectors = $selectors.sprintf("<option %d>%s</option>", $slot_id, $ts_r->slot);
        }
        $selectors = $selectors."</select>";
    }
    $selectors = $selectors."</div>";

    $html = $html.$selectors."</div>";

    $html = $html.'<div class="baps-row">';
    $html = $html.'<input type="submit" value="Absenden" name="submit" />';
    $html = $html.'</div>';
    // <!-- Slots und hidden für Warteliste hinzufügen -->
    $html = $html.'</form>';

    echo $html;

    //echo $selectors;


    /* TODO: this is the code for using tables as selection
    $table_header = "";
    $table_rows = "";

    foreach ($ts_response as $ts_r) {
            $table_rows = $table_rows."<tr>";

            $table_header = "<tr>";
            foreach ($c_response as $c_r) {
                $table_header = $table_header.sprintf("<td><b>%s</b></td>", $c_r->name);
                $slot_id = ($ts_r->id) + ($c_r->id * $num_timeslots);
                /$table_rows = $table_rows.sprintf("<td id=%d>%s</td>", $slot_id, $ts_r->slot);
        }
        $table_rows = $table_rows."</tr>";
    }

    $script = '<script src="https://code.jquery.com/jquery-1.8.3.min.js"  type="text/javascript"></script>
    <script type="text/javascript">
    $(document).ready(function(){
        $("#baps-table td").click(function(){
            $(this).addClass("selected").siblings().removeClass("selected");
            var value=$(this).find("td:first").html();
        });
     });
    </script>';

    $css = "<style>
    td {border: 1px #DDD solid; padding: 5px; cursor: pointer;}
    .selected {
        background-color: brown;
        color: #FFF;
    }
    </style>";

    echo $script;
    echo $css;

    $table = '<table id="baps-table">'.$table_header.$table_rows.'</table>';
    echo $table;
    */
}


function upload_file($filename) {
    echo BAPS_UPLOAD_DIR;
    if(filter_input(INPUT_POST, "submit", FILTER_SANITIZE_STRING)){
        $ext = pathinfo($_FILES["cv"]['name'], PATHINFO_EXTENSION);
        move_uploaded_file($_FILES["cv"]["tmp_name"], BAPS_UPLOAD_DIR . $filename . $ext);
    }
}

?>