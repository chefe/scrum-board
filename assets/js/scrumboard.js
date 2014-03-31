/**************************************************************************************************
* SCRUMBOARD.JS by 
***************************************************************************************************
* Version:      1.0
* Author:       Chefe
* Description:  A small javascript file to enable the scrumboard functionality
**************************************************************************************************/

/**************************************************************************************************
* DOCUMENT-STRUCTURE                                                                               
***************************************************************************************************
* - Drag & Drop
* - Oncommand-Event
* - Gui-Update
* - Helper
* - Autostart
**************************************************************************************************/

/**************************************************************************************************
* LICENCE                                                                              
***************************************************************************************************
* The MIT License (MIT)
* 
* Copyright (c) <year> <copyright holders>
* 
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
* 
* The above copyright notice and this permission notice shall be included in
* all copies or substantial portions of the Software.
* 
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
* THE SOFTWARE.
**************************************************************************************************/



/**************************************************************************************************
* GLOBAL MEMBERS
**************************************************************************************************/

// Member variable to story the id of the current dragged task
var activeDragObjectId = 0;
var activeTeamId = 1;
var activeSprintId = 0;
var templateSprints = null;
var templateScrumboard = null;
var templateStory = null;
var templateTask = null;
var templateMenuEntry = null;
var templateTeamView = null;


/**************************************************************************************************
* DRAG AND DROP FUNCTIONS
**************************************************************************************************/

/**
 * On hover allow dropping of the dragged item
 * @param object ev The current MouseEvent-Object
 */
function allowDrop(ev) {
  // Get the id of the story and check if dropping in this row is allowed
  var storyId = document.getElementById(activeDragObjectId).dataset.scrumStoryId;
  if (storyId == ev.target.dataset.scrumStoryId && ev.target.dataset.scrumStoryId != undefined)
  {
    // Show hover effect and make drop avalibel
    $(ev.target).addClass("active");
    ev.preventDefault();
  }
}

/**
 * On drag an task-obejct 
 * @param  object ev The current MouseEvent-Object
 */
function drag(ev) {
  ev.dataTransfer.setData("Text", ev.target.id);
  activeDragObjectId = ev.target.id;
}

/**
 * On drop an task-object
 * @param  object ev The current MouseEvent-Object
 */
function drop(ev) {
  // Append content
  var element = document.getElementById(activeDragObjectId);
  ev.preventDefault();
  ev.target.appendChild(element);

  // Cleanup
  leaveDrop(ev);  
  activeDragObjectId = 0;

  // Read values for api-call
  var taskId = element.dataset.scrumTaskId;
  var task = { StateId: ev.target.dataset.scrumTaskStateId }

  // Send data to the api and show alert
  getApiResponse('api/Task/' + taskId + '/State', task, showAlert, 'POST');
}

function leaveDrop(ev) {
  $(ev.target).removeClass("active");
}

/**
 * Drag and Drop fix for older browser which don't support HTML5-Drag&Drop
 */
window.onload = function() {
  // Select all draggable nodes
  var dragItems = document.querySelectorAll('[draggable=true]');

  // Setup event-handler on these nodes
  for (var i = 0; i < dragItems.length; i++) {
    dragItems[i].addEventListener('dragstart', drag);
  }
};


/**************************************************************************************************
* ONCOMMAND-EVENT FUNCTIONS
**************************************************************************************************/

/**
 * Show the dialog to add a task to to the story given by the storyId
 * @param int storyId The id of the story on which task should assigned
 */
function addTask(storyId) {
  // Clear placeholders
  $('#addTaskModalDialogCaptionInput').val('');
  $('#addTaskModalDialogDescriptionTextarea').val('');
  $('#addTaskModalDialog').data('storyId', storyId);

  // Show dialog
  $('#addTaskModalDialog').modal();
}

/**
 * Show the edit dialog to edit a task
 * @param int id The id of the task to edit
 */
function editTask(id) {
  // Get current values
  $.getJSON('api/Task/' + id, function(data) {
    if (data.Type == 'success')
    {
      // Setup dialog with data
      $('#editTaskModalDialogCaptionInput').val(data.Data.Caption);
      $('#editTaskModalDialogDescriptionTextarea').val(data.Data.Description);
      $('#editTaskModalDialog').data('taskId', id);

      // Show dialog
      $('#editTaskModalDialog').modal();
    }
    else
    {
      // Show error message
      showAlert({ Type: 'error', Title: 'Task editing failed', Description: 'Failed to load the data of the given task'});
    }
  });
}

