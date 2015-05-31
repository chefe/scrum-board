<?php

/*********************************************************************************************************
* SETUP APPLICATION
*********************************************************************************************************/

// Disable error reporting
error_reporting(0);

// Set content type
header("Content-Type: application/json");

// Setup slim application
require 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();

/*********************************************************************************************************
* INCLUDE ROUTE IMPLEMENTATIONS AND HELPERS
*********************************************************************************************************/

// Include helper functions
include("helper.php");

// Include routes
include("routes/task.php");
include("routes/story.php");
include("routes/sprint.php");
include("routes/team.php");
include("routes/state.php");

/*********************************************************************************************************
* DEFINE ROUTES
*********************************************************************************************************/

// Task
$app->get('/Tasks', apiGetTasks);
$app->get('/Task/:id', apiGetTask);
$app->post('/Tasks', apiAddTask);
$app->post('/Task/:id', apiUpdateTask);
$app->post('/Task/:id/State', apiUpdateTaskState);
$app->delete('/Task/:id', apiDeleteTask);

// Stories
$app->get('/Stories', apiGetStories);
$app->get('/Story/:id', apiGetStory);
$app->get('/Story/:id/Tasks', apiGetTaskByStory);
$app->post('/Stories', apiAddStory);
$app->post('/Story/:id', apiUpdateStory);
$app->delete('/Story/:id', apiDeleteStory);

// Sprints
$app->get('/Sprints', apiGetSprints);
$app->get('/Sprint/:id', apiGetSprint);
$app->get('/Sprint/:id/Stories', apiGetStoriesBySprint);
$app->get('/Sprint/:id/Tasks', apiGetTasksBySprint);
$app->get('/Sprint/:id/Complete', apiGetSprintComplete);
$app->post('/Sprints', apiAddSprint);
$app->post('/Sprint/:id', apiUpdateSprint);
$app->delete('/Sprint/:id', apiDeleteSprint);

// Teams
$app->get('/Teams', apiGetTeams);
$app->get('/Team/:id', apiGetTeam);
$app->get('/Team/:id/Sprints', apiGetSprintsByTeam);
$app->post('/Teams', apiAddTeam);
$app->post('/Team/:id', apiUpdateTeam);
$app->delete('/Team/:id', apiDeleteTeam);

// State
$app->get('/States', apiGetStates);
$app->get('/State/:id', apiGetState);

/*********************************************************************************************************
* RUN  THE APPLICATION
*********************************************************************************************************/
$app->run();

?>
