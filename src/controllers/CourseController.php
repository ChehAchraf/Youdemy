<?php
namespace App\Controllers;

use App\Models\Auth;
use App\Models\Course;
use App\Models\VideoCourse;

class CourseController {
    private $auth;

    public function __construct() {
        $this->auth = Auth::getInstance();
    }

    public function addCourse() {

        $this->auth->requireRole('teacher');


        $courseData = $_POST;
        if ($courseData['type'] === 'video') {
            $course = new VideoCourse();
        } else {
            $course = new Course();
        }


        $course->setTitle($courseData['title']);
        $course->setDescription($courseData['description']);
        $course->setTags($courseData['tags']);
        
        return $course->add();
    }

    public function displayCourse($id) {

        if (!$this->auth->isLoggedIn()) {
            header('Location: /login.php');
            exit();
        }

        $course = new Course();
        $course->setId($id);
        return $course->display();
    }
}
