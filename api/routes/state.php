<?php

/**
 * Return all available states as a json object
 */
function apiGetStates() {
  apiGetObjects("State");
}

/**
 * Return the state with the given id as a json obejct
 * @param int $id The id of the state to return
 */
function apiGetState($id) {
  apiGetObject("State", $id);
}

?>