<?php

/**
 * Return a connection to the database using PHP PDO
 */
function getConnection() {
  $dbhost = "localhost";
  $dbuser = "boardAdmin";
  $dbpass = "boardAdmin";
  $dbname = "scrumboard";
  $dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  return $dbh;
}

/**
 * Return the result of an api-request as a json-object
 * @param objct $data The data which should returned to the client
 * @param string $type The type of the api-response [success, error, info]
 * @param string $title An addition title which can display in an alert on the client side
 * @param string $description An addition description which can display in an alert on the client side
 */
function returnResult($data, $type = 'info', $title = '', $description = '') {
  // Build return Object and return the obejct as a json-object
  $returnObject = array('Type' => $type, 'Title' => $title, 'Description' => $description, 'Data' => $data);
  echo json_encode($returnObject);
}

/**
 * Check if a string starts with the given substring
 * @param string $haystack The string in which should searched
 * @param string $needle The string which should searched in the other string
 */
function startsWith($haystack, $needle) {
  return $needle === "" || strpos($haystack, $needle) === 0;
}

/**
 * Check if a string ends with the given substring
 * @param string $haystack The string in which should searched
 * @param string $needle The string which should searched in the other string
 */
function endsWith($haystack, $needle) {
  return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
}

function apiGetObjects($tablename) {
  $sql = "SELECT * FROM " . $tablename;
  try {
    $dbcon = getConnection();
    $stmt = $dbcon->prepare($sql);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $dbcon = null;
    returnResult($results, "success");
  } catch(PDOException $e) {
    returnResult($e, "error", "Error", ucfirst($tablename) . "s could not getted because of an error");
  }
}

function apiGetObject($tablename, $id) {
  $sql = "SELECT * FROM " . $tablename . " WHERE Id = :id";
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
      returnResult($result, "error", "Error", "No " . lcfirst($tablename) ." was found with the given id");
    }
  } catch(PDOException $e) {
    returnResult($e, "error", "Error", "The data of the " . lcfirst($tablename) ." could not getted because of an error");
  }
}

function apiDeleteObject($tablename, $id) {
  $sql = "DELETE FROM " . $tablename . " WHERE Id = :id";
  try {
    $dbcon = getConnection();
    $stmt = $dbcon->prepare($sql);
    $stmt->bindParam("id", $id);
    $stmt->execute();
    $result = ($stmt->rowCount() == 1);
    $dbcon = null;
    returnResult($result, "success", ucfirst($tablename) . " deleted", "The task was successfully deleted");
  } catch(PDOException $e) {
    returnResult($e, "error", "Error", "The " . lcfirst($tablename) . " could not delete because of an error");
  }
}

?>