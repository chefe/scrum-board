<?php

/**
 * Return all available stories as a json object
 */
function apiGetStories() {
  apiGetObjects("Story");
}

/**
 * Return the story with the given id as a json obejct
 * @param int $id The id of the story to return
 */
function apiGetStory($id) {
	apiGetObject("Story", $id);
}

/**
 * Add the story given in the request to the database
 */
function apiAddStory() {
  global $app;
  $sql = "INSERT INTO Story (Caption, Description, StoryPoints, SprintId) VALUES (:caption, :description, :storyPoints, :sprintId);";
  try {
    $dbcon = getConnection();
    $stmt = $dbcon->prepare($sql);
    $stmt->bindParam("caption", $app->request->params("Caption"));
    $stmt->bindParam("description", $app->request->params("Description"));
    $stmt->bindParam("storyPoints", $app->request->params("StoryPoints"));
    $stmt->bindParam("sprintId", $app->request->params("SprintId"));
    $stmt->execute();
    $result = ($stmt->rowCount() == 1);
    $dbcon = null;
    returnResult($result, "success", "Add Story", "Successfully created new story");
  } catch(PDOException $e) {
    returnResult($e, "error", "Error", "The story could not created because of an error");
  }
}

/**
 * Update the story with the given id and the values in the request
 * @param int $id The id of the story to update
 */
function apiUpdateStory($id) {
  global $app;
  $sql = "UPDATE Story SET Caption = :caption, Description = :description, StoryPoints = :storyPoints WHERE Id = :id";
  try {
    $dbcon = getConnection();
    $stmt = $dbcon->prepare($sql);
    $stmt->bindParam("caption", $app->request->params("Caption"));
    $stmt->bindParam("description", $app->request->params("Description"));
    $stmt->bindParam("storyPoints", $app->request->params("StoryPoints"));
    $stmt->bindParam("id", $id);
    $stmt->execute();
    $result = ($stmt->rowCount() == 1);
    $dbcon = null;
    returnResult($result, "success", "Story updated", "The story was successfully updated");
  } catch(PDOException $e) {
    returnResult($e, "error", "Error", "The story could not updated because of an error");
  }
}

/**
 * Delete the story with the given id
 * @param int $id The id of the story to delete
 */
function apiDeleteStory($id) {
  apiDeleteObject("Story", $id);
}

/**
 * Return all available tasks which are assign to the given story as a json object
 * @param int $id The id of the story to which the tasks should assigned
 */
function apiGetTaskByStory($id) {
  $sql = "SELECT * FROM Task WHERE StoryId = :storyId";
  try {
    $dbcon = getConnection();
    $stmt = $dbcon->prepare($sql);
    $stmt->bindParam("storyId", $id);
    $stmt->execute();
    while ($result = $stmt->fetchObject()) {
      $results[] = $result;
    }
    $dbcon = null;
    returnResult($results, "success");
  } catch(PDOException $e) {
    returnResult($e, "error", "Error", "Could not get the task by a given story because of an error");
  }
}

?>