<?php

# https://stackoverflow.com/questions/293601/php-and-concurrent-file-access
# http://www.php.net/flock
# https://www.w3schools.com/js/js_json_php.asp

add_shortcode("baps", "baps_application_page");

define("BAPS_UPLOAD_DIR", dirname(__FILE__) . "/uploads/");

function baps_application_page() {
    forms();
}

function forms() {
    $values = $_POST;
    $uid = uniqid();
?>
<form action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" method="post" id="aps-form" enctype="multipart/form-data">
<div class="baps-row">
    <span>Name:</span>
    <input type="text" name="full_name" value="<?=$values['full_name']?>" />
</div>
<div class="baps-row">
    <span>E-mail</span>
    <input type="text" name="email" value="<?=$values['email']?>" />
</div>
<div class="baps-row">
    <span>Matrikelnummer:</span>
    <input type="text" name="student_id" value="<?=$values['student_id']?>" />
</div>
<div class="baps-row">
    <span>Studienrichtung:</span>
    <select name="study_field" value="<?=$values['study_field']?>">
        <option>Architektur</option>
        <option>Bauingenierwesen</option>
        <option>Biomedical Engineering</option>
        <option>Elektrotechnik</option>
        <option>Informatik</option>
        <option>Maschinenbau</option>
        <option>Materialwissenschaften</option>
        <option>Raumplanung</option>
        <option>Technische Chemie</option>
        <option>Technische Mathematik</option>
        <option>Technische Physik</option>
        <option>Verfahrenstechnik</option>
        <option>Vermessung und Geoinformation</option>
        <option>Wirtschaftsinformatik</option>
        <option>Wirtschaftsingenierwesen - Maschinenbau</option>
        <option>Sonstige</option>
    </select>
</div>
<div class="baps-row">
    <span>Lebenslauf hochladen</span>
    <input type="file" name="cv" /><br />
</div>
<div class="baps-row">
    <input type="submit" value="Absenden" name="submit" />
</div>
<!-- Slots und hidden für Warteliste hinzufügen -->
</form>

<?php
upload_file($uid);
}

function upload_file($filename) {
    echo BAPS_UPLOAD_DIR;
    if(filter_input(INPUT_POST, "submit", FILTER_SANITIZE_STRING)){
        $ext = pathinfo($_FILES["cv"]['name'], PATHINFO_EXTENSION);
        move_uploaded_file($_FILES["cv"]["tmp_name"], BAPS_UPLOAD_DIR . $filename . $ext);
    }
}

?>

