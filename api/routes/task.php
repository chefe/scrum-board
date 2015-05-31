<?php

/**
 * Return all available tasks as a json object
 */
function apiGetTasks() {
  apiGetObjects("Task");
}

/**
 * Return the task with the given id as a json obejct
 * @param int $id The id of the task to return
 */
function apiGetTask($id) {
	apiGetObject("Task", $id);
}

/**
 * Add the task given in the request to the database
 */
function apiAddTask() {
  global $app;
  $sql = "INSERT INTO Task (Caption, Description, StateId, StoryId) VALUES (:caption, :description, 1, :storyId);";
  try {
    $dbcon = getConnection();
    $stmt = $dbcon->prepare($sql);
    $stmt->bindParam("caption", $app->request->params("Caption"));
    $stmt->bindParam("description", $app->request->params("Description"));
    $stmt->bindParam("storyId", $app->request->params("StoryId"));
    $stmt->execute();
    $result = ($stmt->rowCount() == 1);
    $dbcon = null;
    returnResult($result, "success", "Add Task", "Successfully created new task");
  } catch(PDOException $e) {
    returnResult($e, "error", "Error", "The task could not created because of an error");
  }
}

/**
 * Update the task with the given id and the values in the request
 * @param int $id The id of the task to update
 */
function apiUpdateTask($id) {
  global $app;
  $sql = "UPDATE Task SET Caption = :caption, Description = :description WHERE Id = :id";
  try {
    $dbcon = getConnection();
    $stmt = $dbcon->prepare($sql);
    $stmt->bindParam("caption", $app->request->params("Caption"));
    $stmt->bindParam("description", $app->request->params("Description"));
    $stmt->bindParam("id", $id);
    $stmt->execute();
    $result = ($stmt->rowCount() == 1);
    $dbcon = null;
    returnResult($result, "success", "Task updated", "The task was successfully updated");
  } catch(PDOException $e) {
    returnResult($e, "error", "Error", "The task could not updated because of an error");
  }
}

/**
 * Delete the task with the given id
 * @param int $id The id of the task to delete
 */
function apiDeleteTask($id) {
  apiDeleteObject("Task", $id);
}

/**
 * Return all available tasks which are assign to the given story as a json object
 * @param int $id The id of the story to which the tasks should assigned
 */
function apiUpdateTaskState($id) {
  global $app;
  $sql = "UPDATE Task SET StateId = :stateId WHERE Id = :id";
  try {
    $dbcon = getConnection();
    $stmt = $dbcon->prepare($sql);
    $stmt->bindParam("stateId", $app->request->params("StateId"));
    $stmt->bindParam("id", $id);
    $stmt->execute();
    $result = ($stmt->rowCount() == 1);
    $dbcon = null;
    returnResult($result, "success", "TaskState updated", "The state of the task was successfully updated");
  } catch(PDOException $e) {
    returnResult($e, "error", "Error", "The task-state could not updated because of an error");
  }
}

?>