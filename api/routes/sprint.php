<?php

/**
 * Return all available sprints as a json object
 */
function apiGetSprints() {
  apiGetObjects("Sprint");
}

/**
 * Return the sprint with the given id as a json obejct
 * @param int $id The id of the sprint to return
 */
function apiGetSprint($id) {
	apiGetObject("Sprint", $id);
}

/**
 * Add the sprint given in the request to the database
 */
function apiAddSprint() {
  global $app;
  $sql = "INSERT INTO Sprint (Caption, TeamId, StartDate, EndDate) VALUES (:caption, :teamId, :startDate, :endDate);";
  try {
    $dbcon = getConnection();
    $stmt = $dbcon->prepare($sql);
    $stmt->bindParam("caption", $app->request->params("Caption"));
    $stmt->bindParam("teamId", $app->request->params("TeamId"));
    $stmt->bindParam("startDate", $app->request->params("StartDate"));
    $stmt->bindParam("endDate", $app->request->params("EndDate"));
    $stmt->execute();
    $result = ($stmt->rowCount() == 1);
    $dbcon = null;
    returnResult($result, "success", "Add Sprint", "Successfully created new sprint");
  } catch(PDOException $e) {
    returnResult($e, "error", "Error", "The sprint could not created because of an error");
  }
}

/**
 * Update the sprint with the given id and the values in the request
 * @param int $id The id of the sprint to update
 */
function apiUpdateSprint($id) {
  global $app;
  $sql = "UPDATE Sprint SET Caption = :caption, StartDate = :startDate, EndDate = :endDate WHERE Id = :id";
  try {
    $dbcon = getConnection();
    $stmt = $dbcon->prepare($sql);
    $stmt->bindParam("caption", $app->request->params("Caption"));
    $stmt->bindParam("startDate", $app->request->params("StartDate"));
    $stmt->bindParam("endDate", $app->request->params("EndDate"));
    $stmt->bindParam("id", $id);
    $stmt->execute();
    $result = ($stmt->rowCount() == 1);
    $dbcon = null;
    returnResult($result, "success", "Sprint updated", "The sprint was successfully updated");
  } catch(PDOException $e) {
    returnResult($e, "error", "Error", "The sprint could not updated because of an error");
  }
}

/**
 * Delete the sprint with the given id
 * @param int $id The id of the sprint to delete
 */
function apiDeleteSprint($id) {
  apiDeleteObject("Sprint", $id);
}

/**
 * Return all available tasks which are assign to the given story as a json object
 * @param int $id The id of the story to which the tasks should assigned
 */
function apiGetStoriesBySprint($id) {
  $sql = "SELECT * FROM Story WHERE SprintId = :sprintId";
  try {
    $dbcon = getConnection();
    $stmt = $dbcon->prepare($sql);
    $stmt->bindParam("sprintId", $id);
    $stmt->execute();
    while ($result = $stmt->fetchObject()) {
      $results[] = $result;
    }
    $dbcon = null;
    returnResult($results, "success");
  } catch(PDOException $e) {
    returnResult($e, "error", "Error", "Could not get the stories by a given sprint because of an error");
  }
}

/**
 * Return all available tasks which are assign to the given sprint as a json object
 * @param int $id The id of the sprint to which the tasks should assigned
 */
function apiGetTasksBySprint($id) {
  $sql = "SELECT * FROM Task WHERE StoryId IN (SELECT Id FROM Story WHERE SprintId = :sprintId)";
  try {
    $dbcon = getConnection();
    $stmt = $dbcon->prepare($sql);
    $stmt->bindParam("sprintId", $id);
    $stmt->execute();
    while ($result = $stmt->fetchObject()) {
      $results[] = $result;
    }
    $dbcon = null;
    returnResult($results, "success");
  } catch(PDOException $e) {
    returnResult($e, "error", "Error", "Could not get the tasks by a given sprint because of an error");
  }
}


function apiGetSprintComplete($id) {
	try {
    // Setup necessary variables
    $dbcon = getConnection();
    $states = array();
    $stories = array();
    $tasks = array();

    // Load states
    $stmt = $dbcon->prepare("SELECT * FROM State");
    $stmt->execute();
    $states = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Load stories
    $stmt = $dbcon->prepare("SELECT * FROM Story WHERE SprintId = :sprintId");
    $stmt->bindParam("sprintId", $id);
    $stmt->execute();
    $stories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Load tasks
    $stmt = $dbcon->prepare("SELECT * FROM Task WHERE StoryId IN (SELECT Id FROM Story WHERE SprintId = :sprintId)");
    $stmt->bindParam("sprintId", $id);
    $stmt->execute();
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare stories
    for ($x = 0; $x < count($stories); $x++) {
      for ($y = 0; $y < count($states); $y++) {
        $stories[$x]['TaskStates'][$y] = $states[$y];
        $stories[$x]['TaskStates'][$y]['Tasks'] = array();
      }
    }

    // Append tasks to story
    foreach ($tasks as $task) {
      // Determinate the state-index
      $found = FALSE;
      $stateIndex = -1;
      for ($x = 0; $found == FALSE && $x < count($states); $x++) {
        if ($states[$x]['Id'] == $task['StateId']) {
          $stateIndex = $x;
          $found = TRUE;
        }
      }

      // Determinate the task index  
      $found = FALSE;  
      for ($x = 0; $found == FALSE && $x < count($stories); $x++) {
        if ($stories[$x]['Id'] == $task['StoryId']) {
          $newIndex = count($stories[$x]['TaskStates'][$stateIndex]['Tasks']);
          $stories[$x]['TaskStates'][$stateIndex]['Tasks'][$newIndex] = $task;
          
          $found = TRUE;
        }
      }
    }

    // Return the storyies with all its recursiv content
    returnResult($stories, "success");
	} catch (PDOException $e) {
    returnResult($e, "error", "Error", "Could not get the sprint data recursiv because of an error");
	}
}

?>