/**
 * Delete the task given by the id
 * @param int id The id of the task to delete
 * @param bool confirmed Set if the action is already confirmed
 */
function deleteTask(id, confirmed) {
  // Setup default values
  confirmed = typeof confirmed !== 'undefined' ? confirmed : false;

  // Check if action is confirmed
  if (confirmed)
  {
    // Run the delete command on the server
    getApiResponse("api/Task/" + id, null, function(data) {
      // Hide dialog
      $('#deleteConfirmModalDialog').modal('hide');

      // Show alert
      showAlert(data);

      // Remove task from board
      $('#task' + id).remove();
    }, "DELETE");
  }
  else
  {
    // Setup click eventhandler
    $('#deleteConfirmModalDialogButton').click(function() {
      deleteTask(id, true);
    });

    // Show Confirm Dialog
    $('#deleteConfirmModalDialog').modal();
  }
}

/**
 * Show the dialog to add a story to the sprint given by the sprintId
 * @param int sprintId The id of the story on which the task should assigned
 */
function addStory(sprintId) {
  // Clear placeholders
  $('#addStoryModalDialogCaptionInput').val('');
  $('#addStoryModalDialogDescriptionTextarea').val('');
  $('#addStoryModalDialogStoryPointsInput').val(0);
  $('#addStoryModalDialog').data('sprintId', sprintId);

  // Show dialog
  $('#addStoryModalDialog').modal();
}

/**
 * Show the edit dialog to edit a story
 * @param int id The id of the story to edit
 */
function editStory(id) {
  // Get current values
  $.getJSON('api/Story/' + id, function(data) {
    if (data.Type == 'success')
    {
      // Setup dialog with data
      $('#editStoryModalDialogCaptionInput').val(data.Data.Caption);
      $('#editStoryModalDialogDescriptionTextarea').val(data.Data.Description);
      $('#editStoryModalDialogStoryPointsInput').val(data.Data.StoryPoints);
      $('#editStoryModalDialog').data('storyId', id);

      // Show dialog
      $('#editStoryModalDialog').modal();
    }
    else
    {
      // Show error message
      showAlert({ Type: 'error', Title: 'Story editing failed', Description: 'Failed to load the data of the given story'});
    }
  });
}

/**
 * Delete the story given by the id
 * @param int id The id of the story to delete
 * @param bool confirmed Set if the action is already confirmed
 */
function deleteStory(id, confirmed) {
  // Setup default values
  confirmed = typeof confirmed !== 'undefined' ? confirmed : false;

  // Check if action is confirmed
  if (confirmed)
  {
    // Run the delete command on the server
    getApiResponse("api/Story/" + id, null, function(data) {
      // Hide dialog
      $('#deleteStoryModalDialog').modal('hide');

      // Show alert
      showAlert(data);

      // Remove story from board
      $('#story' + id).remove();
    }, "DELETE");
  }
  else
  {
    // Setup click eventhandler
    $('#deleteStoryModalDialogButton').click(function() {
      deleteStory(id, true);
    });

    // Show Confirm Dialog
    $('#deleteStoryModalDialog').modal();
  }
}

/**
 * Show the dialog to add a sprint to the team given by the teamId
 * @param int teamId The id of the team on which the sprint should assigned
 */
function addSprint(teamId) {
  // Clear placeholders
  $('#addSprintModalDialogCaptionInput').val('');
  $('#addSprintModalDialogStartDateInput').val('');
  $('#addSprintModalDialogEndDateInput').val('');
  $('#addSprintModalDialog').data('teamId', teamId);

  // Show dialog
  $('#addSprintModalDialog').modal();
}

/**
 * Show the edit dialog to edit a sprint
 * @param int id The id of the sprint to edit
 */
function editSprint(id) {
  // Get current values
  $.getJSON('api/Sprint/' + id, function(data) {
    if (data.Type == 'success')
    {
      // Setup dialog with data
      $('#editSprintModalDialogCaptionInput').val(data.Data.Caption);
      $('#editSprintModalDialogStartDateInput').val(data.Data.StartDate);
      $('#editSprintModalDialogEndDateInput').val(data.Data.EndDate);
      $('#editSprintModalDialog').data('sprintId', id);

      // Show dialog
      $('#editSprintModalDialog').modal();
    }
    else
    {
      // Show error message
      showAlert({ Type: 'error', Title: 'Sprint editing failed', Description: 'Failed to load the data of the given sprint'});
    }
  });
}

