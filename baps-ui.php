<?php

# https://stackoverflow.com/questions/293601/php-and-concurrent-file-access
# http://www.php.net/flock
# https://www.w3schools.com/js/js_json_php.asp

// INSERT INTO `timeslots_applicants` (`id`, `applicant_id`, `timeslot_id`, `timestamp`) VALUES (NULL, '3', '1', CURRENT_TIMESTAMP)
// SELECT * FROM applicants INNER JOIN timeslots_applicants ON timeslots_applicants.applicant_id=applicants.id ORDER BY timeslots_applicants.timestamp ASC
// SELECT * FROM wp_baps_applicants INNER JOIN wp_baps_timeslots_applicants ON wp_baps_timeslots_applicants.applicant_id=wp_baps_applicants.id ORDER BY wp_baps_timeslots_applicants.timestamp ASC
// SELECT wp_baps_timeslots_companies.timeslot_id FROM wp_baps_timeslots_companies INNER JOIN wp_baps_companies ON wp_baps_timeslots_companies.company_id=wp_baps_companies.id WHERE wp_baps_companies.id = \ändern\ ORDER BY wp_baps_timeslots_companies.timeslot_id ASC


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
    $msg = "First line of text\nSecond line of text";
    $ret = mail("franz.papst@gmail.com","Test",$msg);
    $converted_res = $ret ? 'true' : 'false';
    echo "Sent mail: ", $converted_res;

    if (!$ret) {
        echo error_get_last()['message'];
    }

    forms();
}

// TODO: Warteliste verbessern
// TODO: Email
// TODO: Backend für Timeslots von Firmen

