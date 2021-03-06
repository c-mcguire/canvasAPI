<?php
/*
	functions for mycanvas.php
*/
// $_SESSION holds the api key
 session_start();

function callAPI($url) {
	// Main function used to make API requests using cURL
	$curl = curl_init(); // Start cURL
	// Set cURL options
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	// Make the request and then close the connection
	$data = curl_exec($curl);
	curl_close($curl);
	return $data;
}

function getAvatar() {
	// Get avatar information from the canvas api and display it on the dashboard
	$avatarURL = "https://clarkcollege.instructure.com/api/v1/users/self/avatars.json?access_token=".$_SESSION['apiKey'];
	$data = callAPI($avatarURL);
	$data = json_decode($data);
	$avatarString = "<img src=".$data[0]->url." id="."avatar".">";
	echo $avatarString;
}

function getCourse($course) {
	// Get data about a course based on the courses id number
	$coursesURL = "https://clarkcollege.instructure.com/api/v1/courses/".$course.".json?access_token=".$_SESSION['apiKey'];
	$data = callAPI($coursesURL);
	return $data;
}

function getEnrollments() {
	// Use the enrollments api to get data about the classes the user is enrolled in
	//Get the user ID
	$selfURL = "https://clarkcollege.instructure.com/api/v1/users/self/profile.json?access_token=".$_SESSION['apiKey'];
	$selfData = callAPI($selfURL);
	$selfData = json_decode($selfData);
	$id = $selfData->id;
	// Use the user id to get enrollment information
	$enrollmentsURL = "https://clarkcollege.instructure.com/api/v1/users/".$id."/enrollments.json?access_token=".$_SESSION['apiKey']; 
	$data = callAPI($enrollmentsURL);
	return $data;
}

function displayCourseSelect() {
	// Function creates a select box that allows the user to pick the courses they are enrolled in
	$selectString = "<form id="."selectCourseForm"."><select id="."selectCourseSelect".">";
	// Get course id numbers from the enrollments api
	$enrollmentsData = getEnrollments();
	$enrollmentsData = json_decode($enrollmentsData);
	// Use the course id number from the enrollments api to get information about each course
	for($i=0;$i<count($enrollmentsData);$i++) {
		$course = $enrollmentsData[$i]->course_id;
		// Uses the Courses API to get data
		$courseData = getCourse($course);
		$courseData = json_decode($courseData);
		// Builds a string to populate the select box
		$selectString .= "<option value=".$courseData->id.">".$courseData->name."</option>";
	}
	// Add another hard coded option to the select 
	// This will allow me to view a course that I'm nolonger enrolled in
	// The reason for this is to display discussion data. None of my other classes have discussion data
	$selectString .= "<option value=\"1510728\">CTEC 145 - WEB SERVER TECHNOLOGY</option>";
	$selectString .= "</select>";
	$selectString .= "<button id="."selectCourse".">Select Course</button>";
	$selectString .= "</form>";
	echo $selectString;
}

function getCourseInfoAPI($data) {
	// GEts information about a single course
	$course = $data->course;
	$courseInfoURL = "https://clarkcollege.instructure.com/api/v1/courses/".$course.".json?access_token=".$_SESSION['apiKey'];
	$data = callAPI($courseInfoURL);
	echo $data;
}

function getSelf() {
	// Get user infomration
	$selfURL = "https://clarkcollege.instructure.com/api/v1/users/self/profile.json?access_token=".$_SESSION['apiKey'];
	$data = callAPI($selfURL);
	$data = json_decode($data);
	$nameString = "<h2>".$data->name."</h2>";
	echo $nameString;
}

function getAssignments($data) {
	// Get assignments based on the selected course
	$course = $data->course;
	$assignmentsURL = "https://clarkcollege.instructure.com/api/v1/users/self/courses/".$course."/assignments.json?access_token=".$_SESSION['apiKey'];
	$data = callAPI($assignmentsURL);
	echo $data;
}

function getModulesAPI($data) {
	// Get data about the modules based on course id number
	$course = $data->course;
	$modulesURL = "https://clarkcollege.instructure.com/api/v1/courses/".$course."/modules.json?access_token=".$_SESSION['apiKey'];
	$data = callAPI($modulesURL);
	echo $data;
}

function getModuleItemsAPI($data)  {
	// Get the data for each module item 
	$itemsURL = $data->url;
	$itemsURL .= ".json?access_token=".$_SESSION['apiKey'];
	$data = callAPI($itemsURL);
	echo $data;
}

function getModuleItemContentAPI($data) {
	// Get the contents of a single module item
	$moduleItemContentURL = "https://clarkcollege.instructure.com/api/v1/courses/".$data->course."/modules/".$data->moduleID."/items/".$data->itemID.".json?access_token=".$_SESSION['apiKey'];
	$data = callAPI($moduleItemContentURL);
	echo $data;
}

function getGrades($data) {
	// Uses the enrollments api to get the overall score for each class
	$gradesURL = "https://clarkcollege.instructure.com/api/v1/users/4337133/enrollments.json?access_token=".$_SESSION['apiKey']; 
	$data = callAPI($gradesURL);
	//$data =json_encode($data);
	echo $data;
}

function getQuizzesAPI($data) {
	// This function calls the quizzes api
	// For me, it only return "quizzes are locked for the course"
	$course = $data->course;
	$quizzesURL = "https://clarkcollege.instructure.com/api/v1/courses/".$course."/quizzes.json?access_token=".$_SESSION['apiKey'];
	$data = callAPI($quizzesURL);
	echo $data;
}

function getDiscussionsAPI($data) {
	// Get discussion data
	$course = $data->course;
	$discussionsURL = "https://clarkcollege.instructure.com/api/v1/courses/".$course."/discussion_topics.json?access_token=".$_SESSION['apiKey'];
	$data = callAPI($discussionsURL);
	echo $data;
}

function getAssignmentGrade($data) {
	// Get the scores for past assignments
	$course = $data->course;
	$id = $data->assignment;
	$gradeURL = "https://clarkcollege.instructure.com/api/v1/courses/".$course."/assignments/".$id."/submissions/4337133.json?access_token=".$_SESSION['apiKey'];
	$data = callAPI($gradeURL);
	echo $data;
}

// Code to fire specific php function from ajax request
if(isset($_GET["action"])){
	$data = json_encode($_GET);
	$data = json_decode($data);
	$action = $_GET["action"];
	$action($data);
}
?>