/**
 * Delete the sprint given by the id
 * @param int id The id of the sprint to delete
 * @param bool confirmed Set if the action is already confirmed
 */
function deleteSprint(id, confirmed) {
  // Setup default values
  confirmed = typeof confirmed !== 'undefined' ? confirmed : false;

  // Check if action is confirmed
  if (confirmed)
  {
    // Run the delete command on the server
    getApiResponse("api/Sprint/" + id, null, function(data) {
      // Hide dialog
      $('#deleteSprintModalDialog').modal('hide');

      // Show alert
      showAlert(data);

      // Remove story from board
      reloadSprintView();
    }, "DELETE");
  }
  else
  {
    // Setup click eventhandler
    $('#deleteSprintModalDialogButton').click(function() {
      deleteSprint(id, true);
    });

    // Show Confirm Dialog
    $('#deleteSprintModalDialog').modal();
  }
}


/**
 * Show the dialog to add a team 
 */
function addTeam() {
  // Clear placeholders
  $('#addTeamModalDialogCaptionInput').val('');

  // Show dialog
  $('#addTeamModalDialog').modal();
}

/**
 * Show the edit dialog to edit a team
 * @param int id The id of the team to edit
 */
function editTeam(id) {
  // Get current values
  $.getJSON('api/Team/' + id, function(data) {
    if (data.Type == 'success')
    {
      // Setup dialog with data
      $('#editTeamModalDialogCaptionInput').val(data.Data.Caption);
      $('#editTeamModalDialog').data('teamId', id);

      // Show dialog
      $('#editTeamModalDialog').modal();
    }
    else
    {
      // Show error message
      showAlert({ Type: 'error', Title: 'Team editing failed', Description: 'Failed to load the data of the given team'});
    }
  });
}

/**
 * Delete the team given by the id
 * @param int id The id of the team to delete
 * @param bool confirmed Set if the action is already confirmed
 */
function deleteTeam(id, confirmed) {
  // Setup default values
  confirmed = typeof confirmed !== 'undefined' ? confirmed : false;

  // Check if action is confirmed
  if (confirmed)
  {
    // Run the delete command on the server
    getApiResponse("api/Team/" + id, null, function(data) {
      // Hide dialog
      $('#deleteTeamModalDialog').modal('hide');

      // Show alert
      showAlert(data);

      // Remove story from board
      reloadTeamView();
    }, "DELETE");
  }
  else
  {
    // Setup click eventhandler
    $('#deleteTeamModalDialogButton').click(function() {
      deleteTeam(id, true);
    });

    // Show Confirm Dialog
    $('#deleteTeamModalDialog').modal();
  }
}

/**************************************************************************************************
* GUI-UPDATE FUNCTIONS
**************************************************************************************************/

/**
 * Reload the board
 */
function reloadBoard() {
  // Diplay loading screen
  hideAllTabs();      

  // Get required data from the api
  getApiResponse("api/States", {}, function (statesJson) {
    if (statesJson.Type == "success") {
      getApiResponse("api/Sprint/" + activeSprintId + "/Complete", {}, function (sprintsJson) { 
        if (sprintsJson.Type == "success") {
          // Combine template with data an d generate html code
          var columnWidth = 100.0 / (statesJson.Data.length + 1);
          var boardData = { Id: activeSprintId, ColumnWidth: columnWidth, States: statesJson.Data, Stories: sprintsJson.Data }; 
          var html = templateScrumboard(boardData);
          $("#boardViewPlaceholder").html(html);

          // Show table
          showTab('#boardView');
        } else {
          showAlert(sprintsJson);
        }
      }, "GET");
    } else {
      showAlert(statesJson);
    }
  }, "GET");
}

function reloadSprintView() {
   // Diplay loading screen
  hideAllTabs();     

  // Get required data from the api
  getApiResponse("api/Team/" + activeTeamId + "/Sprints", {}, function (sprintsJson) { 
    if (sprintsJson.Type == "success") {
      // Combine template with data and generate html code
      var sprintsData = { Id: activeTeamId, Sprints: sprintsJson.Data }; 
      var html = templateSprints(sprintsData);
      $("#sprintViewPlaceholder").html(html);

      // Show table
      showTab('#sprintView');
    } else {
      showAlert(sprintsJson);
    }
  }, "GET");
}

