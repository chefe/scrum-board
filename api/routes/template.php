<?php

$templateFolder = "../assets/templates/";
$templateFileExtension = ".tpl";

function apiGetTemplates() {
  // Import global variables
  global $templateFolder, $templateFileExtension;

  // Setup variables
  $templateNames = array();

  // Read files in folder
  if ($handle = opendir($templateFolder)) {
    while (false !== ($file = readdir($handle))) {
      
      // If is a valid template name add it to the array
      if ($file != "." && $file != ".." && endsWith($file, $templateFileExtension)) {
        $filePathParts = split("/", $file);
        $templateNames[] .= substr($filePathParts[count($filePathParts) - 1], 0, (-1 * strlen($templateFileExtension)));
      }
    }

    // Close the folder
    closedir($handle);
  }

  // Return templates names
  returnResult($templateNames, "success");
}

function apiGetTemplate($name) {
  // Import global variables
  global $templateFolder, $templateFileExtension;

  // Build filename
  $fileName = $templateFolder . $name . $templateFileExtension;
  
  // Try to get the template
  try {
    // Get template and return it as a string
    $templateContent = file_get_contents($fileName);
    returnResult($templateContent, "sucess");
  } catch (Exception $e) {
    // Show error message
    returnResult($e, "error", "Error", "Failed to get the template because there exists no template with the given name");
  }
}

?>