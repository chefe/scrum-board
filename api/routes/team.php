<?php

/**
 * Return all available teams as a json object
 */
function apiGetTeams() {
  apiGetObjects("Team");
}

/**
 * Return the team with the given id as a json obejct
 * @param int $id The id of the team to return
 */
function apiGetTeam($id) {
  apiGetObject("Team", $id);
}

/**
 * Return if the team exists
 * @param int $id The id of the team to check
 */
function apiCheckTeam($id) {
  $sql = "SELECT * FROM Team WHERE Id = :id;";
  try {
    $dbcon = getConnection();
    $stmt = $dbcon->prepare($sql);
    $stmt->bindParam("id", $id);
    $stmt->execute();
    return ($stmt->rowCount() == 1);
  } catch(PDOException $e) {
    return false;
  }
}

/**
 * Add the team given in the request to the database
 */
function apiAddTeam() {
  global $app;
  $sql = "INSERT INTO Team (Caption, OwnerEmployeeId) VALUES (:caption, :ownerEmployeeId);";
  try {
    $dbcon = getConnection();
    $stmt = $dbcon->prepare($sql);
    $employeeId = 1;
    $stmt->bindParam("caption", $app->request->params("Caption"));
    $stmt->bindParam("ownerEmployeeId", $employeeId); // TODO Current user
    $stmt->execute();
    $result = ($stmt->rowCount() == 1);
    $dbcon = null;
    returnResult($result, "success", "Add Team", "Successfully created new team");
  } catch(PDOException $e) {
    returnResult($e, "error", "Error", "The team could not created because of an error");
  }
}

/**
 * Update the team with the given id and the values in the request
 * @param int $id The id of the team to update
 */
function apiUpdateTeam($id) {
  global $app;
  $sql = "UPDATE Team SET Caption = :caption WHERE Id = :id";
  try {
    $dbcon = getConnection();
    $stmt = $dbcon->prepare($sql);
    $stmt->bindParam("caption", $app->request->params("Caption"));
    $stmt->bindParam("id", $id);
    $stmt->execute();
    $result = ($stmt->rowCount() == 1);
    $dbcon = null;
    returnResult($result, "success", "Team updated", "The team was successfully updated");
  } catch(PDOException $e) {
    returnResult($e, "error", "Error", "The team could not updated because of an error");
  }
}

/**
 * Delete the team with the given id
 * @param int $id The id of the team to delete
 */
function apiDeleteTeam($id) {
  apiDeleteObject("Team", $id);
}

function apiGetSprintsByTeam($id) {
  if (apiCheckTeam($id)) {
    $sql = "SELECT * FROM Sprint WHERE TeamId = :teamId";
    try {
      $dbcon = getConnection();
      $stmt = $dbcon->prepare($sql);
      $stmt->bindParam("teamId", $id);
      $stmt->execute();
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $dbcon = null;
      returnResult($results, "success");
    } catch(PDOException $e) {
      returnResult($e, "error", "Error", "Sprints could not getted because of an error");
    }
  } else {
    returnResult(array(), "error", "Error", "Sprints could not getted because the specified team does not exists");
  }
}

function apiGetEmployeesByTeam($id) {
  $sql = "SELECT Id, Username, ProfileImage FROM Employee WHERE id IN (SELECT EmployeeId FROM EmployeeTeam WHERE TeamId = :teamId)";
  try {
    $dbcon = getConnection();
    $stmt = $dbcon->prepare($sql);
    $stmt->bindParam("teamId", $id);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $dbcon = null;
    returnResult($results, "success");
  } catch(PDOException $e) {
    returnResult($e, "error", "Error", "Employees could not getted because of an error");
  }
}

function apiAddEmployeesToTeam($id) {
  global $app;
  $sql = "INSERT INTO EmployeeTeam (EmployeeId, TeamId) VALUES (:employeeId, :teamId);";
  try {
    $dbcon = getConnection();
    $stmt = $dbcon->prepare($sql);
    $stmt->bindParam("employeeId", $app->request->params("Id"));
    $stmt->bindParam("teamId", $id);
    $stmt->execute();
    $result = ($stmt->rowCount() == 1);
    $dbcon = null;
    returnResult($result, "success", "Add employee to team", "Successfully added the employee to the team");
  } catch(PDOException $e) {
    returnResult($e, "error", "Error", "The employee could not added to the team because of an error");
  }
}

function apiDeleteEmployeesFromTeam($id, $employeeId) {
  global $app;
  $sql = "DELETE FROM EmployeeTeam WHERE EmployeeId = :employeeId AND TeamId = :teamId;";
  try {
    $dbcon = getConnection();
    $stmt = $dbcon->prepare($sql);
    $stmt->bindParam("employeeId", $employeeId);
    $stmt->bindParam("teamId", $id);
    $stmt->execute();
    $result = ($stmt->rowCount() == 1);
    $dbcon = null;
    returnResult($result, "success", "Remove employee from team", "Successfully removed the employee from the team");
  } catch(PDOException $e) {
    returnResult($e, "error", "Error", "The employee could not removed from the team because of an error");
  }
}

?>