function reloadTeamView() {
  // Diplay loading screen
  hideAllTabs();

  // Get required data from the api
  getApiResponse("api/Teams", {}, function (teamsJson) { 
    if (teamsJson.Type == "success") {
      // Combine template with data and generate html code
      var teamsData = { Teams: teamsJson.Data }; 
      var html = templateTeamView(teamsData);
      $("#teamViewPlaceholder").html(html);

      // Show table
      showTab('#teamView');
    } else {
      showAlert(sprintsJson);
    }
  }, "GET");
}

function hideAllTabs() {
  $('#teamView').addClass("hidden");
  $("#boardView").addClass("hidden");
  $("#sprintView").addClass("hidden");
  $("#loadingView").removeClass("hidden");  
}

function showTab(tabName) {
  hideAllTabs();
  $("#loadingView").addClass("hidden");
  $(tabName).removeClass("hidden");  
}

/**************************************************************************************************
* HELPER FUNCTIONS
**************************************************************************************************/

/**
 * Get the json-respone from a ajax-call with the given parameters
 * @param string url The url from which the call should read the response 
 * @param object data An object of data which should sent to the server
 * @param function success The function which should processed with the recived json data
 * @param string type The type of the request [POST, GET, PUT, DELETE]
 */
function getApiResponse(url, data, success, type) {
  // Setup default values
  type = typeof type !== 'undefined' ? type : "GET";

  // Make ajax request
  $.ajax({
    type: type,
    url: url,
    data: data,
    success: success,
    dataType: 'json'
  });
}

/**
 * Show a alert on the page
 * @param object alertObject An OBject which contains the alert informations [Type, Title, Description]
 */
function showAlert(alertObject) {
  // Setup default values
  type = typeof alertObject.Type !== 'undefined' ?  alertObject.Type : 'info';
  title = typeof alertObject.Title !== 'undefined' ?  alertObject.Title : '';
  description = typeof alertObject.Description !== 'undefined' ?  alertObject.Description : 'No informations';
  type = (type == 'error') ? 'danger' : type;

  // Build the alert
  alert =  '<div class="alert alert-' + type + '  alert-fixed-top">';
  alert += '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
  alert += '<strong>' + title + '</strong> ' + description + '</div>';

  // Apend the alert to the body
  $('body').append(alert);

  // Enable auto closing alert
  window.setTimeout(function() {
    $(".alert").fadeTo(500, 0).slideUp(500, function(){
      $(this).remove(); 
    });
  }, 5000);
}

/**
* Set a new sprint and reload the board
* @param int id The id of the sprint to load
*/
function setSprint(id) {
  activeSprintId = id;
  // Diplay loading screen
  hideAllTabs(); 
  
  // Reload data
  reloadBoard();
}

function setTeam(id) {
  activeTeamId = id;
  // Diplay loading screen
  hideAllTabs(); 
  
  // Reload data
  reloadSprintView();
}


/**************************************************************************************************
* SETUP AUTOSTART FUNCTIONS 
**************************************************************************************************/

