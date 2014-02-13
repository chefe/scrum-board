<?php

/**
 * Return all available states as a json object
 */
function apiGetStates() {
  $sql = "SELECT * FROM State";
  try {
    $results = array();
    $dbcon = getConnection();
    $stmt = $dbcon->prepare($sql);
    $stmt->execute();
    while ($result = $stmt->fetchObject()) {
      $results[] = $result;
    }
    $dbcon = null;
    returnResult($results, "success");
  } catch(PDOException $e) {
    returnResult($e, "error", "Error", "Could not get the states because of an error");
  }
}

/**
 * Return the state with the given id as a json obejct
 * @param int $id The id of the state to return
 */
function apiGetState($id) {
  $sql = "SELECT * FROM State WHERE Id = :id";
  try {
    $dbcon = getConnection();
    $stmt = $dbcon->prepare($sql);
    $stmt->bindParam("id", $id);
    $stmt->execute();
    $result = $stmt->fetchObject();
    $dbcon = null;
    if ($result) {
      returnResult($result, "success");
    } else {
      returnResult($result, "error", "Error", "No state was found with the given id");
    }
  } catch(PDOException $e) {
    returnResult($e, "error", "Error", "The data of the state could not getted because of an error");
  }
}
?>