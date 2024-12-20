<?php
require('../template/top.php');
require('../api/discord/bots/admin.php');
require('../template/functions/botathon-funcs.php');

if (isset($_POST)) {
	$name = @$_POST['registrant_name'];
	$email = @$_POST['email'];
	$phone = preg_replace('/[^0-9]/', '', @$_POST['phone_number']);
    $team = @$_POST['team_name'];
	$major = @$_POST['major'];
	$gender = @$_POST['gender'];
	$classification = @$_POST['classification'];
	$diet_restrictions = @$_POST['diet_restrictions'];
	$latex_allergy = @$_POST['latex_allergy'];
	$unteuid = @$_POST['unteuid'];
	$promise = @$_POST['promise'];
	$disability_accommodations = @$_POST['disability_accommodations'];
	
	$valid_genders = array('male', 'female', 'other');
	$valid_classifications = array('freshman', 'sophomore', 'junior', 'senior', 'postgraduate');
	
	do {
		if (strlen($name) < 4) {
			echo 'INVALID_NAME';
			break;
		} else if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('@unt.edu$@i', $email)) {
			echo 'INVALID_EMAIL';
			break;
		} else if (strlen($phone) != 10) {
			echo 'INVALID_PHONE';
			break;
		} else if (!in_array(strtolower($gender), $valid_genders)) {
			echo 'INVALID_GENDER';
			break;
		} else if (!in_array(strtolower($classification), $valid_classifications)) {
			echo 'INVALID_CLASSIFICATION';
			break;
		} else if (strlen($major) < 4) {
			echo 'INVALID_MAJOR';
			break;
		} else if (!preg_match('/^[a-zA-Z]{2,3}\d{4}$/', $unteuid)) {
			echo 'INVALID_EUID';
			break;
		} else if ($promise !== 'on' && BOTATHON_ENFORCE_PROMISE) {
			echo 'INVALID_PROMISE';
			break;
		}

		$q = $db->query('INSERT INTO botathon_registration (
                                   name,
                                   email,
                                   phone,
                                   gender,
                                   major,
                                   classification,
                                   latex_allergy,
                                   diet_restrictions,
                                   unteuid,
                                   team_name,
                                   disability_accommodations,
                                   season
                                   )
		VALUES (
			"' . $db->real_escape_string($name) . '",
			"' . $db->real_escape_string($email) . '",
			"' . $db->real_escape_string($phone) . '",
			"' . $db->real_escape_string($gender) . '",
			"' . $db->real_escape_string($major) . '",
			"' . $db->real_escape_string($classification) . '",
			"' . intval($latex_allergy === 'on') . '",
			"' . $db->real_escape_string($diet_restrictions) . '",
			"' . $db->real_escape_string($unteuid) . '",
			"' . $db->real_escape_string($team) . '",
			"' . $db->real_escape_string($disability_accommodations) . '",
			"' . $db->real_escape_string(BOTATHON_SEASON) . '"
		)
		');
		if ($q) {
			echo 'SUCCESS';
			AdminBot::send_message($name . ' has signed up for bothaton. There are ' . botathon_spots_remaining() . ' spots remaining.');

			$email_send_status = email(
                $email,
                "UNT Robotics Botathon Registration",

                "<div style=\"position: relative;max-width: 100vw;text-align:center;\">" .
                '<img src="cid:untrobotics-email-header">' .

                '	<div></div>' .

                '<div style="text-align: left; width: 500px; display: inline-block;">' .
                "	<p>Dear " . $name . ",</p>" .
                "	<p>Thank you for registering for Botathon Season " . BOTATHON_SEASON . "!</p>" .
                "   <p>If you haven't already, please make sure to join our" .
                "      <a href=\"https://www.untrobotics.com/discord\"><b>Discord server</b></a> as this is where we will post all of our event-day communications and announcements.</p>" .
                "</div>" .

                '	<div></div>' .

                "	<p></p>" .

                '<div style="text-align: left; width: 500px; display: inline-block;">' .
                "	<p>If you need any assistance or have any questions, please reach out in our Discord server or email us at <a href=\"mailto:hello@untrobotics.com\">hello@untrobotics.com</a>.</p>" .
                '</div>' .

                '	<div></div>' .

                '<div style="text-align: left; width: 500px; display: inline-block;">' .
                "	<p>All the best,</p>" .
                "   <p><em>UNT Robotics Leadership</em></p>" .
                '</div>' .

                "</div>",

                "hello@untrobotics.com",
                null,
                [
                    [
                        'content' => base64_encode(file_get_contents(BASE . '/images/unt-robotics-email-header.jpg')),
                        'type' => 'image/jpeg',
                        'filename' => 'unt-robotics-email-header.jpg',
                        'disposition' => 'inline',
                        'content_id' => 'untrobotics-email-header'
                    ]
                ]
            );
		} else {
		    error_log("Failed to add botathon registration: " . $db->error);
			echo 'ERROR';
		}
	} while (false);
}

?>