// Onload Funvtions
$(function() {
  
  // Add eventhandler to AddTeamDialogButton
  $('#addTeamModalDialogButton').click(function() {
    // Build the data object
    team = { 
      Caption: $('#addTeamModalDialogCaptionInput').val()
    };

    // Send data to the api
    getApiResponse('api/Teams', team, function(data) {
      // Hide Dialog
      $('#addTeamModalDialog').modal('hide');

      // Show alert & reload team list
      showAlert(data);
      reloadTeamView();
    }, 'POST');
  });

  // Add eventhandler to EditTeamDialogButton
  $('#editTeamModalDialogButton').click(function() {
    // Get task Id
    id = $('#editTeamModalDialog').data('teamId');

    // Build the data object
    team = { 
      Caption: $('#editTeamModalDialogCaptionInput').val()
    };

    // Send data to the api
    getApiResponse('api/Team/' + id, team, function(data) {
      // Hide Dialog
      $('#editTeamModalDialog').modal('hide');

      // Show alert & reload team list
      showAlert(data);
      reloadTeamView();
    }, 'POST');
  });

  // Add eventhandler to AddSprintDialogButton
  $('#addSprintModalDialogButton').click(function() {
    // Build the data object
    sprint = { 
      Caption: $('#addSprintModalDialogCaptionInput').val(), 
      StartDate: $('#addSprintModalDialogStartDateInput').val(),
      EndDate: $('#addSprintModalDialogEndDateInput').val(),
      TeamId: $('#addSprintModalDialog').data('teamId')
    };

    // Send data to the api
    getApiResponse('api/Sprints', sprint, function(data) {
      // Hide Dialog
      $('#addSprintModalDialog').modal('hide');

      // Show alert & reload sprint list
      showAlert(data);
      reloadSprintView();
    }, 'POST');
  });

  // Add eventhandler to EditSprintDialogButton
  $('#editSprintModalDialogButton').click(function() {
    // Get task Id
    id = $('#editSprintModalDialog').data('sprintId');

    // Build the data object
    sprint = { 
      Caption: $('#editSprintModalDialogCaptionInput').val(), 
      StartDate: $('#editSprintModalDialogStartDateInput').val(),
      EndDate: $('#editSprintModalDialogEndDateInput').val()
    };

    // Send data to the api
    getApiResponse('api/Sprint/' + id, sprint, function(data) {
      // Hide Dialog
      $('#editSprintModalDialog').modal('hide');

      // Show alert & add sprint list
      showAlert(data);
      reloadSprintView();
    }, 'POST');
  });


  // Add eventhandler to AddStoryDialogButton
  $('#addStoryModalDialogButton').click(function() {
    // Build the data object
    story = { 
      Caption: $('#addStoryModalDialogCaptionInput').val(), 
      Description: $('#addStoryModalDialogDescriptionTextarea').val(),
      StoryPoints: $('#addStoryModalDialogStoryPointsInput').val(),
      SprintId: $('#addStoryModalDialog').data('sprintId')
    };

    // Send data to the api
    getApiResponse('api/Stories', story, function(data) {
      // Hide Dialog
      $('#addStoryModalDialog').modal('hide');

      // Show alert & reload board
      showAlert(data);
      reloadBoard();
    }, 'POST');
  });

  // Add eventhandler to EditStoryDialogButton
  $('#editStoryModalDialogButton').click(function() {
    // Get task Id
    id = $('#editStoryModalDialog').data('storyId');

    // Build the data object
    story = { 
      Caption: $('#editStoryModalDialogCaptionInput').val(), 
      Description: $('#editStoryModalDialogDescriptionTextarea').val(),
      StoryPoints: $('#editStoryModalDialogStoryPointsInput').val()
    };

    // Send data to the api
    getApiResponse('api/Story/' + id, story, function(data) {
      // Hide Dialog
      $('#editStoryModalDialog').modal('hide');

      // Show alert & add story to board
      showAlert(data);
      reloadBoard();
    }, 'POST');
  });

  // Add eventhandler to EditTaskDialogButton
  $('#editTaskModalDialogButton').click(function() {
    // Get task Id
    id = $('#editTaskModalDialog').data('taskId');

    // Build the data object
    task = { 
      Caption: $('#editTaskModalDialogCaptionInput').val(), 
      Description: $('#editTaskModalDialogDescriptionTextarea').val()
    };

    // Send data to the api
    getApiResponse('api/Task/' + id, task, function(data) {
      // Hide Dialog
      $('#editTaskModalDialog').modal('hide');

      // Show alert & update task content
      showAlert(data);
      reloadBoard();
    }, 'POST');
  });

  // Add eventhandler to AddTaskDialogButton
  $('#addTaskModalDialogButton').click(function() {
    // Build the data object
    task = { 
      Caption: $('#addTaskModalDialogCaptionInput').val(), 
      Description: $('#addTaskModalDialogDescriptionTextarea').val(),
      StoryId: $('#addTaskModalDialog').data('storyId')
    };

    // Send data to the api
    getApiResponse('api/Tasks', task, function(data) {
      // Hide Dialog
      $('#addTaskModalDialog').modal('hide');

      // Show alert & add task to board
      showAlert(data);
      reloadBoard();
    }, 'POST');
  });

  // Setup variables for scrumboar and templating
  templateTeamView = Handlebars.compile($("#teamViewTemplate").html());
  templateScrumboard = Handlebars.compile($("#scrumboardTemplate").html());
  templateSprints = Handlebars.compile($("#sprintsTemplate").html());
  templateScrumboard = Handlebars.compile($("#scrumboardTemplate").html());
  templateStory = Handlebars.compile($("#storyTemplate").html());
  templateTask = Handlebars.compile($("#taskTemplate").html());
  templateMenuEntry = Handlebars.compile($("#menuEntryTemplate").html());
  Handlebars.registerPartial("Task", $("#taskTemplate").html());
  Handlebars.registerPartial("Story", $("#storyTemplate").html());
  
  // Setup view with values
  //setSprint(activeSprintId);

});