function forms() {
    global $wpdb;
    $wp = $wpdb->prefix;

    $app_slot_ids = array();

    if (!empty($_POST)) {
        $full_name = $_POST["full_name"];
        $email = $_POST["email"];
        $student_id = $_POST["student_id"];
        $study_field = $_POST["study_field"];
        $semester = $_POST["semester"];
        
        foreach ($_POST as $key=>$value) {
            if (substr( $key, 0, 4 ) === "com_" && $value != "") {
                array_push($app_slot_ids, $value);
            }
        }

        $uuid = $_GET["id"];

        upload_file($uuid);

        $query = "INSERT INTO {$wp}baps_applicants (id, name, email, student_id, uuid, study_field, semester) 
            VALUES (NULL, '$full_name', '$email', '$student_id', '$uuid', '$study_field', '$semester')
            ON DUPLICATE KEY UPDATE name = '$full_name', email = '$email', study_field = '$study_field', semester = '$semester';";
        $wpdb->query($query);

        $query = "SELECT id FROM {$wp}baps_applicants WHERE uuid = '{$uuid}'";
        $applicant_id = $wpdb->get_var($query)[0];

// TODO: add company_id

        foreach ($app_slot_ids as $slot_id) {
            $query = "INSERT INTO {$wp}baps_timeslots_applicants (id, applicant_id, timeslot_id, timestamp) VALUES (NULL, '{$applicant_id}','{$slot_id}', CURRENT_TIMESTAMP)";
            $wpdb->query($query);
        }

    } else {
        $full_name = "";
        $email = "";
        $student_id = "";
        $study_field = "";
        $semester = "";
    }

    if (isset($_GET["id"])) {
        $uuid = $_GET["id"];

        $query = "SELECT * FROM {$wp}baps_applicants WHERE uuid = '{$uuid}'";
        $filled = $wpdb->get_results($query)[0];

        $full_name = $filled->name;
        $email = $filled->email;
        $student_id = $filled->student_id;
        $study_field = $filled->study_field;
        $semester = $filled->semester;
    }
    else
        $uuid = substr(md5(rand(1000, 100000)."+".rand(0, 100000)."+".rand(0, 1000000)), 0, 32);

//TODO: make list dynamic, add file-upload check
    $script = "<script>
        function check() {
            var form = document.forms['form'];
            var fields = ['full_name', 'email', 'student_id', 'study_field', 'semester'];

            for (i=0; i<fields.length; i++) {
                value = document.getElementsByName(fields[i])[0].value;
                if (!value || !value.trim().length) {
                    alert('Bitte fülle alle Felder aus.');
                    return false;
                }
            }
        };
        </script>";

    $html = $script;
    $html = $html.sprintf('<form action="?id=%s" method="post" name="form" id="baps-form" enctype="multipart/form-data" onsubmit="return check()">', $uuid);
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
            $arr = array ("id" => ($j * $num_timeslots) + $i, "free" => 2, "blocked" => FALSE);
            $timetable[$i][$j] = $arr;
        }
    }

    //$query = "SELECT {$wp}baps_timeslots_applicants.applicant_id, {$wp}baps_timeslots_applicants.company_id, {$wp}baps_timeslots_applicants.timeslot_id FROM wp_baps_applicants INNER JOIN wp_baps_timeslots_applicants ON wp_baps_timeslots_applicants.applicant_id=wp_baps_applicants.id ORDER BY {$wp}baps_timeslots_applicants.timestamp ASC";
    $query = "SELECT {$wp}baps_timeslots_applicants.applicant_id, {$wp}baps_timeslots_applicants.timeslot_id FROM wp_baps_applicants INNER JOIN wp_baps_timeslots_applicants ON wp_baps_timeslots_applicants.applicant_id=wp_baps_applicants.id ORDER BY {$wp}baps_timeslots_applicants.timestamp ASC";
    $response = $wpdb->get_results($query);
   
    $ts_query = "SELECT id, slot from {$wp}baps_timeslots";
    $ts_response = $wpdb->get_results($ts_query);

    $c_query = "SELECT id, name from {$wp}baps_companies";
    $c_response = $wpdb->get_results($c_query);

    $selectors = '<div style="display:block;">';
    foreach ($c_response as $c_r) {
        $selectors = $selectors.$c_r->name.'<select name="com_'.$c_r->name.'">';
        $selectors = $selectors."<option></option>";
        foreach ($ts_response as $ts_r) {
            $timeslot = $ts_r->id - 1;
            $company_id = $c_r->id - 1;

            $free_slots = $timetable[$timeslot][$company_id]["free"];
            $app_slot_id = $timeslot + ($company_id * $num_timeslots);

            $query_cs = "SELECT {$wp}baps_timeslots_companies.timeslot_id FROM {$wp}baps_timeslots_companies INNER JOIN {$wp}baps_companies
                ON {$wp}baps_timeslots_companies.company_id={$wp}baps_companies.id WHERE {$wp}baps_companies.id = %d
                ORDER BY {$wp}baps_timeslots_companies.timeslot_id ASC";
            $query_cs = sprintf($query_cs, $company_id+1);
            $available_timeslots = $wpdb->get_results($query_cs);

            //TODO: könnte man eleganter lösen
            $found = FALSE;
            foreach ($available_timeslots as $avl) {
                if ($avl->timeslot_id == $timeslot) {
                    $found = TRUE;
                }
            }
            if (!$found)
                continue;

            foreach ($response as $k => $row) {
                if ($row->timeslot_id == $app_slot_id) {
                    unset($response[$k]);
                    $free_slots--;
                }
            }

            if (in_array($app_slot_id, $app_slot_ids))
                $selected = "selected";
            else
                $selected = "";
            
            $selectors = $selectors.sprintf("<option value='%d' %s>%s (%d)</option>", $app_slot_id, $selected, $ts_r->slot, $free_slots);
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
                $app_slot_id = ($ts_r->id) + ($c_r->id * $num_timeslots);
                /$table_rows = $table_rows.sprintf("<td id=%d>%s</td>", $app_slot_id, $ts_r->slot);
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
    if(filter_input(INPUT_POST, "submit", FILTER_SANITIZE_STRING)){
        $ext = pathinfo($_FILES["cv"]['name'], PATHINFO_EXTENSION);
        move_uploaded_file($_FILES["cv"]["tmp_name"], BAPS_UPLOAD_DIR . $filename . "." . $ext);
    }
}

?>
